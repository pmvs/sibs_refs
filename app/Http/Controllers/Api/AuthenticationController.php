<?php

namespace App\Http\Controllers\Api;

use GuzzleHttp\Client;
use App\Models\User;
use App\Models\OAuth2JwtGenerator;
use Auth;
use Log;
use Response;
use View;
use DateTimeImmutable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\BancoPortugal\BancoPortugalService;

class AuthenticationController extends Controller
{

    private $logChannel = 'proxylookup';

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth:api', ['except' => ['login','certificado']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }





    /**
    * Handle an incoming authentication request.
    */
    public function store()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            // successfull authentication
            $user = User::find(Auth::user()->id);

            $user_token['token'] = $user->createToken('appToken')->accessToken;

            return response()->json([
                'success' => true,
                'token' => $user_token,
                'user' => $user,
            ], 200);
        } else {
            // failure to authenticate
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate.',
            ], 401);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        if (Auth::user()) {
            $request->user()->token()->revoke();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        }
    }

   
    /**
     * Operation getHealth
     *
     * /health - GET.
     *
     *
     * @return Http response
     */
    public function getHealth()
    {
        try{
            \Log::channel($this->logChannel)->info( '---getHealth---' );
             return response('Health '.strtoupper(config('app.sigla_psp')).' API', 200);

            // $contents='Healthy API';
            // $statusCode = 200;
            // $response = Response::make($contents, $statusCode);
            // $response->header('Content-Type', 'text/plain');
            // return $response;

           // return response()->json(['Healthy'], 200);
        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error( $e->getMessage() );
            return response('', 400);
            //return response('Health API - Error', 500);
            //return response()->json([], 400);
            // $contents='Internal server error';
            // $statusCode = 500;
            // $response = Response::make($contents, $statusCode);
            // $response->header('Content-Type', 'text/plain');
            // return $response;
        }
    }

    // public function readX509(Request $request)
    // {

    //     $data = file_get_contents('C:\inetpub\wwwroot\plcp\teste.pfx');
    //     $certPassword = '123456789';
    //     openssl_pkcs12_read($data, $certs, $certPassword);
    //     //var_dump($certs);

    //     // $data = openssl_x509_parse( file_get_contents('C:\inetpub\wwwroot\plcp\teste.pfx') );

    //     // $validFrom = date('Y-m-d H:i:s', $data['validFrom_time_t']);
    //     // $validTo = date('Y-m-d H:i:s', $data['validTo_time_t']);

    //     // echo $validFrom . "\n";
    //     // echo $validTo . "\n";

    //     $certinfo = openssl_x509_parse( openssl_x509_read($certs['cert']) ) ;

    //     $valid_from = date(DATE_RFC2822,$certinfo['validFrom_time_t']);
    //     $valid_to = date(DATE_RFC2822,$certinfo['validTo_time_t']);
    //     // echo "Valid From: ".$valid_from."<br>";
    //     // echo "Valid To:".$valid_to."<br>";

    //     $isValid = true;
    //     if( $certinfo['validFrom_time_t'] > time() || $certinfo['validTo_time_t'] < time() )
    //         $isValid = false;

    //     return response()->json([
    //         'success' => true,
    //         'isValid' => ( $isValid ? 'SIM' : 'NAO'),
    //         'validFrom' =>  $valid_from,
    //         'validTo' =>  $valid_to,
    //         // 'cert' =>  $certs['cert'],
    //         // 'pkey' =>  $certs['pkey'],
    //         'data1' => $certinfo,
    //         'message' =>  $certs,
           
    //         // 'validFrom' =>  $validFrom,
    //         // 'validTo' =>  $validTo,

    //     ], 200);

    // }

    public function createToken(Request $request)
    {

        try{

            \Log::channel($this->logChannel)->info('POST::createToken');
            \Log::channel($this->logChannel)->info( print_r( $request->all(), true) );

            //obtem certificado como array
            $certificate = $this->getCertificate();
            $sigla = config('app.sigla_psp');
            $issuer = 'https://bpnetsvc-'.$sigla.'-cert.bportugal.pt/';
            $audience = config('app.oauth2bpuriaudience');
          
            if ( config('app.env') == 'prod') {
                $issuer = 'https://bpnetsvc-'.$sigla.'.bportugal.pt/';
            }
          

            $token = ( new OAuth2JwtGenerator( $this->logChannel ) )->generateJwtToken( $audience, $issuer, $certificate );

            if ( $token == '' ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível gerar um token',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'access-token' => $token,
            ], 200);

        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível gerar um token',
            ], 500);
        }
    
    }


    // public function getAuthorizationFromADFS(Request $request )
    // {
    //     \Log::channel($this->logChannel)->info('GET::getAuthorizationFromADFS');
    //     \Log::channel($this->logChannel)->info( print_r( $request->all(), true) );

    //     //get jwt token from BancoPortugalService
    //     $bpService = new BancoPortugalService( $this->logChannel );

    //     $content = $bpService->getAuthorizationFromADFS();

    //     header("Content-Type: text/plain");
        
    //     return view('resposta', compact('content'));

    // }

    // public function callBackAuth(Request $request)
    // {

    //     \Log::channel($this->logChannel)->info('GET::callBackAuth');
    //     \Log::channel($this->logChannel)->info( print_r( $request->all(), true) );

    // }

    public function generateJwtToken(Request $request)
    {

        \Log::channel($this->logChannel)->info('POST::generateJwtToken');
        \Log::channel($this->logChannel)->info( print_r( $request->all(), true) );

        try{

            //obtem certificado como array
            $certificate = $this->getCertificate();
            $sigla = config('app.sigla_psp');
            $issuer = config('app.oauth2_client_id');;
            $audience = config('app.oauth2bpuriaudience');
            $resource = 'https://wwwcert.bportugal.net/apigw/conp/';

            if ( config('app.env') == 'prod') {
                $resource = 'https://www.bportugal.net/apigw/conp/';
            }
          

            //generate jwt token 
            $token = ( new OAuth2JwtGenerator( $this->logChannel ) )->generateJwtToken( $audience, $issuer, $certificate );

            \Log::channel($this->logChannel)->info('JWT Token : ' . $token);

            if ( $token == '' ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível gerar um token',
                ], 500);
            }

            return $token;

            return response()->json([
                'success' => true,
                'client_assertion' => $token,
            ], 200);

            // //get token from ADFS
            // $requestContent = [
            //         'resource' => $resource,
            //         'client_id' => $issuer,
            //         'grant_type' => 'client_credentials',
            //         'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            //         'client_assertion' => $token
            //     ];

            // \Log::channel($this->logChannel)->info('Request Content : ' . print_r($requestContent, true));

            // $audience = 'http://localhost:8000/api/v1/testpost';

            // \Log::channel($this->logChannel)->info('Audience: ' . $audience);
            
            // // $result =  $this->makeCurlCall( $audience,  $requestContent );

            // // \Log::channel($this->logChannel)->info('CURL result: ' . print_r( $result, true));

            // //If you would like to send data using the application/x-www-form-urlencoded content
            // $response = Http::asForm()
            //     ->accept('application/json')
            //     ->post( $audience, $requestContent);
            
            // // Determine if the status code is >= 200 and < 300...
            // $response->successful();
            
            // // Determine if the status code is >= 400...
            // $response->failed();
            
            // // Determine if the response has a 400 level status code...
            // $response->clientError();
            
            // // Determine if the response has a 500 level status code...
            // $response->serverError();
            
            // Immediately execute the given callback if there was a client or server error...
            //$response->onError(callable $callback);

            // $response->body() : string;
            // $response->json() : array;
            // $response->status() : int;
            // $response->ok() : bool;
            // $response->successful() : bool;
            // $response->serverError() : bool;
            // $response->clientError() : bool;
            // $response->header($header) : string;
            // $response->headers() : array;

            // $response = Http::asForm()
            //     ->withToken()
            //     ->accept('application/json')
            //     ->post( $audience, $requestContent);
            // $client = new Client();
            // $promise = $client->postAsync($url, [
            //     'json' => [
            //             'company_name' => 'update Name'
            //     ],
            // ])->then(
            //     function (ResponseInterface $res){
            //         $response = json_decode($res->getBody()->getContents());
            
            //         return $response;
            //     },
            //     function (RequestException $e) {
            //         $response = [];
            //         $response->data = $e->getMessage();
            
            //         return $response;
            //     }
            // );
            // $response = $promise->wait();
            // echo json_encode($response);

            return response()->json([
                'success' => true,
                'response_successful' => $response->successful(),
                'response_failed' => $response->failed(),
                'response_clientError' => $response->clientError(),
                'response_serverError' => $response->serverError(),
                'response_status' => $response->status(),
                'response_ok' => $response->ok(),
                'response_body' => $response->body(),
                'response_json' => print_r($response->json(), true),
                'response_headers' => print_r($response->headers(), true),
            ], 200);

        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível enviar o pedido',
            ], 500);
        }
    
    }

    public function getTokenFromADFS(Request $request)
    {

        \Log::channel($this->logChannel)->info('AuthenticationController::getTokenFromADFS');
   
        try{

            // if(Session::has('token_bearer')){
            //     \Log::channel($this->logChannel)->info('AuthenticationController : Bearer Token already exists');
            //     return response()->json([ 'access_token' => 'Já existe' ] , 200);
            // }

            //get jwt token from BancoPortugalService
            $bpService = new BancoPortugalService( $this->logChannel,false );

            \Log::channel($this->logChannel)->info('AuthenticationController : get jwtToken from BancoPortugalService');
            $jwtToken = $bpService->getJwtToken();
            if( $jwtToken  == '' ){
                \Log::channel($this->logChannel)->info('AuthenticationController : jwtToken not set');
                return '';
            }

            //get token from ADFS 
            \Log::channel($this->logChannel)->info('AuthenticationController : get bearer token from BdP ADFS');
            //resource
            if ( config('app.env') == 'prod') {
                $resource = config('enums.apibp.resources_plcp.COPB');
            }else {
                $resource = config('enums.apibp_dev.resources_plcp.COPB');
            }
          
            \Log::channel($this->logChannel)->info('Resource: ' .  $resource);
            //token 
            $tokenBearer = $bpService->getBearerTokenFromADFS( $resource, $jwtToken );  
            \Log::channel($this->logChannel)->info('Bearer Token: ' . print_r($tokenBearer, true));

            $content = $tokenBearer;

           // session(['token_bearer' => $tokenBearer]);

            return view('resposta', compact('content'));

        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível enviar o pedido',
            ], 500);
        }
    
    }

    public function createSecret(Request $request)
    {

        try{

            $secret = bin2hex(random_bytes(32));

            return response()->json([
                'secret' => $secret,
            ], 200);

        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível gerar um secret',
            ], 500);
        }
    
    }

    public function testPost(Request $request)
    {

        try{

            \Log::channel($this->logChannel)->info('POST::testPost');
            \Log::channel($this->logChannel)->info( print_r( $request->all(), true) );

            return response()->json([
                'success' => true,
            ], 200);

        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível testar o endpoint',
            ], 500);
        }
    
    }

    public function invocaHealthBDP(Request $request, $type )
    {
        \Log::channel($this->logChannel)->info('invocaHealthBDP: ' . $type);
        try{

            $audience = '';

            if ( config('app.env') == 'prod') {

                switch( $type ){
                    case config('enums.apibp.api_plcp.COPB'):
                        $audience = config('enums.apibp.endpoints_plcp.COP_HEALTH');
                        break;
                    case config('enums.apibp.api_plcp.PROXYLOOKUP_GESTAO'):
                        $audience = config('enums.apibp.endpoints_plcp.PL_GESTAO_HEALTH');
                        break;
                    case config('enums.apibp.api_plcp.PROXYLOOKUP_CONSULTAS'):
                        $audience = config('enums.apibp.endpoints_plcp.PL_CONSULTA_HEALTH');
                        break;
                    default: 
                        $audience = 'sem endpoint definido';
                }

            }else {
                
                switch( $type ){
                    case config('enums.apibp_dev.api_plcp.COPB'):
                        $audience = config('enums.apibp_dev.endpoints_plcp.COP_HEALTH');
                        break;
                    case config('enums.apibp_dev.api_plcp.PROXYLOOKUP_GESTAO'):
                        $audience = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_HEALTH');
                        break;
                    case config('enums.apibp_dev.api_plcp.PROXYLOOKUP_CONSULTAS'):
                        $audience = config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_HEALTH');
                        break;
                    default: 
                        $audience = 'sem endpoint definido';
                }
            }
          

            \Log::channel($this->logChannel)->info('Audience: ' . $audience);

            $subscriptionKey = 'cqi040';
            $method = 'GET';
            $payload = [];
            $headers = [];
            $headers = ['accept: text/plain', 'subscription-key:' . $subscriptionKey];
            $headers = ['accept: text/plain'];
            $requestContent = [];

            $result =  $this->makeCurlCall2( $audience,  $requestContent, $headers, $method , $payload );
            
            $content = $result['content'];

            return view('resposta', compact('content'));


        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error($e->getMessage());
        }

    }

    public function readPublicKeyfromADFS(Request $request)
    {
        $this->logChannel = 'bancoportugal';
        \Log::channel($this->logChannel)->info('readPublicKeyfromADFS');
        try{

            $content = ( new BancoPortugalService() )->readPublicKeyfromADFS() ;

            header('Content-type: text/plain');

            return view('resposta', compact('content'));


        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error($e->getMessage());
        }
    }

    private function makeCurlCall2( $url , $curl_data, $headers, $method , $payload)
    {
        \Log::channel($this->logChannel)->info('----makeCurlCall----');
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
                \Log::channel($this->logChannel)->info('vou fazer set do body ' . $payload);
                //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '' . $payload . '');
            }

            //curl_setopt_array($ch, $options);

            //exceute curl
            // echo 'Operation EXECUTING CURL...'. PHP_EOL;
            \Log::channel($this->logChannel)->info('----curl_exec----');
            $start = microtime(true);
            $output = curl_exec($ch);
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logChannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);

            //get curl errors
            $err = curl_error($ch);
            \Log::channel($this->logChannel)->info('ERROS----' . $err );
        
            $errno = curl_errno($ch);
            \Log::channel($this->logChannel)->info('ERRO NUMBER----'. $errno);
          
            //get HTTP code 
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            \Log::channel($this->logChannel)->info('HTTP CODE----'. $httpcode);

            //get HTTP HEADER 
            $httpinfo_header = curl_getinfo($ch);
            \Log::channel($this->logChannel)->info('HTTP HEADER----');
            \Log::channel($this->logChannel)->info($httpinfo_header);

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

            \Log::channel($this->logChannel)->info(print_r( $result, true));
 
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

    /**
    * Mostra o certificado
    */
    public function certificado(Request $request)
    {
        try{
            return response()->json([
                'success' => true,
                'message' => $this->getCertificate(),
            ], 200);
        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error( $e->getMessage() );
            return response()->json([
                'success' => false,
                'message' =>  'Não foi possível obter o certificado',
            ], 500);
        }
    }

    /**
    * Mostra a informacao do certificado
    */
    public function certificadoInfo(Request $request)
    {
        try{


           // \Log::stack(['hbp', 'proxylookup'])->info('registration successful!');

            //obtem certificado
            $certs = $this->getCertificate();

            \Log::channel($this->logChannel)->info( print_r( $certs, true) );

            //le informacoes do certificado
            //$certinfo = openssl_x509_parse( $certs );
           $certinfo = openssl_x509_parse( openssl_x509_read( $certs['cert']) ) ;

           \Log::channel($this->logChannel)->info( print_r(  $certinfo, true) );

           $certinfo =  mb_convert_encoding( $certinfo, 'UTF-8', 'UTF-8');

          // \Log::channel($this->logChannel)->info( print_r(  $certinfo, true) );

           return response()->json([
            'success' => true,
            'message' => print_r(  $certinfo, true)
        ], 200);
            // return response()->json([
            //     'success' => true,
            //     'message' => print_r($certinfo,true),
            // ], 200);

        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error( $e->getMessage() );
            return response()->json([
                'success' => false,
                'message' =>  'Não foi possível obter informação do certificado',
            ], 500);
        }
    }

    /**
    * Verifica validade do certificado
    */
    public function certificadoValido(Request $request)
    {
        try{

            //obtem certificado
            $certs = $this->getCertificate();


           


            //le informacoes do certificado
            $certinfo = openssl_x509_parse( openssl_x509_read( $certs['cert'] ) ) ;

            \Log::channel($this->logChannel)->info( print_r($certinfo, true));
            \Log::channel($this->logChannel)->info( openssl_get_cert_locations());
    
            // this is the CER FILE
            file_put_contents( 'C:\certs\CERT.cer', $certs['pkey'].$certs['cert'].implode('', $certs['extracerts']));

            // this is the PEM FILE
            $cert = $certs['cert'].implode('', $certs['extracerts']);
            file_put_contents('C:\certs\KEY.pem', $cert);

            // this is the PEM FILE
            $cert = $certs['cert'];
            file_put_contents('C:\certs\my-cert.pem', $cert);


            openssl_x509_export( openssl_x509_read( $certs['cert'] ) , $out);
            \Log::channel($this->logChannel)->info( $out );

            $publicKey = openssl_pkey_get_public(  openssl_x509_read( $certs['cert'] )   );
            $keyData = openssl_pkey_get_details($publicKey);
            \Log::channel($this->logChannel)->info( print_r($keyData,true) );

            $privateKey = $certs['pkey'];
            \Log::channel($this->logChannel)->info('private key: ' . $privateKey);
            // $privateKey = openssl_pkey_get_private(  openssl_x509_read( $certs['cert'] )   );
            // $keyData2 = openssl_pkey_get_details($privateKey);
            // \Log::channel($this->logChannel)->info( print_r($keyData2,true) );

            // $digests = openssl_get_md_methods();
            // \Log::channel($this->logChannel)->info('methods : ' . print_r($digests,true));

            $valid_from = date(DATE_RFC2822,$certinfo['validFrom_time_t']);
            $valid_to = date(DATE_RFC2822,$certinfo['validTo_time_t']);
       
            $isValid = true;
            if( $certinfo['validFrom_time_t'] > time() || $certinfo['validTo_time_t'] < time() )
                $isValid = false;

            return response()->json([
                'success' => true,
                'message' => [
                    'public_key' => $keyData['key'], 
                    'private_key' => $privateKey, 
                    'valido' => ( $isValid ? 'Sim':'Nao' ),
                    'valido_desde' => $valid_from,
                    'valido_ate' => $valid_to,
                ],
            ], 200);

        }catch(\Exception $e){
            \Log::channel($this->logChannel)->error( $e->getMessage() );
            return response()->json([
                'success' => false,
                'message' =>  'Não foi possível obter informação da validade do certificado',
            ], 500);
        }
    }

    /**
    * Create a .jwt signed token.
    */
    private function generateToken( $audience, $issuer, $certificate )
    {
        try{

            \Log::channel($this->logChannel)->info('------generateToken--------');
            \Log::channel($this->logChannel)->info('audience: ' . $audience);
            \Log::channel($this->logChannel)->info('issuer: ' . $issuer);
            

            
            // // Create token header as a JSON string
            // $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

            // // Create token payload as a JSON string
            // $payload = json_encode(['user_id' => 123]);

            // // Encode Header to Base64Url String
            // $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

            // // Encode Payload to Base64Url String
            // $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

            // // Create Signature Hash
            // $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);

            // // Encode Signature to Base64Url String
            // $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

            // // Create JWT
            // $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            // echo $jwt;

            // //le informacoes do certificado
            // $certinfo = openssl_x509_parse( openssl_x509_read( $certs['cert'] ) ) ;

            // \Log::channel($this->logChannel)->info( print_r($certinfo, true));
            // \Log::channel($this->logChannel)->info( openssl_get_cert_locations());
            
            // openssl_x509_export( openssl_x509_read( $certs['cert'] ) , $out);
            // \Log::channel($this->logChannel)->info( $out );

            // $publicKey = openssl_pkey_get_public(  openssl_x509_read( $certs['cert'] )   );
            // $keyData = openssl_pkey_get_details($publicKey);
            // \Log::channel($this->logChannel)->info( print_r($keyData,true) );

            // $privateKey = $certs['pkey'];
            // \Log::channel($this->logChannel)->info('private key: ' . $privateKey);


            // //signingCredentials using X509 certificate
            // $digest = openssl_x509_fingerprint(openssl_x509_read( $certificate['cert'] ) , 'RSA-SHA256' );
            // \Log::channel($this->logChannel)->info('digest: ' . $digest);

            $privateKey = $certificate['pkey'];
            \Log::channel($this->logChannel)->info('private key: ' . $privateKey);
            \Log::channel($this->logChannel)->info('------assina com a pkey os dados do certificado --------');
            openssl_sign($certificate['cert'], $signature, $privateKey, OPENSSL_ALGO_SHA256);
            //\Log::channel($this->logChannel)->info('signature: ' . $signature);
           //openssl_free_key( $privateKey );
            $signingCredentials = $signature;
         
            $base64UrlSignatureCredentials = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signingCredentials));
            \Log::channel($this->logChannel)->info('base64UrlSignatureCredentials: ' . $base64UrlSignatureCredentials);
            \Log::channel($this->logChannel)->info($base64UrlSignatureCredentials);

            //create jwt token
            //create jwt token header
            \Log::channel($this->logChannel)->info('------header--------');
            $header = json_encode([
                'typ' => 'JWT',
                'alg' => 'RS256',
            ]);
            \Log::channel($this->logChannel)->info(print_r($header, true));

            //create jwt token claims
            \Log::channel($this->logChannel)->info('------claims--------');
            $date = new DateTimeImmutable();
            $claims = [
                'iss'   => $issuer,
                'sub'   => $issuer,
                'iat'   => $date->getTimestamp(),
                'jti'   => uuid_create(),
                'nbf'   => $date->getTimestamp(),
            ];
            \Log::channel($this->logChannel)->info(print_r($claims, true));

            //create jwt token payload
            \Log::channel($this->logChannel)->info('------payload--------');
            $payload = json_encode([
               'issuer'   => $issuer,
               'audience'   => $audience,
               'claims'   => $claims,
               'expires'   => $date->modify('+15 minutes')->getTimestamp(),
               'signingCredentials'   => $base64UrlSignatureCredentials,
            ]);
            \Log::channel($this->logChannel)->info($payload);
            // $payload = [
            //     'issuer'   => $issuer,
            //     'audience'   => $audience,
            //     'claims'   => $claims,
            //     'expires'   => $date->modify('+15 minutes')->getTimestamp(),
            //     'signingCredentials'   => $signingCredentials,
            //  ];
            //  \Log::channel($this->logChannel)->info(print_r($payload, true));

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            \Log::channel($this->logChannel)->info('------base64UrlHeader--------');
            \Log::channel($this->logChannel)->info( $base64UrlHeader );

            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
            \Log::channel($this->logChannel)->info('------base64UrlPayload--------');
            \Log::channel($this->logChannel)->info( $base64UrlPayload);

            \Log::channel($this->logChannel)->info('------dados a serem assinados --------');
            $data = $base64UrlHeader . "." . $base64UrlPayload;
            \Log::channel($this->logChannel)->info( $data );

            // $privateKey = $certificate['pkey'];
            // \Log::channel($this->logChannel)->info('private key: ' . $privateKey);

            \Log::channel($this->logChannel)->info('------assina com a private key os dados --------');
            openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            \Log::channel($this->logChannel)->info('signature: ' . $signature);
            //openssl_free_key( $privateKey );

            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            \Log::channel($this->logChannel)->info('Encoded signature: ' . $base64UrlSignature);

            // Your JWT signed with the supplied private key
            $jwt = $data . "." . $base64UrlSignature;

            return $jwt;
    
        }catch(\Exception $e){
            \Log::error($e->getMessage());
        }
    }

    /**
    * Read a .pfx file.
    */
    private function getCertificate()
    {
        try{

            $pathToCertificate = config('app.path_to_certificate_pfx');

            $data = file_get_contents( $pathToCertificate );

            $certPassword = config('app.psw_certificate_pfx');

            //certificate stored into an array
            openssl_pkcs12_read( $data, $certs, $certPassword );

            return $certs;

        }catch(\Exception $e){
            \Log::error($e->getMessage());
        }
      
    }


    public function calculaSHA1()
    {
        try {

            $certificate = $this->getCertificate();

            $sha1_hash = openssl_x509_fingerprint($certificate['cert'], 'sha1'); // sha1 hash (x5t parameter)
            \Log::channel($this->logchannel)->info('sha1_hash: ' . $sha1_hash);
        
            $arr2 = str_split($sha1_hash, 2);
            \Log::channel($this->logchannel)->info('sha1_hash: ' . print_r($arr2, true));
            $straux = '';
            foreach( $arr2  as $arr ) {
                $straux .= strtoupper($arr) . ':';
            }
            \Log::channel($this->logchannel)->info('sha1_hash: ' . $straux);

            $aux = '5D:D0:7C:9A:C0:B4:BD:0F:86:4B:9B:17:48:B0:35:10:09:4C:84:DD';
            $encoded_fingerprint4 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($aux));
           //openssl x509 -noout -in bpnetsvc-ccmaf-cert.bportugal.pt.pfx -fingerprint
            \Log::channel($this->logchannel)->info('SHA1 Fingerprint: ' . $aux);
            // \Log::channel($this->logchannel)->info('encoded_fingerprint : ' . $encoded_fingerprint4);
            return response()->json([
                'success' => true,
                'sha1' => $sha1_hash,
                'Fingerprint' => $aux,
                'arr2' => $arr2,
                'straux' => rtrim($straux,':'),
            ], 200);

        }catch(\Exception $e){
            \Log::channel()->error(__FILE__. ' ' . __LINE__ . ' ' . $e->getMessage());
        }
      
    }
   

}
