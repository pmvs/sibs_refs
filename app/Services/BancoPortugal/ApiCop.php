<?php 

namespace App\Services\BancoPortugal;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

use Storage;
use DateTime;

use App\Models\Gba\IBAN;

use App\Models\ADFSToken;
use App\Models\BPPLRequest;
use App\Models\CopPayload;
use App\Models\BancoPortugal\ProxyLookup\Cop;
use App\Repositories\Api\ApiRepository;

class ApiCop
{
    private $logchannel = 'bancoportugal';
    private $isConnProduction = false;
    private $mensagemErro = '';
    private $dt_inicio_gba = '';
    private $dt_resposta_gba = '';
    private $dt_inicio_operacao = '';
    private $dt_resposta_operacao = '';
    private $sigla = '';
    //campos para insert
    private $psp_code = '';
    private $psp_code_destination = '';
    private $customer_identifier = '';
    private $customer_identifier_type = '';
    private $fiscal_number = '';
    private $customer_type = '';
    private $iban = '';
    private $correlation_id_origin = '';
    private $timestamp = '';
    //campos para resposta do insert
    private $success = false;
    private $correlation_id = '';
    private $message = '';
    private $errors= [];
    private $errorsCode = '';
    private $errorsValue = '';
    private $token_valido = false;
    private $access_token = '';
    private $token_id = '';
    private $http_code = '';
    private $payloadJson = '';
    private $metateste = [];
    private $bpService = null;
    private $contrato = '';
    private $copB = [];

