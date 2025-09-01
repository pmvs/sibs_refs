<?php 

namespace App\Services\Testes;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;
use App\Services\BancoPortugal\BancoPortugalService;
use  App\Repositories\BancoPortugal\ProxyLookupRepository;
use App\Models\OAuth2JwtGenerator;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\Models\ADFSToken;
use App\Models\BPPLRequest;
use App\Models\PLAssociacao;
use App\Models\PlPayload;


class OAuth2Indisponibilidade extends Controller
{
    private $logchannel = 'testes';
    private $token_id = '';
    private $token_valido = false;
    private $resource  = '';

    /*
    |--------------------------------------------------------------------------
    | CONSTRUTORES
    |--------------------------------------------------------------------------
    */ 
    public function __construct() 
    {
        $this->logchannel = 'testes';
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct OAuth2Indisponibilidade');
    }

    public function __construct_1( $resource ) 
    {
        $this->logchannel = 'testes';
        $this->resource = $resource;
        \Log::channel($this->logchannel)->info('__construct_1 OAuth2Indisponibilidade :' . $resource);
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS PUBLIC - EXPOSED SERVICES
    |--------------------------------------------------------------------------
    */

      /**
     * @param $redirectURI - URL Encoded Redirect URI
     * @param $clientId - API Key
     * @param $scope - URL encoded, plus sign delimited list of scopes that your application requires. The 'offline_access' scope needed to request a refresh token is added by default.
     * @param $state - Arbitrary string value(s) to verify response and preserve application state
     * @return string - Full Authorization URL
    */

    public function getTokenId()
    {
        return $this->token_id;
    }


    public function getAccessToken()
    {
        try {
            $token_id = '';
            $this->token_id = '';
            $access_token = '';

            //token from ADFS  / DB   
            \Log::channel($this->logchannel)->info('getActiveTokenFromDB');
            $tokenBearer = $this->getActiveTokenFromDB();
            \Log::channel($this->logchannel)->info($tokenBearer);

            if ( ! $tokenBearer ) {
                //nao ha tokens ativos
                \Log::channel($this->logchannel)->info('getTokenFromADFS');
                $tokenBearer = $this->getTokenFromADFS();
                \Log::channel($this->logchannel)->info($tokenBearer);
                if ( $tokenBearer['success'] ) {
                    $access_token =  $tokenBearer['response']['response']['access_token'];
                    $this->token_valido = true;
                    \Log::channel($this->logchannel)->info('token válido');
                }else {
                    $this->token_valido = false;
                    \Log::channel($this->logchannel)->info('token inválido');
                    return '';
                }
            }else {
                //existe um token ativo
                //verifca se está válido
                \Log::channel($this->logchannel)->info('Verifica validade do token');
                $criadoem = $tokenBearer->created_at;
                $expiraem = $tokenBearer->expires_in;
                $validoate = $tokenBearer->valid_until;

                 \Log::channel($this->logchannel)->info('Válido até ' . $validoate );
                 \Log::channel($this->logchannel)->info('Data : ' . date('Y-m-d H:m:i'));

                if ( date('Y-m-d H:i:s') < $validoate ) {
                    $access_token = $tokenBearer->access_token;
                    $token_id = $tokenBearer->id;
                    $this->token_id = $token_id;
                    $this->token_valido = true;
                    // \Log::channel($this->logchannel)->info('O token encontra-se válido');
                }else {
                    //token expirado 
                    //deve pedir novo token ao ADFS do BP
                    \Log::channel($this->logchannel)->info('O token encontra-se expirado na BD');
                    $this->inativaTokenDB();
                    $tokenBearer = $this->getTokenFromADFS();
                    if ( $tokenBearer['success'] ) {
                        $access_token =  $tokenBearer['response']['response']['access_token'];
                        $this->token_valido = true;
                    }else {
                        $this->token_valido = false;
                    }
                }
            }

            if ( ! $this->token_valido  ) {
                \Log::channel($this->logchannel)->info('Vai devolver vazio...Causa: ' . $tokenBearer['error']  );
                return '';
            }

            return $access_token;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return '';
        }

    }

    private function getTokenFromADFS()
    {
        try {

        //generate jwt assertion
        \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : get jwtToken from OAuth2Indisponibilidade');
        $jwtToken = $this->getJwtToken();
        if( $jwtToken  == '' ){
            \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : jwtToken not set');
            return ['success' => false, 'error' => 'erro a criar o JWT' , 'response' => ''];
        }
        //get token from ADFS 
        \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : get bearer token from ADFS');
        $tokenBearer = $this->getBearerTokenFromADFS(  $this->resource , $jwtToken);  
        \Log::channel($this->logchannel)->info($tokenBearer);

        if ( array_key_exists('error',$tokenBearer)) {
            //erro na obtencao de token 
            return ['success' => false, 'error' => 'erro na obtencao de token ' , 'response' => $tokenBearer];
        }

        if (! $tokenBearer['response'] ) {
            //erro na obtencao de token 
            return ['success' => false, 'error' => 'erro na obtencao de token ' , 'response' => ''];
        }

        if ( ! array_key_exists('access_token',$tokenBearer['response'])) {
            //erro na obtencao de token 
            return ['success' => false, 'error' => 'erro na obtencao de token ' , 'response' => $tokenBearer];
        }

        $access_token = $tokenBearer['response']['access_token'];
        $this->access_token = $access_token;
        \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : access_token: ' . $access_token );
        if ( trim($access_token) == '' ) {
            //erro na obtencao de token 
            return ['success' => false, 'error' => 'erro na obtencao de token. Token vazio' , 'response' => ''];
        }

        //inativa tokens ativos anteriores
        $this->inativaTokenDB();

        //save token to database 
        try {
            if ( ! $tokenBearer['response'] ) {
                $tokendata = [
                    'dt_pedido' => date('Y-m-d'),
                    'resource' => $tokenBearer['payload']['resource'], 
                    'client_id' => $tokenBearer['payload']['client_id'], 
                    'client_assertion' => $tokenBearer['payload']['client_assertion'], 
                    'access_token' => '', 
                    'token_type' => 'error', 
                    'expires_in' => 3600, 
                    'ativo' => false,
                    'audience' => $tokenBearer['audience'],
                    'valid_until' => now()
                ];
            }else {
                $tokendata = [
                    'dt_pedido' => date('Y-m-d'),
                    'resource' => $tokenBearer['payload']['resource'], 
                    'client_id' => $tokenBearer['payload']['client_id'], 
                    'client_assertion' => $tokenBearer['payload']['client_assertion'], 
                    'access_token' => trim($tokenBearer['response']['access_token']), 
                    'token_type' => $tokenBearer['response']['token_type'], 
                    'expires_in' => $tokenBearer['response']['expires_in'], 
                    'ativo' => true,
                    'audience' => $tokenBearer['audience'],
                    'valid_until' => now()->addSeconds( $tokenBearer['response']['expires_in'] )
                ];
            }

           
            $tokenADFS = ADFSToken::insertGetId( $tokendata );
            \Log::channel($this->logchannel)->info('Token saved to db'); 
            $this->token_id =  $tokenADFS;
            \Log::channel($this->logchannel)->info('Token ID ' .  $this->token_id ); 

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Token NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
            return ['success' => false, 'error' => $e->getMessage() , 'response' => ''];
        }

        return ['success' => true, 'error' => '' , 'response' => $tokenBearer];

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage() , 'response' => ''];
        }
    }
    

