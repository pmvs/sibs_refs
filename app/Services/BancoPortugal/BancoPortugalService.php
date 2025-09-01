<?php 

namespace App\Services\BancoPortugal;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use  App\Repositories\BancoPortugal\ProxyLookupRepository;
use App\Models\OAuth2JwtGenerator;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

use App\Models\ADFSToken;
use App\Models\BPPLRequest;
use App\Models\BancoPortugal\ProxyLookup\Cop;
use App\Models\Gba\Iban;

use App\Models\PlNotificacaoExpired;


class BancoPortugalService
{

    private $logchannel = 'bancoportugal';
    private $isConnProduction = false;
    private $sizeOfPassword = 10;
    private $utilizador = null;
    private $emensagemErro = '';
    private $sibsApiEndPointTestes = 'https://site1.sibsapimarket.com:8445/sibs-qly/apimarket';
    private $sibsApiEndPointTestes2 = 'https://site1.sibsapimarket.com:8445/sibs-qly/apisforbanks';
    private $dt_inicio_gba = '';
    private $dt_resposta_gba = '';
    private $dt_inicio_operacao = '';
    private $dt_resposta_operacao = '';
    private $sigla = '';
    private $iban = '';
    private $telemovel = '';
    private $identificador = '';
    private $nif = '';
    private $tipoCustomer = '';
    private $tipoIdentifier = '';
    private $metateste = [];
    private $payloadJson = '';
    private $token_id = '';
    private $customer_identifier ='';
    private $correlation_id_origin = '';

    public function __construct() 
    {
        $this->logchannel = 'bancoportugal';
        $this->isConnProduction = config('app.connection_prod');
        $this->utilizador = null;
        $this->mensagemErro = '';
        $this->sigla =  config('app.sigla');
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        // \Log::channel($this->logchannel)->info('__construct BancoPortugalService');
        // \Log::channel($this->logchannel)->info('Connection de Produção ? ' . ( $this->isConnProduction ? 'SIM' : 'NÃO' ) );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
       // \Log::channel($this->logchannel)->info('__construct_1 BancoPortugalService');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        //\Log::channel($this->logchannel)->info('__construct_2 BancoPortugalService');
    }


     /*
    |--------------------------------------------------------------------------
    | GETTERS
    |--------------------------------------------------------------------------
    */  
    public function getMensagemErro()
    {
        return $this->mensagemErro ;
    }

    public function getTokenID()
    {
        return $this->token_id ;
    }

    /*
    |--------------------------------------------------------------------------
    | SETTERS
    |--------------------------------------------------------------------------
    */  
    public function setLogChannel( $logchannel )
    {
        $this->logchannel = $logchannel;
    }

    public function setIsConnProduction( $isConnProduction )
    {
        $this->isConnProduction = $isConnProduction;
    }

    public function setUtilizador( $utilizador )
    {
        $this->utilizador = $utilizador;
    }

    public function setResource( $resource )
    {
        $this->resource = trim($resource);
    }

    public function setPayload($jsonPayload)
    {
        $this->payloadJson = $jsonPayload;
       // \Log::channel($this->logchannel)->info('BancoPortugalService : payloadJson SET');
    }

    public function setMetaTeste($metateste)
    {
        $this->metateste = $metateste;
        //\Log::channel($this->logchannel)->info('BancoPortugalService : metateste SET');
    }
 
 
    