    public function __construct() 
    {
        $this->logchannel = 'bancoportugal';
        $this->isConnProduction = config('app.connection_prod');
        $this->utilizador = null;
        $this->mensagemErro = '';
        $this->sigla =  config('app.sigla');
        $this->bpService = null;
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        // \Log::channel($this->logchannel)->info('__construct ApiCop');
        // \Log::channel($this->logchannel)->info('Connection de Produção ? ' . ( $this->isConnProduction ? 'SIM' : 'NÃO' ) );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
       // \Log::channel($this->logchannel)->info('__construct_1 ApiCop');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
       // \Log::channel($this->logchannel)->info('__construct_2 ApiCop');
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
    public function getErrors()
    {
        return $this->errors ;
    }
    public function getSuccess()
    {
        return $this->success ;
    }
    public function getCorrelationId()
    {
        return $this->correlation_id ;
    }
    public function getMessage()
    {
        return $this->message ;
    }
    public function getErrorsCode()
    {
        return $this->errorsCode ;
    }
    public function getErrorsValue()
    {
        return $this->errorsValue ;
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

    //setters para proxylookup insert association
    public function setPSPCode( $psp_code )
    {
        $this->psp_code = $psp_code;
    }

    public function setPSPCodeDestination( $psp_code_destination )
    {
        $this->psp_code_destination = '' . $psp_code_destination;
    }

    public function setCustomerIdentifier( $customer_identifier )
    {
        $this->customer_identifier = $customer_identifier;
    }
    public function setCustomerIdentifierType( $customer_identifier_type )
    {
        $this->customer_identifier_type = $customer_identifier_type;
    }
    public function setFiscalNumber( $fiscal_number )
    {
        $this->fiscal_number = $fiscal_number;
    }
    public function setCustomerType( $customer_type )
    {
        $this->customer_type = $customer_type;
    }
    public function setIban( $iban )
    {
        $this->iban = $iban;
    }
    public function setContrato( $contrato )
    {
        $this->contrato = $contrato;
    }
    public function setCopB( $copb )
    {
        $this->copb = $copb;
    }
    public function setCorrelationIdOrigin( $correlation_id_origin )
    {
        $this->correlation_id_origin = $correlation_id_origin;
    }
    public function setTimestamp()
    {
        /*
        * The `DateTime` constructor doesn't create objects with fractional seconds.
        * However, the static method `DateTime::createFromFormat()` does include the
        * fractional seconds in the object.  Finally, since ISO 8601 specifies only
        * millisecond precision, remove the last three decimal places from the timestamp.
        */
        // DateTime object with microseconds
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', '')); 
        // Truncate to milliseconds
        $nowFormatted = substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'; 
        //set timestamp
        $this->timestamp = $nowFormatted; 

        //\Log::channel($this->logchannel)->info('Timestamp : ' .  $this->timestamp );

    }

    //campos para resposta do insert
    public function setSuccess($success)
    {
        $this->success = $success;  
    }
    public function setCorrelationId($correlation_id)
    {
        $this->correlation_id = $correlation_id;  
    }
    public function setMessage($message)
    {
        $this->message = $message;  
    }
    public function setErrors($errors)
    {
        $this->errors = $errors;  
    }
    public function setErrorsCode($errorsCode)
    {
        $this->errorsCode = $errorsCode;  
    }
    public function setErrorsValue($errorsValue)
    {
        $this->errorsValue = $errorsValue;  
    }


    public function setPayload($jsonPayload)
    {
        $this->payloadJson = $jsonPayload;
    }

    public function setMetaTeste($metateste)
    {
        $this->metateste = $metateste;
    }

    public function getAccessTokenUsed()
    {
        return $this->access_token;
    }
    public function getAccessTokenID()
    {
        return $this->token_id;
    }
    public function getHttpStatusCode()
    {
        return $this->http_code;
    }



    /*
    |--------------------------------------------------------------------------
    | METHODS PUBLIC - EXPOSED SERVICES
    |--------------------------------------------------------------------------
    */
    /*  
    |--------------------------------------------------------------------------
    | METHODS PUBLIC - EXPOSED TESTES
    |--------------------------------------------------------------------------
    */
    public function executaTeste()
    {
        try{

            if( count($this->metateste) == 0 ){
                \Log::channel($this->logchannel)->info('ApiCop : metaTeste not set');
                return '';
            }

            \Log::channel($this->logchannel)->info('ApiCop : executa teste ' . $this->metateste['nrteste'] . ' ' . $this->metateste['nmteste'] );

           // $this->token_valido = config('app.access_token_valido');
            $this->token_valido = false;

            $access_token = $this->getAccessToken();
            if (  $access_token == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter um token para autenticação');
                return ['Erros Internos : Não foi possivel obter um token para autenticação'];
            }
    
            \Log::channel($this->logchannel)->info('Vai efetuar chamada com o token obtido');
            //add auth headers 
            $headers = [];
            $headers[] = 'Content-Type:application/json';
            $headers[] = "Authorization: Bearer ".$access_token;

            //endpoint audience
            $audience = $this->metateste['endpointteste'];

            //make a culr call
            $start = microtime(true);
            $response = $this->callUsingCURL($this->metateste['endpointteste'], $this->payloadJson, $headers);
            $time_elapsed_secs = microtime(true) - $start;
           // $seconds = number_format($time_elapsed_secs  * 1000, 2);
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs . 's');
           // \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $seconds . 'ms');

            //verifica validade do JWT
            $data = json_decode($response, true);

            $correlation_id = '';
            $request_id = 0;
            $invalidJWT = false;

            //se não houve sucesso na chamada
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    //o token esta invalido
                    $invalidJWT = true;
                    $this->token_valido = false;
                    \Log::channel($this->logchannel)->info('O token encontra-se expirado pelo BP');
                    $this->inativaTokenDB();
                    if ( $invalidJWT ) {
                        \Log::channel($this->logchannel)->info('**********'); 
                        \Log::channel($this->logchannel)->info('*****JWT TIMEOUT EXECUTA NOVO PEDIDO*****');
                        \Log::channel($this->logchannel)->info('**********');  
                        $this->executaTeste();
                    }
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        //erro na chamada 
                        \Log::channel($this->logchannel)->info('Ocorreram erros na chamada ao BP : ' .  $data['message']);
                        return $response;
                    }

                }
            }else {
                $correlation_id = $data['correlation_id'];
            }

            $contrato = 0;

            //grava pedido na BD
            $request_id = $this->saveRequestToDB( $audience, $response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            //grava payload na BD
            $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

        
            return $response;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    public function copS()
    {
        try{
            \Log::channel($this->logchannel)->info('ApiCop: vai fazer COPS....');
            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.COPS');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.COPB');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.COPS');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.COPB');
            }

          
            $access_token = $this->getAccessToken();
            if (  $access_token == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter um token para autenticação');
                return ['Erros Internos : Não foi possivel obter um token para autenticação'];
            }

            //\Log::channel($this->logchannel)->info('Vai efetuar chamada com o token obtido');
            //add auth headers 
            $headers = [];
            $headers[] = 'Content-Type:application/json';
            $headers[] = "Authorization: Bearer ".$access_token;

            //endpoint audience
            //$audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.COPS');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.COPS');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }
            
            //payload 
            $this->payloadJson = '';
            //\Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(1);
            //\Log::channel($this->logchannel)->info($this->payloadJson);