    private function getActiveTokenFromDB()
    {
        try{

            $tokenADFS = ADFSToken::where( 'dt_pedido', date('Y-m-d') )
                ->where('ativo', true)->first();
            
            if ( ! $tokenADFS ) {
                \Log::channel($this->logchannel)->info('Não foi encontrado nenhum token ativo');
                $this->token_valido = false;
                return null;
            }
            \Log::channel($this->logchannel)->info('Foi encontrado na BD um token ativo');

            return $tokenADFS;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    public function inativaTokenDB()
    {
        try{
            \Log::channel($this->logchannel)->info('Inativa token DB...');
            ADFSToken::where('ativo', true)
            ->update(['ativo' => false]);
            $this->token_valido = false;
            \Log::channel($this->logchannel)->info('Token inativado OK');
            return true;
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->info('Token NAO inativado NOT OK');
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    
    public function getBearerTokenFromADFS( $resource, $jwtToken )
    {
         \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : getBearerTokenFromADFS');
 
         try{

            //quem assina o jwt
            $client_id = config('app.oauth2_client_id');
            $audience =  config('app.oauth2bpuriaudience');
            $client_assertion_type = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
            
            //build request content
            $requestContent = [
                'resource' => $resource,
                'client_id' => $client_id,
                'grant_type' => 'client_credentials',
                'client_assertion_type' => $client_assertion_type,
                'client_assertion' => $jwtToken 
            ];
            \Log::channel($this->logchannel)->info('Request Content : ' . print_r($requestContent, true));

            $response = null;
            try {
                \Log::channel($this->logchannel)->info('CURL POST TO OAUTH BANCO PORTUGAL');
                $response =$this->callUsingCURL( $audience, $requestContent );
            }catch(\Exception $e){
                \Log::channel($this->logchannel)->error($e->getMessage());
            }

            return ['response' => $response, 'audience' => $audience, 'payload' => $requestContent]; 

         }catch(\Exception $e){
             \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
             return response()->json([
                 'success' => false,
                 'message' => 'Não foi possível enviar o pedido',
             ], 500);
         }
     
    }

 
 

    public function getJwtToken(  )
    {
        \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : getJwtToken');
        try{

            //obtem certificado como array
            $certificate = $this->getCertificate();
           
            if ( ! $certificate ) {
                \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : certificate not found');
                return '';
            }
            if( count($certificate) == 0 ){
                \Log::channel($this->logchannel)->info('OAuth2Indisponibilidade : certificate not read');
                return '';
            }

            //variaveis para jwt
            $sigla = config('app.sigla_psp');
            $issuer = config('app.oauth2_client_id');
            //oauth2 bp uri
            $audience = config('app.oauth2bpuriaudience');
       
            //generate jwt token 
            $token = ( new OAuth2JwtGenerator( $this->logchannel ) )->generateJwtToken( $audience, $issuer, $certificate );
            \Log::channel($this->logchannel)->info('JWT Token : ' . print_r($token, true));

            return $token;

        } catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return '';
        }
    }

  
    /*
    |--------------------------------------------------------------------------
    | CURL CALLS
    |--------------------------------------------------------------------------
    */
  
    public  function callUsingCURL_3($audience,  $payload, $headers)
    {
        try {

            \Log::channel($this->logchannel)->info( 'OAuth2Indisponibilidade callUsingCURL_3 _________');
            \Log::channel($this->logchannel)->info( 'audience:' . $audience );
            \Log::channel($this->logchannel)->info( 'payload: ' . $payload);
            \Log::channel($this->logchannel)->info( 'headers: ' . print_r($headers, true));
     
            $storageDir = storage_path('app/curl-indisp-'. date('Y-md').'txt');
            $fp = fopen($storageDir, 'a+');

            $curl = curl_init();

            $params = array(
                CURLOPT_URL => $audience ,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR =>  $fp,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POST => true, 
                CURLOPT_POSTFIELDS => $payload ,
            );
  
            curl_setopt_array($curl, $params);
        

            //\Log::channel($this->logchannel)->info('----curl_exec----');
            $start = microtime(true);
            $response = curl_exec($curl);
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_msecs: ' .  ($time_elapsed_secs * 1000));
         
            // \Log::channel($this->logchannel)->info( '----RAW RESPONSE----');
            // \Log::channel($this->logchannel)->info( $response );

            //get curl errors
            $err = curl_error($curl);
            \Log::channel($this->logchannel)->error('ERROS----' . $err );
            $errno = curl_errno($curl);
            \Log::channel($this->logchannel)->error('ERRO NUMBER----'. $errno);

         
          
            //get HTTP code 
            $this->httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            \Log::channel($this->logchannel)->info('HTTP CODE----'. $this->httpcode );

            // if ( $this->httpcode != '200'  ) {
            //     \Log::channel($this->logchannel)->info('incrementa contador ' . $this->psp_code_destination);

            //     try {
            //         $counter =  \Storage::disk('local-counter')->get('counter-'.$this->psp_code_destination.'-'.$this->httpcode .'.txt');
            //         $counter+=1;
            //         \Storage::disk('local-counter')->put('counter-'.$this->psp_code_destination.'-'.$this->httpcode .'.txt', $counter);
            //     }catch(Exception $e){
            //         \Log::channel($this->logchannel)->error($e->getMessage());
            //     }

            // }
         

            //get HTTP HEADER 
            //$httpinfo_header = curl_getinfo($curl);
            // \Log::channel($this->logchannel)->info('HTTP HEADER----');
            // \Log::channel($this->logchannel)->info($httpinfo_header);
           
            $data = json_decode($response, true);
            \Log::channel($this->logchannel)->info($response);

            return $response;
            

        } catch ( \Symfony\Component\ErrorHandler\Error\FatalError $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return response()->json(['data' => 'Problemas de ligação ao Banco de Portugal'], 200, ['Content-Type', 'application/json']);
            throw $e;

        } catch ( \Error $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
            // return response()->json(['data' => 'Problemas de ligação ao Banco de Portugal'], 200, ['Content-Type', 'application/json']);

        } catch ( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
            // return response()->json(['data' => 'Problemas de ligação ao Banco de Portugal'], 200, ['Content-Type', 'application/json']);
        }

 
    }

 
    private function callUsingCURL($audience,  $requestContent)
    {
        try {

            $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $storageDir = storage_path('app/curlerr.txt');
            $fp = fopen($storageDir, 'a+');

            $urlEncoded1 = "resource=".urlencode($requestContent['resource'])
                ."&client_id=".urlencode($requestContent['client_id'])
                ."&grant_type=".urlencode($requestContent['grant_type'])
                ."&client_assertion_type=".urlencode($requestContent['client_assertion_type'])
                ."&client_assertion=".urlencode($requestContent['client_assertion']);

            $curl = curl_init();

            $params = array(
                CURLOPT_URL => $audience ,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR =>  $fp,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
                    "Accept: *",
                ),
                CURLOPT_POST => true, 
                CURLOPT_POSTFIELDS => $urlEncoded1 ,
                // CURLOPT_SSLCERTTYPE => "P12",
                // CURLOPT_SSLCERT => "c:\certs\bpnetsvc-ccmaf-cert.bportugal.pt.pfx",
                // CURLOPT_SSLCERTPASSWD => "1234567890"
            );
  
            curl_setopt_array($curl, $params);
        
            $response = curl_exec($curl);

            \Log::channel($this->logchannel)->info( '----RAW RESPONSE----');
            \Log::channel($this->logchannel)->info( $response );
           
            $data = json_decode($response, true);
            \Log::channel($this->logchannel)->info( 'JSON : ' . print_r($data, true) );

            return $data;

        } catch ( \Exception $e) {
            return response()->json(['data' => $e->getMessage()]);
        }

 
    }



    /**
    * Read a .pfx file.
    */
    private function getCertificate()
    {
        \Log::channel($this->logchannel)->info('BancoPortugalService : getCertificate');
        try{

            $pathToCertificate = config('app.path_to_certificate_pfx');

            $data = file_get_contents( $pathToCertificate );

            $certPassword = config('app.psw_certificate_pfx');

            //certificate stored into an array
            openssl_pkcs12_read( $data, $certs, $certPassword );

            Log::channel($this->logchannel)->info('obtive o certificado');

            return $certs;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return [];
        }
      
    }

    public function verify_jwt($jwt, $publicKey) 
    {

        \Log::channel($this->logchannel)->info('BancoPortugalService verify_jwt');
        try{

            list( $headb64, $bodyb64, $cryptob64 ) = explode('.', $jwt);

            $header = json_decode(base64_decode($headb64), true);
            $payload = json_decode(base64_decode($bodyb64), true);

            $message = base64_decode($headb64) . '.' . base64_decode($bodyb64); 
            $signature = base64_decode($cryptob64);

            //$publicKey = openssl_get_publickey($publicKey);

            $success = false;
            try {
                \Log::channel($this->logchannel)->info('Verify with RSA-SHA256' );
                $success = openssl_verify($message,  $signature ,$publicKey, 'RSA-SHA256' );
                \Log::channel($this->logchannel)->info('Success: ' . $success);
            }catch(\Exception $e){
                \Log::channel($this->logchannel)->error(__FILE__. ' ' . __LINE__ . ' ' . $e->getMessage());
                $success = false;
            }
            try {
                \Log::channel($this->logchannel)->info('Verify with OPENSSL_ALGO_SHA1' );
                $success = openssl_verify($message,  $signature ,$publicKey, OPENSSL_ALGO_SHA1 );
                \Log::channel($this->logchannel)->info('Success: ' . $success);
            }catch(\Exception $e){
                \Log::channel($this->logchannel)->error(__FILE__. ' ' . __LINE__ . ' ' . $e->getMessage());
                $success = false;
            }
            try {
                \Log::channel($this->logchannel)->info('Verify with OPENSSL_ALGO_SHA256' );
                $success = openssl_verify($message,  $signature ,$publicKey, OPENSSL_ALGO_SHA256 );
                \Log::channel($this->logchannel)->info('Success: ' . $success);
            }catch(\Exception $e){
                \Log::channel($this->logchannel)->error(__FILE__. ' ' . __LINE__ . ' ' . $e->getMessage());
                $success = false;
            }
            try {
                \Log::channel($this->logchannel)->info('Verify' );
                $success = openssl_verify($message,  $signature ,$publicKey );
                \Log::channel($this->logchannel)->info('Success: ' . $success);
            }catch(\Exception $e){
                \Log::channel($this->logchannel)->error(__FILE__. ' ' . __LINE__ . ' ' . $e->getMessage());
                $success = false;
            }
           
            
            return $success === 1;

          }catch(\Exception $e){

            report($e);
            \Log::channel($this->logchannel)->error(__FILE__. ' ' . __LINE__ . ' ' . $e->getMessage());
            return false;
        }
    }

    public function readPublicKeyfromADFS()
    {
     
        \Log::channel($this->logchannel)->info('BancoPortugalService readPublicKeyfromADFS');
        try{

            $audience = config('app.oauth2_keys');
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);

            $method = 'GET';
            $payload = [];
            $headers = [];
            $headers = ['accept: application/json'];
            $requestContent = [];

            $result =  $this->makeCurlCall2( $audience,  $requestContent, $headers, $method , $payload );
            
            $content = $result['content'];

            $json = json_decode( $content, true);

            $publicKeys = $json['keys'][0]['x5c'][0];

            //return $publicKeys;
            //\Log::channel($this->logchannel)->info('Public key : ' . $publicKeys );

            // $publicKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($publicKeys, 64) . "-----END PUBLIC KEY-----";
            $publicKey = "-----BEGIN CERTIFICATE-----\n" . chunk_split($publicKeys, 64) . "-----END CERTIFICATE-----";

            return $publicKey;

 


        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error($e->getMessage());
        }
    }

    private function makeCurlCall2( $url , $curl_data, $headers, $method , $payload)
    {
        \Log::channel($this->logchannel)->info('BancoPortugalService----makeCurlCall2----');
        //make a curl call to homebanking
        try {

            // ini_set('max_execution_time',0);
            // ini_set('memory_limit', '-1');
    
            $proxyip= env('PROXY_IP');
            $proxyport = env('PROXY_PORT');
            $proxydata = $proxyip . ':' . $proxyport;

            //$start = microtime(true);
          
            $storageDir = storage_path('app/curlbdp.txt');
            $fp = fopen($storageDir, 'a+');

            //\Log::channel($this->logChannel)->info('----init----');
            //chamada ao homebanking
            $ch = curl_init();

            
            curl_setopt($ch, CURLOPT_URL, $url ) ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
            curl_setopt($ch, CURLOPT_ENCODING, '' );
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
            curl_setopt($ch, CURLOPT_TIMEOUT, 0 );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1) ;
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method) ;
            curl_setopt($ch, CURLOPT_FAILONERROR, 1 );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
            curl_setopt($ch, CURLOPT_VERBOSE, 1 );
            curl_setopt($ch, CURLOPT_STDERR, $fp );



            if ( config('app.usa_proxy') == 'S' ) {
                curl_setopt($ch, CURLOPT_PROXY, $proxydata );
            }
       
            if ( ! empty($headers) ) {
                //\Log::channel($this->logChannel)->info('vou fazer set do header ' . print_r($headers, true) );
                if(!is_array($headers)) throw new InvalidArgumentException('headers must be an array');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if ( ! empty($payload) ) {
                \Log::channel($this->logchannel)->info('vou fazer set do body ' . $payload);
                //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '' . $payload . '');
            }

            //curl_setopt_array($ch, $options);

            //exceute curl
            // echo 'Operation EXECUTING CURL...'. PHP_EOL;
            \Log::channel($this->logchannel)->info('----curl_exec----');
            $start = microtime(true);
            $output = curl_exec($ch);
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);

            //get curl errors
            $err = curl_error($ch);
            \Log::channel($this->logchannel)->info('ERROS----' . $err );
        
            $errno = curl_errno($ch);
            \Log::channel($this->logchannel)->info('ERRO NUMBER----'. $errno);
          
            //get HTTP code 
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            \Log::channel($this->logchannel)->info('HTTP CODE----'. $httpcode);

            //get HTTP HEADER 
            $httpinfo_header = curl_getinfo($ch);
            \Log::channel($this->logchannel)->info('HTTP HEADER----');
            \Log::channel($this->logchannel)->info($httpinfo_header);

            curl_close($ch);
   
            //$response = json_decode($output, true);
            // \Log::channel($this->logchannel)->info('----RESPONSE----');
            // \Log::channel($this->logchannel)->info($response);

            if ( trim($httpcode) != 200)  {
                $result = [
                    'httpinfo_header' => $httpinfo_header,
                    'content' => $output ,
                    'error' => true,
                    'httpcode' => $httpcode,
                   // 'response' => $response ,
                ];
            }  else   {
                $result = [
                    'httpinfo_header' => $httpinfo_header ,
                    'content' => $output ,
                    'error' => false,
                    'httpcode' => $httpcode,
                    //'response ' => $response ,
                ];
            }

            \Log::channel($this->logchannel)->info(print_r( $result, true));
 
            return $result ;
            
        } catch (\Exception $e) {
           // echo 'Operation EXCEPTION in makeCurlCall...' . $e->getMessage();
            \Log::channel($this->logChannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [
                'httpinfo_header' => [] ,
                'content' => $e->getMessage(),
                'error' => true,
                'httpcode' => 500,
            ];;
        }
    }