    /*
    |--------------------------------------------------------------------------
    | METHODS PUBLIC - EXPOSED SERVICES
    |--------------------------------------------------------------------------
    */
    public function getJwtToken(  )
    {
        \Log::channel($this->logchannel)->info('BancoPortugalService : getJwtToken');
        try{

            //obtem certificado como array
            $certificate = $this->getCertificate();
           
            if ( ! $certificate ) {
                \Log::channel($this->logchannel)->info('BancoPortugalService : certificate not found');
                return '';
            }
            if( count($certificate) == 0 ){
                \Log::channel($this->logchannel)->info('BancoPortugalService : certificate not read');
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

    /**
     * @param $redirectURI - URL Encoded Redirect URI
     * @param $clientId - API Key
     * @param $scope - URL encoded, plus sign delimited list of scopes that your application requires. The 'offline_access' scope needed to request a refresh token is added by default.
     * @param $state - Arbitrary string value(s) to verify response and preserve application state
     * @return string - Full Authorization URL
    */


    public function getAccessToken()
    {
        try {
            $token_id = '';
            $access_token = '';

            //token from ADFS  / DB   
            //\Log::channel($this->logchannel)->info('getActiveTokenFromDB');
            $tokenBearer = $this->getActiveTokenFromDB();
            //\Log::channel($this->logchannel)->info(print_r($tokenBearer, true));
            if ( ! $tokenBearer ) {
                //nao ha tokens ativos
                \Log::channel($this->logchannel)->info('getTokenFromADFS');
                $tokenBearer = $this->getTokenFromADFS();
                \Log::channel($this->logchannel)->info(print_r($tokenBearer, true));
                if ( $tokenBearer['success'] ) {
                    $access_token =  $tokenBearer['response']['response']['access_token'];
                    $this->token_valido = true;
                    //\Log::channel($this->logchannel)->info('token válido');
                }else {
                    $this->token_valido = false;
                    \Log::channel($this->logchannel)->info('token inválido');
                    return '';
                }
            }else {
                //existe um token ativo
                //verifca se está válido
                //\Log::channel($this->logchannel)->info('Verifica validade do token');
                $criadoem = $tokenBearer->created_at;
                $expiraem = $tokenBearer->expires_in;
                $validoate = $tokenBearer->valid_until;

                // \Log::channel($this->logchannel)->info('Válido até ' . $validoate );
                // \Log::channel($this->logchannel)->info('Data : ' . date('Y-m-d H:m:i'));

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
        \Log::channel($this->logchannel)->info('BancoPortugalService : get jwtToken from BancoPortugalService');
        $jwtToken = $this->getJwtToken();
        if( $jwtToken  == '' ){
            \Log::channel($this->logchannel)->info('BancoPortugalService : jwtToken not set');
            return ['success' => false, 'error' => 'erro a criar o JWT' , 'response' => ''];
        }
        //get token from ADFS 
        \Log::channel($this->logchannel)->info('BancoPortugalService : get bearer token from ADFS');
        $tokenBearer = $this->getBearerTokenFromADFS(  $this->metateste['resourceteste'] , $jwtToken);  
        \Log::channel($this->logchannel)->info(print_r($tokenBearer, true));

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
        \Log::channel($this->logchannel)->info('BancoPortugalService : access_token: ' . $access_token );
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
            //\Log::channel($this->logchannel)->info('Foi encontrado na BD um token ativo');

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

    public function getBearerTokenFromADFS( $resource, $jwtToken )
    {
         \Log::channel($this->logchannel)->info('BancoPortugalService : getBearerTokenFromADFS');
 
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

    /*
    |--------------------------------------------------------------------------
    | API COP
    |--------------------------------------------------------------------------
    */

    public function getNomePrimeiroTitular( $contrato, $iban )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.getNomePrimeiroTitular ');
        \Log::channel($this->logchannel)->info( 'Contrato: ' . $contrato);
        \Log::channel($this->logchannel)->info( 'IBAN: ' . $iban);
       
        try {

            $iban2 = new Iban( $iban,$this->logchannel  );
            \Log::channel($this->logchannel)->info( $iban2->getInfoIBAN());
                
            if ( $iban2->getPaisIban() != 'PT' ) {
                \Log::channel($this->logchannel)->info( 'Não pode invocar API...Iban não nacional' );
                return response()->json([
                    'success' => false ,  
                    'correlation_id' => '' ,  
                    'account_holder' => '',  
                    'commercial_name' => '' , 
                    'nif_cop' => '',
                    'message' => 'IBAN not PT',
                    'errors' => ['IBAN not PT'],
                    ], 200);
            }

        }catch(\Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
      

        try {

            $banco = $this->getCodigoBanco();
            $banco_iban = substr($iban,4,4);
           
            if (  $banco_iban == $banco ) {

                \Log::channel($this->logchannel)->info( 'IBAN interno');

                //initialize service
                $apiCop= new ApiCop( $this->logchannel, $this->isConnProduction );
            
                // //set variables
                // $apiCop->setPSPCode( $banco );
                // $apiCop->setPSPCodeDestination( $banco_iban );
                // $apiCop->setIban($iban);
                // $apiCop->setContrato( $contrato );
                // $apiCop->setTimestamp();

                // //call action 
                // $response =  $apiCop->copS();

                // // try {
                // //     \Log::channel($this->logchannel)->info( 'Response');
                // //     \Log::channel($this->logchannel)->info( print_r($response, true));
                // // }catch( Exception $e) {
                // //     \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
                // // }

                // return $response;

                //e interno
                // \Log::channel($this->logchannel)->info( 'IBAN interno ' );
                //fetch information
                $cop = Cop::where('iban', $iban)
                ->where('n_titular', 1)
                ->first();

                if ( ! $cop ) {
                    return response()->json([
                        'success' => false ,  
                        'correlation_id' => 'interno' ,  
                        'account_holder' => '',  
                        'commercial_name' => '' , 
                        'nif_cop' => '',
                        'message' => 'IBAN not found',
                        'errors' => ['IBAN not found'],
                        ], 200);
                }

                //so preencher se for empresa
                $commercial_name = '';
                if ( $cop->tp_entidade == 'E' ) {
                    $commercial_name = trim($cop->nome);
                }

                $response = [
                    'success' => true ,  
                    'correlation_id' => 'interno' ,  
                    'account_holder' => trim($cop->nome) ,  
                    'commercial_name' => $commercial_name , 
                    'nif_cop' => trim($cop->nif),
                    'message' => 'Request successful'
                ];
                
                try {
                    \Log::channel($this->logchannel)->info( 'Response');
                    \Log::channel($this->logchannel)->info( print_r($response, true));
                }catch( Exception $e) {
                    \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
                }

                return ['error' => false, 'response' => json_encode($response), 'status_code' => 200];

             
                //return response()->json($response, 200);

            }else {
                //e externo
                \Log::channel($this->logchannel)->info( 'IBAN externo ' );
               
                //initialize service
                $apiCop= new ApiCop( $this->logchannel, $this->isConnProduction );
                
                //set variables
                $apiCop->setPSPCode( $banco );
                $apiCop->setPSPCodeDestination( $banco_iban );
                $apiCop->setIban($iban);
                $apiCop->setContrato( $contrato );
                $apiCop->setTimestamp();

                //call action 
                $response =  $apiCop->copS();

                try {
                    \Log::channel($this->logchannel)->info( 'Response');
                    \Log::channel($this->logchannel)->info( print_r($response, true));
                }catch( Exception $e) {
                    \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
                }

                return $response;

            }



        }catch( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return response()->json([
                'account_holder' => '',  
                'commercial_name' => '' , 
                'message' => 'IBAN not found ERROR'
                ], 404);
        }

    }

    public function invocaCopB($contrato, $copb, $psp_destination)
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.invocaCopB ');
        \Log::channel($this->logchannel)->info( 'Contrato: ' . $contrato);
        \Log::channel($this->logchannel)->info( 'COPB: ' . print_r($copb,true));
        \Log::channel($this->logchannel)->info( 'PSP Destination: ' . $psp_destination);
        try {

            //initialize service
            $apiCop= new ApiCop( $this->logchannel, $this->isConnProduction );
                        
            //set variables
            $banco = $this->getCodigoBanco();
            $apiCop->setPSPCode( $banco );
            $apiCop->setPSPCodeDestination( $psp_destination );
            $apiCop->setCopB($copb);
            $apiCop->setContrato( $contrato );
            $apiCop->setTimestamp();

            //call action 
            $response =  $apiCop->copB();

            // try {
            //     \Log::channel($this->logchannel)->info( 'Response');
            //     \Log::channel($this->logchannel)->info( print_r($response, true));
            // }catch( Exception $e) {
            //     \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            // }

            return $response;

        }catch( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return response()->json([
                'items' => [],
                'message' => 'Request unsuccessful',
                ], 404);
        }

    }

    public function invocaCopBTeste($contrato, $nrcopb, $copb, $psp_destination)
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.invocaCopBTeste ');
        // \Log::channel($this->logchannel)->info( 'Contrato: ' . $contrato);
        // \Log::channel($this->logchannel)->info( 'NRCOPB: ' . $nrcopb);
        //\Log::channel($this->logchannel)->info( 'PSP Destination: ' . $psp_destination);
       
        try {

            //initialize service
            $apiCop= new ApiCop( $this->logchannel, $this->isConnProduction );
                        
            //set variables
            $banco = $this->getCodigoBanco();
            $apiCop->setPSPCode( $banco );
            $apiCop->setPSPCodeDestination( $psp_destination );
            $apiCop->setCopB($copb);
            $apiCop->setContrato( $contrato );
            $apiCop->setTimestamp();

            //call action 
            $response =  $apiCop->copBTeste($nrcopb);

            // try {
            //     \Log::channel($this->logchannel)->info( 'Response');
            //     \Log::channel($this->logchannel)->info( print_r($response, true));
            // }catch( Exception $e) {
            //     \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            // }

            return $response;

        }catch( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return response()->json([
                'items' => [],
                'message' => 'Request unsuccessful',
                ], 404);
        }

    }

    private function getCodigoBanco()
    {
        $cdbanco = '0000';
        //caso nao consiga ir ao bparametros
        switch( config('app.sigla') ) {
            case 'TVD':
                $cdbanco = '5340';
                break;
            case 'MAF':
                $cdbanco = '5200';
                break;
            case 'BOM':
                $cdbanco = '0098';
                break;
            case 'CHM':
                $cdbanco = '0097';
                break;
            default:
                $cdbanco = '0000';
                break;
        }
        return $cdbanco;
    }

    public function getCopB( $contrato, $copb, $psp_destination  )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.getCopB ');
        \Log::channel($this->logchannel)->info( 'Contrato: ' . $contrato);
        \Log::channel($this->logchannel)->info( 'PSP destination : ' . $psp_destination);
        \Log::channel($this->logchannel)->info( 'COPB: ' . print_r($copb, true));
     
        try {

                //initialize service
                $apiCop= new ApiCop( $this->logchannel, $this->isConnProduction );

                //set variables
                $banco = $this->getCodigoBanco();
                $apiCop->setPSPCode( $banco );
                $apiCop->setPSPCodeDestination( $psp_destination );
                $apiCop->setCopB($copb);
                $apiCop->setContrato( $contrato );
                $apiCop->setTimestamp();
                

                //call action 
                $response =  $apiCop->copB();

                try {
                    \Log::channel($this->logchannel)->info( 'Response');
                    \Log::channel($this->logchannel)->info( print_r($response, true));
                }catch( Exception $e) {
                    \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
                }

                return $response;


        }catch( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return response()->json([
                'account_holder' => '',  
                'commercial_name' => '' , 
                'message' => 'IBAN not found ERROR'
                ], 404);
        }

    }
    /*
    |--------------------------------------------------------------------------
    | API PROXY LOOKUP
    |--------------------------------------------------------------------------
    */
    public function criaAssociacao($contrato, $iban,  $identificador, $nif , $tipo_customer,$tipo_identifier )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.criaAssociacao');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            $this->contrato = $contrato;  
            $this->iban = str_replace(' ', '', trim($iban));  
            $this->identificador = $this->formataTelemovel(trim($identificador));
            $this->nif = $this->formataNIF(trim($nif)); 
            $this->tipoCustomer = $tipo_customer;
            $this->tipoIdentifier = $tipo_identifier;

            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);

            $response =  $this->invocaApiProxyLookup( config('enums.tipoOperacaoProxyLookup.Insert') );

            \Log::channel($this->logchannel)->info( 'Analisa resposta para saber se insere no IFX a associação...');
            \Log::channel($this->logchannel)->info( print_r($response, true) );
      
            if ( $response['error']) {
                \Log::channel($this->logchannel)->info( 'Existiram erros...vai abortar aqui.');
                return false;
            }

            \Log::channel($this->logchannel)->info($response['response']); 
            \Log::channel($this->logchannel)->info(gettype($response['response']) ); 

            $data = json_decode( $response['response'], true);
            \Log::channel($this->logchannel)->info(gettype($data) ); 

            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                    return false;
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }else {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }
                }
                \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                return false;
            }else {
                $correlation_id = $data['correlation_id'];
            }