            if ( ! $this->payloadJson ) {
                \Log::channel($this->logchannel)->warning('Payload NULL');
                return ['Erros Internos : Payload Nulo'];
            }

            //make a culr call
            $start = microtime(true);
            $response = $this->callUsingCURL($audience, $this->payloadJson, $headers);
            $time_elapsed_secs = microtime(true) - $start;
            // $seconds = number_format($time_elapsed_secs  * 1000, 2);
            //\Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs . 's');
           // \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $seconds . 'ms');

            //verifica validade do JWT
            $data = json_decode($response, true);

            $correlation_id = '';
            $request_id = 0;
            $invalidJWT = false;

            if ( ! $data) {
                //erro na chamada 
                \Log::channel($this->logchannel)->info('Ocorreram erros na chamada ao BP sem dados' );
                return false;
            }

            //se não houve sucesso na chamada
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    //o token esta invalido
                    $invalidJWT = true;
                    $this->token_valido = false;
                    \Log::channel($this->logchannel)->info('O token encontra-se expirado pelo BP');
                    $this->inativaTokenDB();
                    if ( $invalidJWT ) {
                        \Log::channel($this->logchannel)->info('**********'); 
                        \Log::channel($this->logchannel)->info('*****JWT TIMEOUT EXECUTA NOVO PEDIDO*****');
                        \Log::channel($this->logchannel)->info('**********');  
                        return $this->copS();
                    }
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        //erro na chamada 
                        \Log::channel($this->logchannel)->info('Ocorreram erros na chamada ao BP : ' .  $data['message']);
                        return false;
                    }

                }
            }else {
                $correlation_id = $data['correlation_id'];
            }

            $contrato = $this->contrato;

            //grava pedido na BD
            $request_id = $this->saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            // //grava payload na BD
            // $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            // //check what to do
            // \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            // \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 

            return ['error' => false, 'response' => $response, 'status_code' => $this->httpcode];

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }

    }

    public function copB()
    {
        try{
            \Log::channel($this->logchannel)->info('ApiCop: vai fazer COPB....');
            $this->token_valido = false;
            $this->metateste = [];
            // $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.COPB');
            // $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.COPB');
         
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.COPB');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.COPB');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.COPB');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.COPB');
            }


            $access_token = $this->getAccessToken();
            if (  $access_token == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter um token para autenticação');
                return ['Erros Internos : Não foi possivel obter um token para autenticação'];
            }

            //\Log::channel($this->logchannel)->info('Vai efetuar chamada com o token obtido');
            //add auth headers 
            $headers = [];
            $headers[] = 'Content-Type:application/json';
            $headers[] = "Authorization: Bearer ".$access_token;

            //endpoint audience
            // $audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.COPB');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.COPB');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }
            
            //payload 
            $this->payloadJson = '';
            //\Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(2);
            \Log::channel($this->logchannel)->info($this->payloadJson);

            if ( ! $this->payloadJson ) {
                \Log::channel($this->logchannel)->warning('Payload NULL');
                return ['Erros Internos : Payload Nulo'];
            }

            //make a culr call
            $start = microtime(true);
            $response = $this->callUsingCURL($audience, $this->payloadJson, $headers);
            $time_elapsed_secs = microtime(true) - $start;
           // $seconds = number_format($time_elapsed_secs  * 1000, 2);
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs . 's');
           // \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $seconds . 'ms');

            //verifica validade do JWT
            $data = json_decode($response, true);

            $correlation_id = '';
            $request_id = 0;
            $invalidJWT = false;

            //se não houve sucesso na chamada
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    //o token esta invalido
                    $invalidJWT = true;
                    $this->token_valido = false;
                    \Log::channel($this->logchannel)->info('O token encontra-se expirado pelo BP');
                    $this->inativaTokenDB();
                    if ( $invalidJWT ) {
                        \Log::channel($this->logchannel)->info('**********'); 
                        \Log::channel($this->logchannel)->info('*****JWT TIMEOUT EXECUTA NOVO PEDIDO*****');
                        \Log::channel($this->logchannel)->info('**********');  
                        return $this->copB();
                    }
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        //erro na chamada 
                        \Log::channel($this->logchannel)->info('Ocorreram erros na chamada ao BP : ' .  $data['message']);
                        return false;
                    }

                }
            }else {
                $correlation_id = $data['correlation_id'];
            }

            $contrato = $this->contrato;

            //grava pedido na BD
            $request_id = $this->saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            // //grava payload na BD
            // $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            // //check what to do
            // \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            // \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 

            return ['error' => false, 'response' => $response, 'status_code' => $this->httpcode];

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }

    }

    public function copBTeste($nrteste)
    {
        try{

            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.COPB');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.COPB');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.COPB');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.COPB');
            }


            $access_token = $this->getAccessToken();
            if (  $access_token == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter um token para autenticação');
                return ['Erros Internos : Não foi possivel obter um token para autenticação'];
            }

            //\Log::channel($this->logchannel)->info('Vai efetuar chamada com o token obtido');
            //add auth headers 
            $headers = [];
            $headers[] = 'Content-Type:application/json';
            $headers[] = "Authorization: Bearer ".$access_token;

            //endpoint audience
            //$audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.COPB');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.COPB');
            }
            //\Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }
           
            //payload 
            $this->payloadJson = '';
            //\Log::channel($this->logchannel)->info(  'Cria payload teste...' . $nrteste );
            $this->payloadJson  = $this->criaPayloadTeste($nrteste);
            // \Log::channel($this->logchannel)->info($this->payloadJson);

            if ( ! $this->payloadJson ) {
                \Log::channel($this->logchannel)->warning('Payload NULL');
                return ['Erros Internos : Payload Nulo'];
            }

            //make a culr call
            $start = microtime(true);
            $response = $this->callUsingCURL($this->metateste['endpointteste'], $this->payloadJson, $headers);
            $time_elapsed_secs = microtime(true) - $start;
           // $seconds = number_format($time_elapsed_secs  * 1000, 2);
            //\Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs . 's');
           // \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $seconds . 'ms');

            //verifica validade do JWT
            $data = json_decode($response, true);

            $correlation_id = '';
            $request_id = 0;
            $invalidJWT = false;

            //se não houve sucesso na chamada
            if ( ! array_key_exists('success', $data) ) {
                if ( $data['statusCode'] == 401 && $data['message'] == 'Invalid JWT.' ) {
                    //o token esta invalido
                    $invalidJWT = true;
                    $this->token_valido = false;
                    \Log::channel($this->logchannel)->info('O token encontra-se expirado pelo BP');
                    $this->inativaTokenDB();
                    if ( $invalidJWT ) {
                        \Log::channel($this->logchannel)->info('**********'); 
                        \Log::channel($this->logchannel)->info('*****JWT TIMEOUT EXECUTA NOVO PEDIDO*****');
                        \Log::channel($this->logchannel)->info('**********');  
                        return $this->copBTeste($nrteste);
                    }
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        //erro na chamada 
                        \Log::channel($this->logchannel)->info('Ocorreram erros na chamada ao BP : ' .  $data['message']);
                        return false;
                    }

                }
            }else {
                $correlation_id = $data['correlation_id'];
            }

            $contrato = $this->contrato;

            //grava pedido na BD
            $request_id = $this->saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            // //grava payload na BD
            // $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            // //check what to do
            // \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            // \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 

            return ['error' => false, 'response' => $response, 'status_code' => $this->httpcode];

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }

    }

    private function criaPayload( $nrteste )
    {
        try{

            $myObj  = new \stdClass();

            switch( $nrteste ){
                case 1: //COPS
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    //customer identifier
                    $myObj->{'psp_code_destination'} = $this->psp_code_destination ;
                    //iban
                    $myObj->{'iban'} = $this->iban;
                    break;
                    
                case 2: //COPB

                    $myObj->{'psp_code'} = $this->psp_code;
                    $this->bancoDestino = $this->psp_code_destination;
                    $myObj->{'psp_code_destination'} = $this->bancoDestino ;

                    \Log::channel($this->logchannel)->info( ' COPB payload ' . print_r( $this->copb, true) );

                    if ( count($this->copb) > 0 ) {
                       
                        $myObj->{'items'} = (object) [];

                        $list = [];

                        foreach( $this->copb as $copb ){
                         
                            $copb = explode(';', $copb );
                           
                            \Log::channel($this->logchannel)->info( print_r( $copb, true) );

                            if ( count( $copb ) == 3) {
                              
                                $iban = $copb[0];
                                $nif = $copb[1];
                                $tipo = $copb[2];

                                $iban2 = new Iban($iban,$this->logchannel);
                                \Log::channel($this->logchannel)->info( $iban2->getInfoIBAN());

                                $bancoDestino = $iban2->getBancoIban();

                                $item = [
                                    'fiscal_number' => $nif,
                                    'type' => (int) $tipo,
                                    'iban' => $iban
                                ];

                                array_push($list, $item);

                            }else {
                                \Log::channel($this->logchannel)->info( 'Items count incorrect' );
                            }
                        }

                        $myObj->{'items'} = $list;
                      
                    }


                    break;
    
                default:
                    return null;
            }

             //timestamp
             $myObj->{'timestamp'} = $this->timestamp;

            //encode json object
            $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            //\Log::channel($this->logchannel)->info($myJSON);

            $myJSON = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $myJSON);
            //\Log::channel($this->logchannel)->info($myJSON);
            $myJSON = str_replace(" ", '', $myJSON);
            
            return $myJSON;
   

          }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function criaPayloadTeste( $nrteste )
    {
        try{

            $myObj  = new \stdClass();

            switch( $nrteste ){
                
                case 2000:

                   // \Log::channel($this->logchannel)->info(print_r($this->copb, true));
                    $myObj->{'psp_code'} = $this->psp_code;
                    $this->bancoDestino = $this->psp_code_destination;
                    $myObj->{'psp_code_destination'} = $this->bancoDestino ;
                    $myObj->{'items'} = (object) [];
                    $myObj->{'items'} = $this->copb;

                        // $list = [];

                        // for ( $i = 1; $i <= 1000; $i++ ) {

                        //     //\Log::channel($this->logchannel)->info('entrei aqui2');
                        //     $copb = explode(';', $this->copb );
                        //     //\Log::channel($this->logchannel)->info(print_r( $copb, true));
                        //     if ( count( $copb ) == 3) {
                                
                        //         //\Log::channel($this->logchannel)->info('entrei aqui5');

                        //         $iban = $copb[0];
                        //         $nif = $copb[1];
                        //         $tipo = $copb[2];

                        //         // $iban2 = new Iban($iban,$this->logchannel);
                        //         // \Log::channel($this->logchannel)->info( $iban2->getInfoIBAN());

                        //         // $bancoDestino = $iban2->getBancoIban();

                        //         $item = [
                        //             'fiscal_number' => $nif,
                        //             'type' => (int) $tipo,
                        //             'iban' => $iban
                        //         ];

                        //         array_push($list, $item);

                        //     }
                           
                        // }
                        

                        //$myObj->{'items'} = $this->list;
                       // \Log::channel($this->logchannel)->info('lista : ' . count($list));
                      
                    
                break;

                case 1:
                    $myObj->{'psp_code'} = $this->psp_code;
                    $this->bancoDestino = $this->psp_code_destination;
                    $myObj->{'psp_code_destination'} = $this->bancoDestino ;
                    if ( count($this->copb) > 0 ) {
                       
                        $myObj->{'items'} = (object) [];

                        $list = [];

                        foreach( $this->copb as $copb ){
                         
                            $copb = explode(';', $copb );
                           
                            if ( count( $copb ) == 3) {
                              
                                $iban = $copb[0];
                                $nif = $copb[1];
                                $tipo = $copb[2];

                                $iban2 = new Iban($iban,$this->logchannel);
                                \Log::channel($this->logchannel)->info( $iban2->getInfoIBAN());

                                $bancoDestino = $iban2->getBancoIban();

                                $item = [
                                    'fiscal_number' => $nif,
                                    'type' => (int) $tipo,
                                    'iban' => $iban
                                ];

                                array_push($list, $item);

                            }
                        }

                        $myObj->{'items'} = $list;
                      
                    }
                break;

                case 8:

                    $myObj->{'psp_code'} = '';
                    $this->bancoDestino = $this->psp_code_destination;
                    $myObj->{'psp_code_destination'} = $this->bancoDestino ;
                    if ( count($this->copb) > 0 ) {
                       
                        $myObj->{'items'} = (object) [];

                        $list = [];

                        foreach( $this->copb as $copb ){
                         
                            $copb = explode(';', $copb );
                           
                            if ( count( $copb ) == 3) {
                              
                                $iban = $copb[0];
                                $nif = $copb[1];
                                $tipo = $copb[2];

                                $iban2 = new Iban($iban,$this->logchannel);
                                \Log::channel($this->logchannel)->info( $iban2->getInfoIBAN());

                                $bancoDestino = $iban2->getBancoIban();

                                $item = [
                                    'fiscal_number' => $nif,
                                    'type' => (int) $tipo,
                                    'iban' => $iban
                                ];

                                array_push($list, $item);

                            }
                        }

                        $myObj->{'items'} = $list;
                      
                    }
                break;

                     
                case 9:
                    $myObj->{'psp_code'} = $this->psp_code;
                    $this->bancoDestino = $this->psp_code_destination;
                    $myObj->{'psp_code_destination'} = $this->bancoDestino ;
                    if ( count($this->copb) > 0 ) {
                       
                        $myObj->{'items'} = (object) [];

                        $list = [];

                        foreach( $this->copb as $copb ){
                         
                            $copb = explode(';', $copb );
                           
                            if ( count( $copb ) == 3) {
                              
                                $iban = $copb[0];
                                $nif = $copb[1];
                                $tipo = $copb[2];

                                $iban2 = new Iban($iban,$this->logchannel);
                                \Log::channel($this->logchannel)->info( $iban2->getInfoIBAN());

                                $bancoDestino = $iban2->getBancoIban();

                                $item = [
                                    'fiscal_number' => $nif,
                                    'type' => (int) $tipo,
                                    'iban' => $iban
                                ];

                                array_push($list, $item);

                            }
                        }

                        $myObj->{'items'} = $list;
                      
                    }
                break;

                case 99:

                    $myObj->{'psp_code'} = $this->psp_code;
                    $myObj->{'psp_code_destination'} = '0098' ;
          
                    $myObj->{'items'} = (object) [];

                    $list = [];

                    $iban = 'PT50009851100000000117604';
                    $nif = 'PT101851618';
                    $tipo = 1;

                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];

                    array_push($list, $item);

                    $iban = 'PT50009851100000000117604';
                    $nif = 'PT163463530';
                    $tipo = 1;

                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];

                    array_push($list, $item);

                 

                    $myObj->{'items'} = $list;
                    \Log::channel($this->logchannel)->info('#: ' . count($list));  

                break;
    
                
                case 5:

                    $myObj->{'psp_code'} = $this->psp_code;
                    $this->bancoDestino = $this->psp_code;
                    $myObj->{'psp_code_destination'} = $this->bancoDestino ;
          
                    $myObj->{'items'} = (object) [];

                    $list = [];

                    $cop1000 = Cop::all()->take(1000);

                    foreach( $cop1000 as $copb ){
                         
                        $iban = $copb->iban;
                        $nif = $copb->nif;
                        $tipo = $copb->tp_entidade;
                        if ( $tipo == 'P') {
                            $tipo = 1;
                        }else {
                            $tipo = 2;
                        }

                        $item = [
                            'fiscal_number' => $nif,
                            'type' => (int) $tipo,
                            'iban' => $iban
                        ];

                        array_push($list, $item);

                          
                    }

                    $myObj->{'items'} = $list;
                    \Log::channel($this->logchannel)->info('#: ' . count($list));  

                break;
    

                case 4:

                    $myObj->{'psp_code'} = $this->psp_code;
                    $this->bancoDestino = $this->psp_code;
                    $myObj->{'psp_code_destination'} = $this->bancoDestino ;
          
                    $myObj->{'items'} = (object) [];

                    $list = [];

                    $cop1000 = Cop::all()->take(1001);

                    foreach( $cop1000 as $copb ){
                         
                        $iban = $copb->iban;
                        $nif = $copb->nif;
                        $tipo = $copb->tp_entidade;
                        if ( $tipo == 'P') {
                            $tipo = 1;
                        }else {
                            $tipo = 2;
                        }

                        $item = [
                            'fiscal_number' => $nif,
                            'type' => (int) $tipo,
                            'iban' => $iban
                        ];

                        array_push($list, $item);

                          
                    }

                    $myObj->{'items'} = $list;
                    \Log::channel($this->logchannel)->info('#: ' . count($list));  

                break;

                default:
                    return null;
            }

            //timestamp
            if ( $nrteste != 9 ) {
                $myObj->{'timestamp'} = $this->timestamp;
            }
            

            //encode json object


            // $json = json_encode($yourArray, JSON_UNESCAPED_UNICODE);
            // $json = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json);
     
            // $json = json_encode(array_map('trim', $yourArray));
            // $json = str_replace("\t", '', $json);

            $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
          
            $myJSON = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $myJSON);
            //\Log::channel($this->logchannel)->info($myJSON);
            $myJSON = str_replace(" ", '', $myJSON);
            

            return $myJSON;
   

          }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }



    private function getAccessToken()
    {
        try {

            $access_token = '';

            $this->bpService = new BancoPortugalService( $this->logchannel, $this->isConnProduction );

            //set metateste
            $this->bpService->setMetaTeste($this->metateste);
            //set payload
            $this->bpService->setPayload( $this->payloadJson);
            
            $access_token = $this->bpService->getAccessToken();
            
            $this->token_id = $this->bpService->getTokenID();

            return $access_token;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return '';
        }
  
    }

    private function saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs , $contrato)
    {
        //\Log::channel($this->logchannel)->info('SAVE Request to DB'); 
        try {
            $pedido = [
                'dt_pedido' => date('Y-m-d'),
                'token_id' => $this->token_id , 
                'payload' => json_encode($this->payloadJson, JSON_UNESCAPED_SLASHES), 
                'response' => json_encode($response, JSON_UNESCAPED_SLASHES),
                'audience' => trim($audience),
                'correlation_id' => $correlation_id ,
                'n_netcaixa' => $contrato,
                'user_id' => 0, 
                'timeelapsed' => $time_elapsed_secs,
                'http_code_response' => $this->httpcode 
            ];
            //\Log::channel($this->logchannel)->info('Request: ' . print_r($pedido, true)); 
            $requestBP = BPPLRequest::insertGetId( $pedido);
            $request_id =  $requestBP;
            //\Log::channel($this->logchannel)->info('Request saved to db with id ' .  $request_id  ); 
            
            return $request_id;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Request NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
            return 0;
        }
    }

    private function savePayloadToDB($request_id, $data ,$contrato)
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

    private function inativaTokenDB()
    {
        try {
            if ( ! $this->bpService ) {
                \Log::channel($this->logchannel)->info('BP Service not initialized');
                return false;
            }
           return $this->bpService->inativaTokenDB();
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function tokenValido()
    {
       return $this->token_valido;
    }

 
    public function getNomeFromIban()
    {

          //POST
            //URL:  /conp/cops
            //descr: recebe um request com um IBAN e devolve o nome do primeiro titular da conta bancária
  

        \Log::channel($this->logchannel)->info( 'ApiCop.cops... TO DO getNomeFromIban');
        \Log::channel($this->logchannel)->info( 'Call Banco  Portugal..../conp/cops');
        \Log::channel($this->logchannel)->info( 'IBAN: ' . $this->iban);

        try {
            //verifica se o iban esta preenchido
            if ( strlen(trim( $this->iban )) != 25 ) {
                \Log::channel($this->logchannel)->info( 'IBAN com tamanho errado.');
                return 'IBAN com tamanho errado.';
            }
            //cria o modelo de IBAN  
            $modelo = new IBAN( $this->iban );
            $modelo->setLogChannel( $this->logchannel );
            $modelo->setIsConnProduction( $this->isConnProduction );
            $modelo->print();
            //nome do primeiro titular
            $nome =  $modelo->getNomePrimeiroTitular();
            $nif =  $modelo->getNif();
            $banco =  $modelo->getBancoIban();
            $nmbanco =  $modelo->getNomeBanco();
            $swift = $modelo->getSwift();
           // \Log::channel($this->logchannel)->info( 'NOME: ' . $nome);


            $result = [];
            $result['iban'] = trim($this->iban);
            $result['nome'] = trim($nome);
            $result['nif'] = trim($nif);
            $result['banco'] = trim($banco);
            $result['nmbanco'] = trim($nmbanco);
            $result['swift'] = trim($swift);

            return $result;
          
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw new \Exception('Excecao no BANCO de PORTUGAL : ' . $e->getMessage());
        }
      
    }

    public function getNomesFromIbans()
    {
        \Log::channel($this->logchannel)->info( 'ApiCop.cops... TO DO ');
        \Log::channel($this->logchannel)->info( 'Call Banco  Portugal..../conp/cops');


        try {

            $listaIBANs = IBAN::getIBANSFromPetc($this->logchannel, $this->isConnProduction );

            $results = [];

            if ( $listaIBANs ) {
                if ( count($listaIBANs) > 0  ) {
                    foreach( $listaIBANs as $iban ){

                        \Log::channel($this->logchannel)->info( $iban['iban_ordenante'] );
                      
                        // //verifica se o iban esta preenchido
                        // if ( strlen(trim( $iban[$iban] )) != 25 ) {
                        //     \Log::channel($this->logchannel)->info( 'IBAN com tamanho errado.');
                        //     return 'IBAN com tamanho errado.';
                        // }
                        //cria o modelo de IBAN  
                        $modelo = new IBAN( $iban['iban_ordenante'] );
                        $modelo->setLogChannel( $this->logchannel );
                        $modelo->setIsConnProduction( $this->isConnProduction );
                        //$modelo->print();
                        //nome do primeiro titular
                        $nome =  $modelo->getNomePrimeiroTitular();
                        $nif =  $modelo->getNif();
                        $banco =  $modelo->getBancoIban();
                        $nmbanco =  $modelo->getNomeBanco();
                        $swift = $modelo->getSwift();

                        $result = [];
                        $result['iban_ordenante'] = trim($iban['iban_ordenante']);
                        $result['nome'] = trim($nome);
                        $result['nif'] = trim($nif);
                        $result['banco'] = trim($banco);
                        $result['nmbanco'] = trim($nmbanco);
                        $result['swift'] = trim($swift);

                        array_push( $results, $result);

                        \Log::channel($this->logchannel)->info( $iban['iban_dst'] );
                      
                        // //verifica se o iban esta preenchido
                        // if ( strlen(trim( $iban[$iban] )) != 25 ) {
                        //     \Log::channel($this->logchannel)->info( 'IBAN com tamanho errado.');
                        //     return 'IBAN com tamanho errado.';
                        // }
                        //cria o modelo de IBAN  
                        $modelo = new IBAN( $iban['iban_dst'] );
                        $modelo->setLogChannel( $this->logchannel );
                        $modelo->setIsConnProduction( $this->isConnProduction );
                        //$modelo->print();
                        //nome do primeiro titular
                        $nome =  $modelo->getNomePrimeiroTitular();
                        $nif =  $modelo->getNif();
                        $banco =  $modelo->getBancoIban();
                        $nmbanco =  $modelo->getNomeBanco();
                        $swift = $modelo->getSwift();
                        
                        $result = [];
                        $result['iban_dst'] = trim($iban['iban_dst']);
                        $result['nome'] = trim($nome);
                        $result['nif'] = trim($nif);
                        $result['banco'] = trim($banco);
                        $result['nmbanco'] = trim($nmbanco);
                        $result['swift'] = trim($swift);

                        array_push( $results, $result);
                    }
                } 
            }

            \Log::channel($this->logchannel)->info( print_r ($results, true) );

            return $results;
           

            //POST
            //URL:  /conp/cops
            //descr: recebe um request com um IBAN e devolve o nome do primeiro titular da conta bancária
           
            // return ( (new ApiRepository( $this->logchannel, $this->isConnProduction ) )->getNomePrimeiroTitular( $modelo ) );
    

            // return trim($nome);

        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw new \Exception('Excecao no BANCO de PORTUGAL : ' . $e->getMessage());
        }
      
    }
 
    /*
    |--------------------------------------------------------------------------
    | CURL CALLS
    |--------------------------------------------------------------------------
    */
  
    private function callUsingCURL($audience,  $payload, $headers)
    {
        try {

            \Log::channel($this->logchannel)->info( 'ApiCop callUsingCURL _________');
            \Log::channel($this->logchannel)->info( 'audience:' . $audience );
            \Log::channel($this->logchannel)->info( 'payload: ' . $payload);
            \Log::channel($this->logchannel)->info( 'headers: ' . print_r($headers, true));
     
            $storageDir = storage_path('app/curl-cop-'. date('Y-md').'txt');
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

            if ( $this->httpcode != '200'  ) {
                \Log::channel($this->logchannel)->info('incrementa contador ' . $this->psp_code_destination);

                try {
                    $counter =  \Storage::disk('local-counter')->get('counter-'.$this->psp_code_destination.'-'.$this->httpcode .'.txt');
                    $counter+=1;
                    \Storage::disk('local-counter')->put('counter-'.$this->psp_code_destination.'-'.$this->httpcode .'.txt', $counter);
                }catch(Exception $e){
                    \Log::channel($this->logchannel)->error($e->getMessage());
                }

            }
         

            //get HTTP HEADER 
            //$httpinfo_header = curl_getinfo($curl);
            // \Log::channel($this->logchannel)->info('HTTP HEADER----');
            // \Log::channel($this->logchannel)->info($httpinfo_header);
           
            $data = json_decode($response, true);
            //\Log::channel($this->logchannel)->info( 'JSON : ' . print_r($data, true) );

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