    public function readPublicKeyfromCertificate()
    {
        \Log::channel($this->logchannel)->info('BancoPortugalService : readPublicKeyfromCertificate');
        try{

            //obtem certificado como array
            $certificate = $this->getCertificate();
            if( count($certificate) == 0 ){
                \Log::channel($this->logchannel)->info('BancoPortugalService : certificate not read');
                return '';
            }
    
            $publicKey = openssl_pkey_get_public(  openssl_x509_read( $certificate['cert'] )   );

            return $publicKey;

        } catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return '';
        }
    }

    public function readPrivateKeyfromCertificate()
    {
        \Log::channel($this->logchannel)->info('BancoPortugalService : readPrivateKeyfromCertificate');
        try{

            //obtem certificado como array
            $certificate = $this->getCertificate();
            if( count($certificate) == 0 ){
                \Log::channel($this->logchannel)->info('BancoPortugalService : certificate not read');
                return '';
            }
    
           $privateKey = $certificate['pkey'];

            return $privateKey;

        } catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return '';
        }
    }


    public function saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs , $contrato, $myJSON, $token_id)
    {
        //\Log::channel($this->logchannel)->info('SAVE Request to DB'); 
        try {
            \Log::channel($this->logchannel)->info('SAVE Request to DB'); 
            $pedido = [
                'dt_pedido' => date('Y-m-d'),
                'token_id' => $token_id , 
                'payload' => json_encode($myJSON, JSON_UNESCAPED_SLASHES), 
                'response' => json_encode($response, JSON_UNESCAPED_SLASHES),
                'audience' => trim($audience),
                'correlation_id' => $correlation_id ,
                'n_netcaixa' => $contrato,
                'user_id' => 0, 
                'timeelapsed' => $time_elapsed_secs,
                'http_code_response' => $this->httpcode 
            ];
            \Log::channel($this->logchannel)->info('Request: ' . print_r($pedido, true));

            $requestBP = BPPLRequest::insertGetId( $pedido);
            $request_id =  $requestBP;

            \Log::channel($this->logchannel)->info('Request saved to db with id ' .  $request_id  ); 
            
            return $request_id;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Request NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
            return 0;
        }
    }

    public function savePayloadToDB($request_id, $data ,$contrato)
    {
        //grava payload na BD
        \Log::channel($this->logchannel)->info('SAVE Payload to DB'); 
        try {
           // $this->payloadJson = json_decode($this->payloadJson);
            \Log::channel($this->logchannel)->info(print_r($this->payloadJson, true)); 

            $errorcodes = '';
            $errorvalues = '';
            $account_holder = '';
            $commercial_name = '';
            //percorre erros para codigos e descricao
            if ( ! $data['success'] ) {
                foreach( $data['errors'] as $error){
                    $errorcodes .=  trim($error['code']) . ',';
                    $errorvalues .=  trim($error['value']) . ',';
                }
            }else {
                if ( array_key_exists('account_holder', $data)) {
                    $account_holder = trim($data['account_holder']);
                }
                if ( array_key_exists('commercial_name', $data)) {
                    $commercial_name = trim($data['commercial_name']);
                }
            }

            $psp_code = ''; //not in payload
            if ( property_exists($this->payloadJson,'psp_code')) {
                $psp_code = $this->payloadJson->psp_code;
            }
            $psp_code_destination = ''; //not in payload
            if ( property_exists($this->payloadJson,'psp_code_destination')) {
                $psp_code_destination = $this->payloadJson->psp_code_destination;
            }
            $iban = ''; //not in payload
            if ( property_exists($this->payloadJson,'iban')) {
                $iban = $this->payloadJson->iban;
            }
            $timestamp = ''; //not in payload
            if ( property_exists($this->payloadJson,'timestamp')) {
                $timestamp = $this->payloadJson->timestamp;
            }

            $payload = [
                'dt_pedido' => date('Y-m-d'),
                'request_id' => $request_id ,
                'n_netcaixa' => $contrato, 
                'user_id' => 0, 
                'psp_code' => $psp_code, 
                'psp_code_destination' => $psp_code_destination,  
                'iban' => $iban, 
                'account_holder' => $account_holder, 
                'commercial_name' => $commercial_name, 
                'correlation_id_origin' => '', 
                'timestamp' => $timestamp,
                'success' => $data['success'],
                'correlation_id' => $data['correlation_id'],
                'message' => $data['message'],
                'errors_codes' => $errorcodes,
                'errors_values' => $errorvalues,
                'http_code' => $this->httpcode 
            ];
            $payloadBD = CopPayload::insertGetId( $payload );
            $payload_id =  $payloadBD;
            \Log::channel($this->logchannel)->info('Payload saved to db with id ' . $payload_id );
    
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Payload NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }

    public function savePayloadPLToDB($request_id, $data ,$contrato, $myJSON,)
    {
        //grava payload na BD
        \Log::channel($this->logchannel)->info('SAVE Payload to DB'); 
        try {
           // $this->payloadJson = json_decode($this->payloadJson);
            $errorcodes = '';
            $errorvalues = '';
            $success = false;
            $message = '';
            $correlation_id = '';

            //percorre erros para codigos e descricao
            if ( array_key_exists('success', $data)) {
                $success = $data['success'];
                if ( ! $data['success'] ) {
                    foreach( $data['errors'] as $error){
                        $errorcodes .=  trim($error['code']) . ',';
                        $errorvalues .=  trim($error['value']) . ',';
                    }
                }
            }else {
                $success = false;
            }

            if ( array_key_exists('message', $data)) {
                $message = $data['message'];
            }
            if ( array_key_exists('correlation_id', $data)) {
                $correlation_id = $data['correlation_id'];
            }


            $psp_code = ''; //not in payload
            if ( property_exists($myJSON,'psp_code')) {
                $psp_code = $myJSON->psp_code;
            }
            $customer_identifier = ''; //not in payload
            if ( property_exists($myJSON,'customer_identifier')) {
                $customer_identifier = $myJSON->customer_identifier;
            }
            $fiscal_number = ''; //not in payload
            if ( property_exists($myJSON,'fiscal_number')) {
                $fiscal_number = $myJSON->fiscal_number;
            }
            $iban = ''; //not in payload
            if ( property_exists($myJSON,'iban')) {
                $iban = $this->myJSON->iban;
            }
            $customer_identifier_type = 0; //not in payload
            if ( property_exists($myJSON,'customer_identifier_type')) {
                $customer_identifier_type = $myJSON->customer_identifier_type;
            }
            $customer_type = 0; //not in payload
            if ( property_exists($myJSON,'customer_type')) {
                $customer_type = $myJSON->customer_type;
            }
            $timestamp = ''; //not in payload
            if ( property_exists($myJSON,'timestamp')) {
                $timestamp = $myJSON->timestamp;
            }

            $payload = [
                'dt_pedido' => date('Y-m-d'),
                'request_id' => $request_id ,
                'n_netcaixa' => $contrato, 
                'user_id' => 0, 
                'psp_code' => $psp_code, 
                'customer_identifier' =>  $customer_identifier, 
                'customer_identifier_type' => $customer_identifier_type, 
                'fiscal_number' => $fiscal_number, 
                'customer_type' => $customer_type, 
                'iban' => $iban, 
                'correlation_id_origin' => '', 
                'timestamp' => $timestamp,
                'success' =>  $success ,
                'correlation_id' => $correlation_id,
                'message' => $message,
                'errors_codes' => $errorcodes,
                'errors_values' => $errorvalues,
                'http_code' => $this->httpcode 
            ];
            \Log::channel($this->logchannel)->info('Payload: ' . print_r($payload, true)); 

            $payloadBD = PlPayload::insertGetId( $payload );
            $payload_id =  $payloadBD;

            \Log::channel($this->logchannel)->info('Payload saved to db with id ' . $payload_id );

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Payload NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }

    public function saveAssociacaoToDB( $request_id, $data , $contrato, $myJSON  )
    {
        \Log::channel($this->logchannel)->info('SAVE Associacao to DB'); 
        try {
            $errorcodes = '';
            $errorvalues = '';
            $success = false;
            $message = '';
            $correlation_id = '';

            //percorre erros para codigos e descricao
            if ( array_key_exists('success', $data)) {
                $success = $data['success'];
                if ( ! $data['success'] ) {
                    foreach( $data['errors'] as $error){
                        $errorcodes .=  trim($error['code']) . ',';
                        $errorvalues .=  trim($error['value']) . ',';
                    }
                }
            }else {
                $success = false;
            }

            if ( array_key_exists('message', $data)) {
                $message = $data['message'];
            }
            if ( array_key_exists('correlation_id', $data)) {
                $correlation_id = $data['correlation_id'];
            }

            $psp_code = ''; //not in payload
            if ( property_exists($myJSON,'psp_code')) {
                $psp_code = $myJSON->psp_code;
            }
            $customer_identifier = ''; //not in payload
            if ( property_exists($myJSON,'customer_identifier')) {
                $customer_identifier = $myJSON->customer_identifier;
            }
            $fiscal_number = ''; //not in payload
            if ( property_exists($myJSON,'fiscal_number')) {
                $fiscal_number = $myJSON->fiscal_number;
            }
            $iban = ''; //not in payload
            if ( property_exists($myJSON,'iban')) {
                $iban = $this->myJSON->iban;
            }
            $customer_identifier_type = 0; //not in payload
            if ( property_exists($myJSON,'customer_identifier_type')) {
                $customer_identifier_type = $myJSON->customer_identifier_type;
            }
            $customer_type = 0; //not in payload
            if ( property_exists($myJSON,'customer_type')) {
                $customer_type = $myJSON->customer_type;
            }
            $timestamp = ''; //not in payload
            if ( property_exists($myJSON,'timestamp')) {
                $timestamp = $myJSON->timestamp;
            }


            $associacao = [
                'dt_pedido' => date('Y-m-d'),
                'request_id' => $request_id ,
                'status' => 1, //ativo
                'n_netcaixa' =>  $contrato , 
                'user_id' => 0, 
                'psp_code' =>  $psp_code, 
                'customer_identifier' =>  $customer_identifier, 
                'customer_identifier_type' => $customer_identifier_type, 
                'fiscal_number' => $fiscal_number, 
                'customer_type' => $customer_type, 
                'iban' => $iban, 
                'correlation_id_origin' => $data['correlation_id'], 
                'correlation_id' => $data['correlation_id'],
                'timestamp' => $timestamp,
                'http_code' =>$this->httpcode 
            ];
            \Log::channel($this->logchannel)->info('Associação: ' . print_r($associacao, true));

            $associacaoBD = PlAssociacao::insertGetId( $associacao );
            $associacao_id =  $associacaoBD;

            \Log::channel($this->logchannel)->info('Associacao saved to db with id ' . $associacaoBD );
      
            return true;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Associacao NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
            return false;
        }
    }


    public function getActiveCorrelationIdFromDB($contrato,$myJSON )
    {
        try{

   
            $customer_identifier = ''; //not in payload
            if ( property_exists($myJSON,'customer_identifier')) {
                $customer_identifier = $myJSON->customer_identifier;
            }
            $fiscal_number = ''; //not in payload
            if ( property_exists($myJSON,'fiscal_number')) {
                $fiscal_number = $myJSON->fiscal_number;
            }
            $iban = ''; //not in payload
            if ( property_exists($myJSON,'iban')) {
                $iban = $this->myJSON->iban;
            }

            \Log::channel($this->logchannel)->info('Vai procurar associacoes ativas');
            \Log::channel($this->logchannel)->info('Contrato : ' . $contrato);
            \Log::channel($this->logchannel)->info('customer_identifier : ' . $customer_identifier);
            \Log::channel($this->logchannel)->info('fiscal_number : ' . $fiscal_number);
            \Log::channel($this->logchannel)->info('iban: ' . $iban);

            $correlation_id = PlAssociacao::where( 'n_netcaixa', $contrato )
                ->where('customer_identifier', $customer_identifier)
                ->where('fiscal_number', $fiscal_number)
                ->where('iban', $iban)
                ->where('status', 1)
                ->pluck('correlation_id')->first();
        
            if ( ! $correlation_id ) {
                \Log::channel($this->logchannel)->info('Não foi encontrado nenhuma associacao ativa...');
                return '';
            }

            \Log::channel($this->logchannel)->info('Foi encontrada uma associacao ativa/pendente : ' . $correlation_id);

            return $correlation_id;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return '';
        }
    }

    public function updateDissociacaoToDB( $correlation_id, $correlation_id_ativo )
    {
        \Log::channel($this->logchannel)->info('Faz update  Dissociação to DB'); 
        try {
            $dissociacaoBD = PlAssociacao::where('correlation_id', $correlation_id_ativo )->update( ['status'=> 0, 'correlation_id_origin' => $correlation_id] );
            \Log::channel($this->logchannel)->info('Dissociação updated' );
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Dissociação NOT updated to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }


    public function getHttpCode()
    {
        return $this->httpcode;
    }

}