            \Log::channel($this->logchannel)->info( 'Status Code ? ' . $response['status_code']);
            \Log::channel($this->logchannel)->info( 'Sucesso ? ' . $data['success']);
            \Log::channel($this->logchannel)->info( 'correlation_id: ' . $correlation_id);

            return $response;

            // //passa aqui o correlation_id
            // \Log::channel($this->logchannel)->info( 'Tratamento IFX...');
            // if ( $data['success'] &&  $response['status_code'] == 201) { 
            //     \Log::channel($this->logchannel)->info( 'Vai remover associacoes existentes IFX');
            //     $this->removeAssociacoesProxyLookup($correlation_id);

            //     \Log::channel($this->logchannel)->info( 'Vai inserir associação IFX');
            //     return $this->insereAssociacaoProxyLookup($correlation_id);
            // }else {
            //     \Log::channel($this->logchannel)->warning( 'O que vai fazer aqui ??');
            //     \Log::channel($this->logchannel)->warning( 'Por exemplo, analisar : [response] => {"success":false,"correlation_id":"00-999436381807fe006545c4cde72a619c-b13a2af43452bafa-01","message":"Request unsuccessful. The following errors were found.","errors":[{"code":"E_SPL_ASSOC_MN_DUPLICATED_1","value":"There is an active association with the same customer identifier, IBAN and NIF (00-6980bf5ef7559e099263935a94775138-ebe232274eff3d73-01)."}]}');
            //     //return $this->insereAssociacaoProxyLookup($correlation_id);
            //     return false;
            // }


        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function removeAssociacao($contrato, $iban,  $identificador, $nif , $tipo_customer,$tipo_identifier )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.removeAssociacao');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            $this->contrato = $contrato;  
            $this->iban = str_replace(' ', '', trim($iban));  
            $this->identificador = $this->formataTelemovel(trim($identificador));
            $this->nif = $this->formataNIF(trim($nif)); 
            $this->tipoCustomer = $tipo_customer;
            $this->tipoIdentifier = $tipo_identifier;

            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);

            //$response = $this->invocaApiProxyLookup( config('enums.tipoOperacaoProxyLookup.Delete') );
            //analisa resposta aqui
            $response = $this->invocaApiProxyLookup( config('enums.tipoOperacaoProxyLookup.Delete') );
          
            \Log::channel($this->logchannel)->info( 'Analisa resposta para saber se remove no IFX a associação...');
            \Log::channel($this->logchannel)->info( print_r($response, true) );

            \Log::channel($this->logchannel)->info($response['response']); 
            \Log::channel($this->logchannel)->info(gettype($response['response']) );

            //passa aqui o correlation_id para insert
            $data = json_decode($response['response'], true);
            \Log::channel($this->logchannel)->info(gettype($data) ); 
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                    return $data;
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return $data;
                    }else {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return $data;
                    }
                }
                \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                return $data;
            }else {
                $correlation_id = $data['correlation_id'];
            }

            \Log::channel($this->logchannel)->info( 'Status Code ? ' . $response['status_code']);
            \Log::channel($this->logchannel)->info( 'Sucesso ? ' . $data['success']);
            \Log::channel($this->logchannel)->info( 'correlation_id: ' . $correlation_id);
         
            return $response;

            //passa aqui o correlation_id
            if ( $data['success'] &&  $response['status_code'] == 200) { 
                return $response;
                // \Log::channel($this->logchannel)->info( 'Vai remover associação IFX');
                // return $this->removeAssociacaoProxyLookup($correlation_id);
            }else {
                \Log::channel($this->logchannel)->info( 'O que vai fazer aqui ??');
                return $data;
            }
  
        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function confirmaAssociacao($contrato, $iban,  $identificador, $nif , $tipo_customer,$tipo_identifier )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.confirmaAssociacao');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            $this->contrato = $contrato;  
            $this->iban = str_replace(' ', '', trim($iban));  
            $this->identificador = trim($identificador);
            $this->nif = $this->formataNIF(trim($nif)); 
            $this->tipoCustomer = $tipo_customer;
            $this->tipoIdentifier = $tipo_identifier;

            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);

            //$response = $this->invocaApiProxyLookup( config('enums.tipoOperacaoProxyLookup.Delete') );
            //analisa resposta aqui
            $response = $this->invocaApiProxyLookup( config('enums.tipoOperacaoProxyLookup.Confirmation') );
          
            \Log::channel($this->logchannel)->info( 'Analisa resposta...');
            \Log::channel($this->logchannel)->info( print_r($response, true) );

            \Log::channel($this->logchannel)->info($response['response']); 
            \Log::channel($this->logchannel)->info(gettype($response['response']) );

            //passa aqui o correlation_id para insert
            $data = json_decode($response['response'], true);
            \Log::channel($this->logchannel)->info(gettype($data) ); 
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                    return false;
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }else {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }
                }
                \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                return false;
            }else {
                $correlation_id = $data['correlation_id'];
            }

            \Log::channel($this->logchannel)->info( 'Status Code ? ' . $response['status_code']);
            \Log::channel($this->logchannel)->info( 'Sucesso ? ' . $data['success']);
            \Log::channel($this->logchannel)->info( 'correlation_id: ' . $correlation_id);
         
            return $response;
            // //passa aqui o correlation_id
            // if ( $data['success'] &&  $response['status_code'] == 200) { 
                
            //     \Log::channel($this->logchannel)->info( 'Vai remover associação IFX');
            //     return $this->removeAssociacaoProxyLookup($correlation_id);
            // }else {
            //     \Log::channel($this->logchannel)->info( 'O que vai fazer aqui ??');
            //     return false;
            // }
  
        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function reativarAssociacao($contrato, $iban,  $identificador, $nif , $tipo_customer,$tipo_identifier,$correlation_id_origin )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.reativarAssociacao');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            $this->contrato = $contrato;  
            $this->iban = str_replace(' ', '', trim($iban));  
            $this->identificador =$identificador;
            $this->nif = $nif; 
            $this->tipoCustomer = $tipo_customer;
            $this->tipoIdentifier = $tipo_identifier;
            $this->correlation_id_origin = $correlation_id_origin;

            \Log::channel($this->logchannel)->info( 'CorrelationID origin: ' . $this->correlation_id_origin);
            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);

            $response =  $this->invocaApiProxyLookup( 6 );

            \Log::channel($this->logchannel)->info( 'Analisa resposta para saber se insere no IFX a associação...');
            \Log::channel($this->logchannel)->info( print_r($response, true) );
      
            if ( $response['error']) {
                \Log::channel($this->logchannel)->info( 'Existiram erros...vai abortar aqui.');
                return false;
            }

            \Log::channel($this->logchannel)->info($response['response']); 
            \Log::channel($this->logchannel)->info(gettype($response['response']) ); 

            $data = json_decode( $response['response'], true);
            \Log::channel($this->logchannel)->info(gettype($data) ); 

            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                    return false;
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }else {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }
                }
                \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                return false;
            }else {
                $correlation_id = $data['correlation_id'];
            }

            \Log::channel($this->logchannel)->info( 'Status Code ? ' . $response['status_code']);
            \Log::channel($this->logchannel)->info( 'Sucesso ? ' . $data['success']);
            \Log::channel($this->logchannel)->info( 'correlation_id: ' . $correlation_id);
            \Log::channel($this->logchannel)->info( 'correlation_id_origin: ' . $this->correlation_id_origin);

            //update das notificacoes pendentes
            if ( $response['status_code'] == 200 ) {
                \Log::channel($this->logchannel)->info( 'Faz update das notificacoes pendentes....deixou de estar pendente! Passou a ativa');
                $status = 'A'; //ativo
                $updated = $this->updateNotificacaoPendente($status);
            }
          

            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function updateNotificacaoPendente($status)
    {
        try {
            $updated =  PlNotificacaoExpired::where( 'correlation_id_origin', $this->correlation_id_origin )
            ->update(['status' => $status, 'dt_status' => date('Y-m-d H:i:s')]);
            \Log::channel($this->logchannel)->info( 'PlNotificacaoExpired Update OK');
         }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( 'PlNotificacaoExpired Update NOT OK');
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
      
    }

    public function eliminarAssociacaoPendente($contrato, $iban,  $identificador, $nif , $tipo_customer,$tipo_identifier,$correlation_id_origin )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.eliminarAssociacaoPendente');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            $this->contrato = $contrato;  
            $this->iban = str_replace(' ', '', trim($iban));  
            $this->identificador =$identificador;
            $this->nif = $nif; 
            $this->tipoCustomer = $tipo_customer;
            $this->tipoIdentifier = $tipo_identifier;
            $this->correlation_id_origin = $correlation_id_origin;

            \Log::channel($this->logchannel)->info( 'CorrelationID origin: ' . $this->correlation_id_origin);
            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);

            $response =  $this->invocaApiProxyLookup( 7 );

            \Log::channel($this->logchannel)->info( print_r($response, true) );
      
            if ( $response['error']) {
                \Log::channel($this->logchannel)->info( 'Existiram erros...vai abortar aqui.');
                return false;
            }

            \Log::channel($this->logchannel)->info($response['response']); 
            \Log::channel($this->logchannel)->info(gettype($response['response']) ); 

            $data = json_decode( $response['response'], true);
            \Log::channel($this->logchannel)->info(gettype($data) ); 

            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                    return false;
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }else {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }
                }
                \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                return false;
            }else {
                $correlation_id = $data['correlation_id'];
            }

            \Log::channel($this->logchannel)->info( 'Status Code ? ' . $response['status_code']);
            \Log::channel($this->logchannel)->info( 'Sucesso ? ' . $data['success']);
            \Log::channel($this->logchannel)->info( 'correlation_id: ' . $correlation_id);
            \Log::channel($this->logchannel)->info( 'correlation_id_origin: ' . $this->correlation_id_origin);
          
            if ( $response['status_code'] == 200 ) {
                //update das notificacoes pendentes
                \Log::channel($this->logchannel)->info( 'Faz update das notificacoes pendentes....deixou de estar pendente! Passou a removida');
                $status = 'R'; //removed
                $updated = $this->updateNotificacaoPendente($status);
            }
           

            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function consultaContatos($contrato, $phone_book )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.consultaContatos');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            $this->contrato = $contrato;  
            $this->phone_book = $phone_book;  

            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);
            \Log::channel($this->logchannel)->info( 'Phone Book: ' . print_r( $this->phone_book, true));
            //$response = $this->invocaApiProxyLookup( config('enums.tipoOperacaoProxyLookup.Delete') );
            //analisa resposta aqui
            $response = $this->invocaApiProxyLookup( config('enums.tipoOperacaoProxyLookup.Contacts') );
          
            \Log::channel($this->logchannel)->info( 'Analisa resposta...');
            \Log::channel($this->logchannel)->info( print_r($response, true) );

            \Log::channel($this->logchannel)->info($response['response']); 
            \Log::channel($this->logchannel)->info(gettype($response['response']) );

            //passa aqui o correlation_id para insert
            $data = json_decode($response['response'], true);
            \Log::channel($this->logchannel)->info(gettype($data) ); 
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                    return false;
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }else {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }
                }
                \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                return false;
            }else {
                $correlation_id = $data['correlation_id'];
            }

            \Log::channel($this->logchannel)->info( 'Status Code ? ' . $response['status_code']);
            \Log::channel($this->logchannel)->info( 'Sucesso ? ' . $data['success']);
            \Log::channel($this->logchannel)->info( 'correlation_id: ' . $correlation_id);
         
            return $response;
            // //passa aqui o correlation_id
            // if ( $data['success'] &&  $response['status_code'] == 200) { 
                
            //     \Log::channel($this->logchannel)->info( 'Vai remover associação IFX');
            //     return $this->removeAssociacaoProxyLookup($correlation_id);
            // }else {
            //     \Log::channel($this->logchannel)->info( 'O que vai fazer aqui ??');
            //     return false;
            // }
  
        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function obtemIban($contrato, $customer_identifier )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.obtemIban');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            $this->contrato = $contrato;  
            $this->customer_identifier = $customer_identifier;  

            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);
            \Log::channel($this->logchannel)->info( 'Identificador: ' .$this->customer_identifier);
           
            //analisa resposta aqui
            $response = $this->invocaApiProxyLookup( 5 );
          
            \Log::channel($this->logchannel)->info( 'Analisa resposta...');
            \Log::channel($this->logchannel)->info( print_r($response, true) );

            \Log::channel($this->logchannel)->info($response['response']); 
            \Log::channel($this->logchannel)->info(gettype($response['response']) );

            //passa aqui o correlation_id para insert
            $data = json_decode($response['response'], true);
            \Log::channel($this->logchannel)->info(gettype($data) ); 
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                    return false;
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }else {
                        \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                        return false;
                    }
                }
                \Log::channel($this->logchannel)->info( 'Status code de ERRO');
                return false;
            }else {
                $correlation_id = $data['correlation_id'];
            }

            \Log::channel($this->logchannel)->info( 'Status Code ? ' . $response['status_code']);
            \Log::channel($this->logchannel)->info( 'Sucesso ? ' . $data['success']);
            \Log::channel($this->logchannel)->info( 'correlation_id: ' . $correlation_id);
         
            return $response;
            // //passa aqui o correlation_id
            // if ( $data['success'] &&  $response['status_code'] == 200) { 
                
            //     \Log::channel($this->logchannel)->info( 'Vai remover associação IFX');
            //     return $this->removeAssociacaoProxyLookup($correlation_id);
            // }else {
            //     \Log::channel($this->logchannel)->info( 'O que vai fazer aqui ??');
            //     return false;
            // }
  
        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }
    
    private function invocaApiProxyLookup( $tipoOperacao )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.invocaApiProxyLookup ' . $tipoOperacao);
        try {
            switch( $tipoOperacao )
            {
                case '1':
                    return $this->apiProxyLookupInsert();
                case '2':
                    return $this->apiProxyLookupDelete();
                case '3':
                    return $this->apiProxyLookupConfirmation();
                case '4':
                    return $this->apiProxyLookupContacts();
                case '5':
                    return $this->apiProxyLookupAccount();
                case '6':
                    return $this->apiProxyLookupReativate();
                case '7':
                    return $this->apiProxyLookupEliminate();
                default: 
                    \Log::channel($this->logchannel)->info( 'BancoPortugalService.invocaApiProxyLookup : Tipo de API não reconhecida ' . $tipoOperacao);
                    break;
            }
        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
    }

    private function apiProxyLookupInsert()
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.apiProxyLookupInsert ');
        
        try {

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
            
            //set variables
            $apiProxyLookup->setPSPCode( $this->getCodigoBanco() );
            $apiProxyLookup->setCustomerIdentifier( $this->identificador );
            $apiProxyLookup->setCustomerIdentifierType( $this->tipoIdentifier );
            $apiProxyLookup->setFiscalNumber(  $this->nif );
            $apiProxyLookup->setCustomerType( $this->tipoCustomer );
            $apiProxyLookup->setIban(  $this->iban );
            $apiProxyLookup->setCorrelationIdOrigin( '' );
            $apiProxyLookup->setContrato( $this->contrato );
            $apiProxyLookup->setTimestamp();

            //call action 
            $response = $apiProxyLookup->insert();
            try {
                \Log::channel($this->logchannel)->info( 'Response');
                \Log::channel($this->logchannel)->info( print_r($response, true));
            }catch( Exception $e) {
                \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            }

        
            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

    }

    private function apiProxyLookupDelete()
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.apiProxyLookupDelete ');
        
        try {

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
            
            //set variables
            $apiProxyLookup->setPSPCode( $this->getCodigoBanco() );
            $apiProxyLookup->setCustomerIdentifier( $this->identificador );
            $apiProxyLookup->setCustomerIdentifierType( $this->tipoIdentifier );
            $apiProxyLookup->setFiscalNumber(  $this->nif );
            $apiProxyLookup->setCustomerType( $this->tipoCustomer );
            $apiProxyLookup->setIban(  $this->iban );
            $apiProxyLookup->setCorrelationIdOrigin( '' );
            $apiProxyLookup->setContrato( $this->contrato );
            $apiProxyLookup->setTimestamp();

            //call action 
            $response =  $apiProxyLookup->delete();
           try {
            \Log::channel($this->logchannel)->info( 'Response');
            \Log::channel($this->logchannel)->info( print_r($response, true));
            }catch( Exception $e) {
                \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            }

            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

    }

    private function apiProxyLookupConfirmation()
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.apiProxyLookupConfirmation ');
        
        try {

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
            
            //set variables
            $apiProxyLookup->setPSPCode( $this->getCodigoBanco() );
            $apiProxyLookup->setCustomerIdentifier( $this->identificador );
            $apiProxyLookup->setCustomerIdentifierType( $this->tipoIdentifier );
            $apiProxyLookup->setFiscalNumber(  $this->nif );
            $apiProxyLookup->setCustomerType( $this->tipoCustomer );
            $apiProxyLookup->setIban(  $this->iban );
            $apiProxyLookup->setCorrelationIdOrigin( '' );
            $apiProxyLookup->setContrato( $this->contrato );
            $apiProxyLookup->setTimestamp();

            //call action 
            $response =  $apiProxyLookup->confirmation();

           try {
            \Log::channel($this->logchannel)->info( 'Response');
            \Log::channel($this->logchannel)->info( print_r($response, true));
            }catch( Exception $e) {
                \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            }

            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

    }

    private function apiProxyLookupContacts()
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.apiProxyLookupContacts ');
        
        try {

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
            
            //set variables
            $apiProxyLookup->setPSPCode( $this->getCodigoBanco() );
            $apiProxyLookup->setPhoneBook($this->phone_book);
            $apiProxyLookup->setContrato( $this->contrato );
            $apiProxyLookup->setTimestamp();

            //call action 
            $response =  $apiProxyLookup->contacts();

           try {
            \Log::channel($this->logchannel)->info( 'Response');
            \Log::channel($this->logchannel)->info( print_r($response, true));
            }catch( Exception $e) {
                \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            }

            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

    }

    private function apiProxyLookupAccount()
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.apiProxyLookupAccount ');
        
        try {

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
            
            //set variables
            $apiProxyLookup->setPSPCode( $this->getCodigoBanco() );
            $apiProxyLookup->setCustomerIdentifier($this->customer_identifier);
            $apiProxyLookup->setContrato( $this->contrato );
            $apiProxyLookup->setTimestamp();

            //call action 
            $response =  $apiProxyLookup->account();

           try {
            \Log::channel($this->logchannel)->info( 'Response');
            \Log::channel($this->logchannel)->info( print_r($response, true));
            }catch( Exception $e) {
                \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            }

            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

    }

    private function apiProxyLookupReativate()
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.apiProxyLookupReativate ');
        
        try {

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
            
            //set variables
            $apiProxyLookup->setPSPCode( $this->getCodigoBanco() );
            $apiProxyLookup->setCustomerIdentifier( $this->identificador );
            $apiProxyLookup->setCustomerIdentifierType( $this->tipoIdentifier );
            $apiProxyLookup->setFiscalNumber(  $this->nif );
            $apiProxyLookup->setCustomerType( $this->tipoCustomer );
            $apiProxyLookup->setIban(  $this->iban );
            $apiProxyLookup->setCorrelationIdOrigin(  $this->correlation_id_origin);
            $apiProxyLookup->setContrato( $this->contrato );
            $apiProxyLookup->setTimestamp();

            //call action 
            $response = $apiProxyLookup->reativate();
            try {
                \Log::channel($this->logchannel)->info( 'Response');
                \Log::channel($this->logchannel)->info( print_r($response, true));
            }catch( Exception $e) {
                \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            }

        
            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

    }

    private function apiProxyLookupEliminate()
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.apiProxyLookupEliminate ');
        
        try {

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
            
            //set variables
            $apiProxyLookup->setPSPCode( $this->getCodigoBanco() );
            $apiProxyLookup->setCustomerIdentifier( $this->identificador );
            $apiProxyLookup->setCustomerIdentifierType( $this->tipoIdentifier );
            $apiProxyLookup->setFiscalNumber(  $this->nif );
            $apiProxyLookup->setCustomerType( $this->tipoCustomer );
            $apiProxyLookup->setIban(  $this->iban );
            $apiProxyLookup->setCorrelationIdOrigin(  $this->correlation_id_origin);
            $apiProxyLookup->setContrato( $this->contrato );
            $apiProxyLookup->setTimestamp();

            //call action 
            $response = $apiProxyLookup->eliminate();
            try {
                \Log::channel($this->logchannel)->info( 'Response');
                \Log::channel($this->logchannel)->info( print_r($response, true));
            }catch( Exception $e) {
                \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            }

        
            return $response;

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

    }

    private function formataTelemovel( $numero )
    {
        try{
        
            if ( trim($numero) != '' ) {
        
                $startString = '+351';
                $len = strlen($startString); 
                $comeca = (substr($numero, 0, $len) === $startString); 
                if ( $comeca ) { return $numero; }
                        
                $startString = '00351';
                $len = strlen($startString); 
                $comeca = (substr($numero, 0, $len) === $startString); 
                       
                if ( $comeca ) {
                    return '+' . substr( $numero, 2,  strlen($numero) );
                } else {
            
                    $startString = '351';
                    $len = strlen($startString); 
                    $comeca = (substr($numero, 0, $len) === $startString); 
    
                    if ( $comeca ) {
                        return '+' . $numero;
                    } else {
            
                        $len = strlen($numero); 
            
                        if ( $len == 9 ) {
    
                            $startString = '96';
                            $len = strlen($startString); 
                            $comeca = (substr($numero, 0, $len) === $startString); 
                            if ( $comeca ) { 
                                return '+351' . $numero;
                            }
    
                            $startString = '91';
                            $len = strlen($startString); 
                            $comeca = (substr($numero, 0, $len) === $startString); 
                            if ( $comeca ) { 
                                return '+351' . $numero;
                            }
    
                            $startString = '93';
                            $len = strlen($startString); 
                            $comeca = (substr($numero, 0, $len) === $startString); 
                            if ( $comeca ) { 
                                return '+351' . $numero;
                            }
    
                            $startString = '92';
                            $len = strlen($startString); 
                            $comeca = (substr($numero, 0, $len) === $startString); 
                            if ( $comeca ) { 
                                return '+351' . $numero;
                            }
    
                        } else {
                            //TO DO : analisar pais
                            return $numero; 
                        }
                        
                    }
            
                }
            
            } else {
                return false;
            }

        }catch( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
    }

    private function formataNIF( $nif )
    {
        try{
        
            if ( trim($nif) != '' ) {
                if ( strlen(trim($nif)) == 9 ) {
                    return 'PT' . trim($nif);
                }
                if ( strlen(trim($nif)) == 11 ) {
                    return trim($nif);
                }
            }

            return trim($nif);
 
        }catch( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return '';
        }
    }
 


    public function existeAssociacaoAtiva($tipoCustomer, $tipoIdentifier,  $contrato, $telemovel, $nif )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.existeAssociacaoAtiva ');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras


            $this->contrato = trim($contrato);
            $this->telemovel = $this->formataTelemovel(trim($telemovel));
            $this->nif = $this->formataNIF(trim($nif)); 
            $this->tipoCustomer = $tipoCustomer;
            $this->tipoIdentifier = $tipoIdentifier;

            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);
            \Log::channel($this->logchannel)->info( 'Telemóvel: ' . $this->telemovel);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);

            //inicia repositorio
            $repo = new ProxyLookupRepository($this->logchannel, $this->isConnProduction);

            return $repo->existeAssociacaoAtiva( $this->contrato, $this->telemovel, $this->nif, $this->tipoIdentifier,$this->tipoCustomer);

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function insereAssociacaoProxyLookup( $correlation_id )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.insereAssociacaoProxyLookup ');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);
            \Log::channel($this->logchannel)->info( 'Correlation_id: ' . $correlation_id);


            //inicia repositorio
            $repo = new ProxyLookupRepository($this->logchannel, $this->isConnProduction);

            return $repo->insereAssociacaoAtiva( $correlation_id, $this->contrato, $this->identificador, $this->nif, $this->tipoIdentifier,$this->tipoCustomer, $this->iban);

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function removeAssociacaoProxyLookup( $correlation_id )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.removeAssociacaoProxyLookup ');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);
            \Log::channel($this->logchannel)->info( 'Correlation_id: ' . $correlation_id);


            //inicia repositorio
            $repo = new ProxyLookupRepository($this->logchannel, $this->isConnProduction);

            return $repo->removeAssociacao( $correlation_id,$this->contrato, $this->identificador, $this->nif, $this->tipoIdentifier,$this->tipoCustomer, $this->iban);

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    public function removeAssociacoesProxyLookup( $correlation_id )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.removeAssociacoesProxyLookup ');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);
            \Log::channel($this->logchannel)->info( 'Identificador: ' . $this->identificador);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'Correlation_id: ' . $correlation_id);


            //inicia repositorio
            $repo = new ProxyLookupRepository($this->logchannel, $this->isConnProduction);

            return $repo->removeAssociacoes( $correlation_id, $this->contrato, $this->identificador, $this->nif, $this->tipoIdentifier,$this->tipoCustomer, $this->iban);

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }
 
    public function getAssociacaoProxyLookup( )
    {
        \Log::channel($this->logchannel)->info( 'BancoPortugalService.getAssociacaoProxyLookup ');
       
        try{
            //inicializa variaveis para associacao e formata de acordo com as regras
            \Log::channel($this->logchannel)->info( 'Tipo Customer: ' . $this->tipoCustomer);
            \Log::channel($this->logchannel)->info( 'Tipo Identifier: ' . $this->tipoIdentifier);
            \Log::channel($this->logchannel)->info( 'Contrato: ' . $this->contrato);
            \Log::channel($this->logchannel)->info( 'Telemóvel: ' . $this->telemovel);
            \Log::channel($this->logchannel)->info( 'Número Fiscal: ' . $this->nif);
            \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);

            //inicia repositorio
            $repo = new ProxyLookupRepository($this->logchannel, $this->isConnProduction);

            return $repo->getAssociacaoAtiva( $this->contrato, $this->telemovel, $this->nif, $this->tipoIdentifier,$this->tipoCustomer, $this->iban);

        }catch( Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }
     
    }

    /*
    |--------------------------------------------------------------------------
    | CURL CALLS
    |--------------------------------------------------------------------------
    */
  
    public function callCurl( $nomeTeste, $parametro )
    {
        \Log::channel($this->logchannel)->info('----callCurl----');
        try{
            switch( $nomeTeste )
            {
                case 'ia-text':
                    $isPost = true;
                    $useBody = true;
                    $api_endpoint = 'https://api.openai.com/v1/text/generate';
                    return $this->callCurlIA( $api_endpoint, $parametro, $isPost, $useBody );
              
                case 'ia-models':
                    $isPost = false;
                    $useBody = false;
                    $api_endpoint = 'https://api.openai.com/v1/models';
                    return $this->callCurlIA( $api_endpoint, $parametro, $isPost, $useBody );

                default: 
                    return 'curl não implementado para ' . $nomeTeste;
            }


        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $this->mensagemErro = 'Curl não executado. Erro interno.';
            return true;
        }
    }
 
    
    /*
    |--------------------------------------------------------------------------
    | COUNTERS  
    |--------------------------------------------------------------------------
    */
    private function initTimerCounter()
    {
        $date = date_create();
        $this->dt_inicio_gba = date_format($date,"Y-m-d H:i:s.u");  
    }

    private function initOperationTimerCounter()
    {
        $date = date_create();
        $this->dt_inicio_operacao = date_format($date,"Y-m-d H:i:s.u");  
    }

    private function initOperationTimerInfo($datainfo)
    {

        $date = date_create();
        $dt = date_format($date,"Y-m-d H:i:s.u");  

        $this->dt_inicio_operacao = $dt;  

        \Log::channel($this->logchannel)->info($datainfo .  ' ' . $dt);
 

    }

    private function endTimerCounter()
    {
        $date = date_create();
        $this->dt_resposta_gba = date_format($date,"Y-m-d H:i:s.u");  
    }

    private function endOperationTimerCounter()
    {
        $date = date_create();
        $this->dt_resposta_operacao = date_format($date,"Y-m-d H:i:s.u");  
    }

    private function writeTempos()
    {
        try {

            $date = date_create();
            $this->dt_resposta_gba = date_format($date,"Y-m-d H:i:s.u");  

            $differencegba = calculateTransactionDuration($this->dt_inicio_gba, $this->dt_resposta_gba);

            $datainfo ='Inicio Pedido: ' .  $this->dt_inicio_gba . PHP_EOL . 
                        'Resposta ao pedido : ' . $this->dt_resposta_gba . PHP_EOL . 
                        'Tempos GBA: ' . $differencegba;
 
            \Log::channel($this->logchannel)->info($datainfo);
          
        } catch(Exception $e){
            \Log::channel($this->logchannel)->error($e->getMessage());
        }
    }

    private function writeTemposOperation()
    {
        try {

            $date = date_create();
            $this->dt_resposta_operacao = date_format($date,"Y-m-d H:i:s.u");  

            $differencegba = calculateTransactionDuration($this->dt_inicio_operacao, $this->dt_resposta_operacao);

            $datainfo = 'Inicio Pedido: ' .  $this->dt_inicio_operacao . PHP_EOL . 
                        'Resposta ao pedido : ' . $this->dt_resposta_operacao . PHP_EOL . 
                        'Tempos Operação : ' . $differencegba ;
 
            \Log::channel($this->logchannel)->info($datainfo);
           
        } catch(Exception $e){
            \Log::channel($this->logchannel)->error($e->getMessage());
        }
    }


}