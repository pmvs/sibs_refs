<?php 

namespace App\Services\BancoPortugal;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use  App\Repositories\BancoPortugal\ProxyLookupRepository;

use DateTime;

use App\Models\ADFSToken;
use App\Models\BPPLRequest;
use App\Models\PlPayload;
use App\Models\PlAssociacao;

class ApiProxyLookup
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
    private $contrato = '';
    private $psp_code = '';
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
    private $metateste = [];
    private $token_valido = false;
    private $access_token = '';
    private $token_id = '';
    private $http_code = '';
    private $payloadJson = '';
    private $bpService = null;
    private $phone_book = [];

    public function __construct() 
    {
        $this->logchannel = 'bancoportugal';
        $this->isConnProduction = config('app.connection_prod');
        $this->utilizador = null;
        $this->mensagemErro = '';
        $this->token_valido = false;
        $this->bpService = null;
        $this->sigla =  config('app.sigla');
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct ApiProxyLookup');
        \Log::channel($this->logchannel)->info('Connection de Produção ? ' . ( $this->isConnProduction ? 'SIM' : 'NÃO' ) );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 ApiProxyLookup');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        \Log::channel($this->logchannel)->info('__construct_2 ApiProxyLookup');
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
        \Log::channel($this->logchannel)->info('Timestamp : ' .  $this->timestamp );

    }

    public function setPhoneBook($phone_book)
    {
        $this->phone_book = $phone_book;  
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

    public function setData($data)
    {
        
        if ( $data ) 
        {
            if ( array_key_exists('psp_code', $data)) {
                $this->psp_code = $data['psp_code'];
            }
            if ( array_key_exists('customer_identifier', $data)) {
                $this->customer_identifier = $data['customer_identifier'];
            }
            if ( array_key_exists('customer_identifier_type', $data)) {
                $this->customer_identifier_type = $data['customer_identifier_type'];
            }
            if ( array_key_exists('fiscal_number', $data)) {
                $this->fiscal_number = $data['fiscal_number'];
            }
            if ( array_key_exists('customer_type', $data)) {
                $this->customer_type = $data['customer_type'];
            }
            if ( array_key_exists('iban', $data)) {
                $this->iban = $data['iban'];
            }
            if ( array_key_exists('correlation_id_origin', $data)) {
                $this->correlation_id_origin = $data['correlation_id_origin'];
            }
            if ( array_key_exists('timestamp', $data)) {
                $this->timestamp = $data['timestamp'];
            }
        }
    }

    public function setPayload($jsonPayload)
    {
        $this->payloadJson = $jsonPayload;
        \Log::channel($this->logchannel)->info('ApiProxyLookup : payloadJson SET');
    }

    public function setMetaTeste($metateste)
    {
        $this->metateste = $metateste;
        \Log::channel($this->logchannel)->info('ApiProxyLookup : metateste SET');
    }
    public function setContrato($contrato)
    {
        $this->contrato = $contrato;
        \Log::channel($this->logchannel)->info('ApiProxyLookup : contrato SET');
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

    public function getPreviousTimestamp()
    {
        /*
        * The `DateTime` constructor doesn't create objects with fractional seconds.
        * However, the static method `DateTime::createFromFormat()` does include the
        * fractional seconds in the object.  Finally, since ISO 8601 specifies only
        * millisecond precision, remove the last three decimal places from the timestamp.
        */
        // DateTime object with microseconds
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', '')); 

        $datetime =  $now->modify('-1 minute');

     
        // Truncate to milliseconds
        $nowFormatted = substr($datetime->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'; 
   
        return $nowFormatted ;

    }


    public function getPreviousTimestampBOM()
    {
        /*
        * The `DateTime` constructor doesn't create objects with fractional seconds.
        * However, the static method `DateTime::createFromFormat()` does include the
        * fractional seconds in the object.  Finally, since ISO 8601 specifies only
        * millisecond precision, remove the last three decimal places from the timestamp.
        */
        // DateTime object with microseconds
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', '')); 

       // $datetime =  $now->modify('+15 seconds');
      //  $datetime =  $now->modify('+48 seconds');
      //  $datetime =  $now->modify('-2 minutes');
       // $datetime =  $now->modify('-1 minute');
        $datetime =  $now;
        
        // $datetime =  $now->modify('+1 minute');
        // $datetime =  $now->modify('+50 seconds');
        // Truncate to milliseconds
        // $nowFormatted = substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'; 
        $nowFormatted = substr($datetime->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'; 
   
        return $nowFormatted ;

    }

    /*  
    |--------------------------------------------------------------------------
    | METHODS PUBLIC - EXPOSED TESTES
    |--------------------------------------------------------------------------
    */
    public function executaTeste()
    {
        try{

            if( count($this->metateste) == 0 ){
                \Log::channel($this->logchannel)->info('ApiProxyLookup : metaTeste not set');
                return '';
            }

            \Log::channel($this->logchannel)->info('ApiProxyLookup : executa teste ' . $this->metateste['nrteste'] . ' ' . $this->metateste['nmteste'] );

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
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }
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
            $correlation_id_actual = '';
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
                $correlation_id_actual =  $data['correlation_id'];
            }

            $contrato = 0;

            //grava pedido na BD
            $request_id = $this->saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            //grava payload na BD
            $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            //check what to do
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 

            //grava associacao na BD
            if ( $this->httpcode == 201 && $audience == 'https://wwwcert.bportugal.net/apigw/pl/mgmt/insert'  ) {
              
                \Log::channel($this->logchannel)->info('Vai efetuar a associação...'); 
              
                $this->saveAssociacaoToDB( $request_id, $data , $contrato);

            }
        
            //update dissociacao na BD
            if ( $this->httpcode == 200 && $audience == 'https://wwwcert.bportugal.net/apigw/pl/mgmt/delete'  ) {
             
                \Log::channel($this->logchannel)->info('Vai efetuar a dissociação...'); 

                $customer_identifier = ''; //not in payload
                if ( property_exists($this->payloadJson,'customer_identifier')) {
                    $customer_identifier = $this->payloadJson->customer_identifier;
                }
                $fiscal_number = ''; //not in payload
                if ( property_exists($this->payloadJson,'fiscal_number')) {
                    $fiscal_number = $this->payloadJson->fiscal_number;
                }
                $iban = ''; //not in payload
                if ( property_exists($this->payloadJson,'iban')) {
                    $iban = $this->payloadJson->iban;
                }
                //get active record association
                $correlation_id = $this->getActiveCorrelationIdFromDB($contrato,$customer_identifier,$fiscal_number, $iban);
                if ( $correlation_id != '' ) {
                    $this->updateDissociacaoToDB( $correlation_id_actual ,$correlation_id );
                }
              
            }

            return $response;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }

    }

    public function insert()
    {
        try{

            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO');
            }

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
            //$audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }

            //payload 
            $this->payloadJson = '';
            \Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(1);
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

            if ( !  $data ) {
                \Log::channel($this->logchannel)->info('Sem resposta do Bdp');
                return ['error' => true, 'message' => 'Sem resposta do Bdp'];
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
                        return $this->insert();
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
            $request_id = $this->saveRequestToDB($audience,$response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            //grava payload na BD
            $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            //check what to do
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 

            $audience_env = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience_env = config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            }

            //grava associacao na BD
            if ( $this->httpcode == 201 && ( trim($audience) == trim($audience_env) )  ) {
              
                \Log::channel($this->logchannel)->info('Vai invalidar associações existente...'); 
             
                $this->payloadJson = json_decode($this->payloadJson);
                \Log::channel($this->logchannel)->info( print_r($this->payloadJson, true));

                $this->invalidaAssociacoesExistentes( );

                \Log::channel($this->logchannel)->info('Vai efetuar a associação...'); 

                //grava nova associacao
                $this->saveAssociacaoToDB( $request_id, $data , $contrato );

            }

            return ['error' => false, 'response' => $response, 'status_code' => $this->httpcode];

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }

    }

    public function delete()
    {
        try{

            $this->token_valido = false;
           
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO');
            
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO');
            }

       
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
            //$audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }

            //payload 
            $this->payloadJson = '';
            \Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(2);
        
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

            if ( !  $data ) {
                \Log::channel($this->logchannel)->info('Sem resposta do Bdp');
                return ['error' => true, 'message' => 'Sem resposta do Bdp'];
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
                        return  $this->delete();
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

            $contrato = $this->contrato;

            //grava pedido na BD
            $request_id = $this->saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            //grava payload na BD
            $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            //check what to do
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 

            
            //update dissociacao na BD


            $audience_env = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience_env = config('enums.apibp.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            }
 
            if ( $this->httpcode == 200 && ( trim($audience) == trim($audience_env) )   ) {
             
                \Log::channel($this->logchannel)->info('Vai efetuar a dissociação...'); 
                //\Log::channel($this->logchannel)->info($this->payloadJson);
                //$this->payloadJson = json_encode($this->payloadJson,  JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                $this->payloadJson = json_decode($this->payloadJson);
                \Log::channel($this->logchannel)->info( print_r($this->payloadJson, true));

                $customer_identifier = ''; //not in payload
                if ( property_exists($this->payloadJson,'customer_identifier')) {
                    $customer_identifier = $this->payloadJson->customer_identifier;
                }
                $fiscal_number = ''; //not in payload
                if ( property_exists($this->payloadJson,'fiscal_number')) {
                    $fiscal_number = $this->payloadJson->fiscal_number;
                }
                $iban = ''; //not in payload
                if ( property_exists($this->payloadJson,'iban')) {
                    $iban = $this->payloadJson->iban;
                }
                //get active record association
                $correlation_id_ativo = $this->getActiveCorrelationIdFromDB($contrato,$customer_identifier,$fiscal_number, $iban);
                if ( $correlation_id_ativo != '' ) {
                    $this->updateDissociacaoToDB( $correlation_id , $correlation_id_ativo);
                }
              
            }

            return ['error' => false, 'response' => $response, 'status_code' => $this->httpcode];


        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }

    }

    public function reativate()
    {
        try{


            \Log::channel($this->logchannel)->info(  'Reativate ...' );
            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO');
             
            }


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
            //$audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }

            //payload 
            $this->payloadJson = '';
            \Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(6);
            \Log::channel($this->logchannel)->info($this->payloadJson);

            if ( ! $this->payloadJson ) {
                \Log::channel($this->logchannel)->warning('Payload NULL');
                return ['Erros Internos : Payload Nulo'];
            }

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

            if ( !  $data ) {
                \Log::channel($this->logchannel)->info('Sem resposta do Bdp');
                return ['error' => true, 'message' => 'Sem resposta do Bdp'];
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
                        return $this->reativate();
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
            $request_id = $this->saveRequestToDB($audience,$response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            //grava payload na BD
            $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            //check what to do
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 

            //grava associacao na BD

            $audience_env = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience_env = config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
            }

            if ( $this->httpcode == 201 && ( trim($audience) == trim($audience_env) )   ) {
              
                \Log::channel($this->logchannel)->info('Vai invalidar associações existente...'); 
             
                $this->payloadJson = json_decode($this->payloadJson);
                \Log::channel($this->logchannel)->info( print_r($this->payloadJson, true));

                $this->invalidaAssociacoesExistentes( );

                \Log::channel($this->logchannel)->info('Vai efetuar a associação...'); 
 
                //grava nova associacao
                $this->saveAssociacaoToDB( $request_id, $data , $contrato );

            }

            return ['error' => false, 'response' => $response, 'status_code' => $this->httpcode];

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }

    }

    public function eliminate()
    {
        try{


            \Log::channel($this->logchannel)->info(  'Eliminate ...' );
            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO');
         
            }


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
            // $audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }

            //payload 
            $this->payloadJson = '';
            \Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(7);
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

            if ( !  $data ) {
                \Log::channel($this->logchannel)->info('Sem resposta do Bdp');
                return ['error' => true, 'message' => 'Sem resposta do Bdp'];
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
                        return $this->eliminate();
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
            $request_id = $this->saveRequestToDB($audience,$response, $correlation_id, $time_elapsed_secs,$contrato  );
           
            //grava payload na BD
            $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );

            //check what to do
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);  
            \Log::channel($this->logchannel)->info('Status CODE: ' . $this->httpcode); 
           
            //update dissociacao na BD
            $audience_env = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            if ( config('app.env') == 'prod' ) {
                $audience_env = config('enums.apibp.endpoints_plcp.PL_GESTAO_DELETE_ASSOC');
            }

            if ( $this->httpcode == 200 && ( trim($audience) == trim($audience_env) )  ) {
             
                \Log::channel($this->logchannel)->info('Vai efetuar a dissociação...'); 
              
                $this->payloadJson = json_decode($this->payloadJson);
                \Log::channel($this->logchannel)->info( print_r($this->payloadJson, true));

                $customer_identifier = ''; //not in payload
                if ( property_exists($this->payloadJson,'customer_identifier')) {
                    $customer_identifier = $this->payloadJson->customer_identifier;
                }
                $fiscal_number = ''; //not in payload
                if ( property_exists($this->payloadJson,'fiscal_number')) {
                    $fiscal_number = $this->payloadJson->fiscal_number;
                }
                $iban = ''; //not in payload
                if ( property_exists($this->payloadJson,'iban')) {
                    $iban = $this->payloadJson->iban;
                }
                //get active record association
                $correlation_id_ativo = $this->getPendingCorrelationIdFromDB($contrato,$customer_identifier,$fiscal_number, $iban);
                if ( $correlation_id_ativo != '' ) {
                    $this->updateDissociacaoToDB( $correlation_id , $correlation_id_ativo);
                }
            
            }


            return ['error' => false, 'response' => $response, 'status_code' => $this->httpcode];

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }

    }

    public function confirmation()
    {
        try{

            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_CONFIRMATION');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.PL_CONSULTA_CONFIRMATION');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO');
            }


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
            //$audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_CONFIRMATION');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.PL_CONSULTA_CONFIRMATION');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }

            //payload 
            $this->payloadJson = '';
            \Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(3);
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

            if ( !  $data ) {
                \Log::channel($this->logchannel)->info('Sem resposta do Bdp');
                return ['error' => true, 'message' => 'Sem resposta do Bdp'];
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
                        return $this->confirmation();
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
            $request_id = $this->saveRequestToDB($audience,$response, $correlation_id, $time_elapsed_secs,$contrato  );
           
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

    public function contacts()
    {
        try{

            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_CONTACTS');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_CONSULTAS');
        
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_plcp.PL_CONSULTA_CONTACTS');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.PROXYLOOKUP_CONSULTAS');
            }


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
            //$audience = $this->metateste['endpointteste'];
            $audience = config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_CONTACTS');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.PL_CONSULTA_CONTACTS');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }

            //payload 
            $this->payloadJson = '';
            \Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(4);
            \Log::channel($this->logchannel)->info($this->payloadJson);

            if ( ! $this->payloadJson ) {
                \Log::channel($this->logchannel)->warning('Payload NULL');
                return ['Erros Internos : Payload Nulo'];
            }


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

            if ( !  $data ) {
                \Log::channel($this->logchannel)->info('Sem resposta do Bdp');
                return ['error' => true, 'message' => 'Sem resposta do Bdp'];
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
                        return $this->contacts();
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

    public function account()
    {
        try{

            $this->token_valido = false;
            $this->metateste = [];
            $this->metateste['endpointteste'] = config('enums.apibp_dev.endpoints_pl.PL_CONSULTA_ACCOUNT');
            $this->metateste['resourceteste'] = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_CONSULTAS');
         
            if ( config('app.env') == 'prod' ) {
                $this->metateste['endpointteste'] = config('enums.apibp.endpoints_pl.PL_CONSULTA_ACCOUNT');
                $this->metateste['resourceteste'] = config('enums.apibp.resources_plcp.PROXYLOOKUP_CONSULTAS');
             
            }

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
           // $audience = $this->metateste['endpointteste'];
          //  $audience = 'https://wwwcert.bportugal.net/apigw/pl/lookup/account';
           //\Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            $audience = config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_ACCOUNT');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.PL_CONSULTA_ACCOUNT');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }
            
            //payload 
            $this->payloadJson = '';
            \Log::channel($this->logchannel)->info(  'Cria payload ...' );
            $this->payloadJson  = $this->criaPayload(5);
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

            if ( !  $data ) {
                \Log::channel($this->logchannel)->info('Sem resposta do Bdp');
                return ['error' => true, 'message' => 'Sem resposta do Bdp'];
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
                        return $this->account();
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
            $request_id = $this->saveRequestToDB($audience,$response, $correlation_id, $time_elapsed_secs,$contrato  );
           
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


    private function getActiveCorrelationIdFromDB($contrato,$customer_identifier,$fiscal_number, $iban)
    {
        try{

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

    private function getPendingCorrelationIdFromDB($contrato,$customer_identifier,$fiscal_number, $iban)
    {
        try{

            \Log::channel($this->logchannel)->info('Vai procurar associacoes pendentes');
            \Log::channel($this->logchannel)->info('Contrato : ' . $contrato);
            \Log::channel($this->logchannel)->info('customer_identifier : ' . $customer_identifier);
            \Log::channel($this->logchannel)->info('fiscal_number : ' . $fiscal_number);
            \Log::channel($this->logchannel)->info('iban: ' . $iban);

            $correlation_id = PlAssociacao::where( 'n_netcaixa', $contrato )
                ->where('customer_identifier', $customer_identifier)
                ->where('fiscal_number', $fiscal_number)
                ->where('iban', $iban)
                ->where('status', 2)
                ->pluck('correlation_id')->first();
        
            if ( ! $correlation_id ) {
                \Log::channel($this->logchannel)->info('Não foi encontrado nenhuma associacao pendente ');
                return '';
            }
            \Log::channel($this->logchannel)->info('Foi encontrada uma associacao pendente : ' . $correlation_id);

            return $correlation_id;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return '';
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

    private function saveRequestToDB($audience, $response, $correlation_id, $time_elapsed_secs , $contrato)
    {
        \Log::channel($this->logchannel)->info('SAVE Request to DB'); 
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

    private function savePayloadToDB($request_id, $data ,$contrato)
    {
        //grava payload na BD
        \Log::channel($this->logchannel)->info('SAVE Payload to DB'); 
        try {
           // $this->payloadJson = json_decode($this->payloadJson);
            \Log::channel($this->logchannel)->info(print_r($this->payloadJson, true)); 

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
            if ( property_exists($this->payloadJson,'psp_code')) {
                $psp_code = $this->payloadJson->psp_code;
            }
            $customer_identifier = ''; //not in payload
            if ( property_exists($this->payloadJson,'customer_identifier')) {
                $customer_identifier = $this->payloadJson->customer_identifier;
            }
            $fiscal_number = ''; //not in payload
            if ( property_exists($this->payloadJson,'fiscal_number')) {
                $fiscal_number = $this->payloadJson->fiscal_number;
            }
            $iban = ''; //not in payload
            if ( property_exists($this->payloadJson,'iban')) {
                $iban = $this->payloadJson->iban;
            }
            $customer_identifier_type = 0; //not in payload
            if ( property_exists($this->payloadJson,'customer_identifier_type')) {
                $customer_identifier_type = $this->payloadJson->customer_identifier_type;
            }
            $customer_type = 0; //not in payload
            if ( property_exists($this->payloadJson,'customer_type')) {
                $customer_type = $this->payloadJson->customer_type;
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
            $payloadBD = PlPayload::insertGetId( $payload );
            $payload_id =  $payloadBD;
            \Log::channel($this->logchannel)->info('Payload saved to db with id ' . $payload_id );
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Payload NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }

    private function saveAssociacaoToDB( $request_id, $data , $contrato )
    {
        \Log::channel($this->logchannel)->info('SAVE Associacao to DB'); 
        try {
            $associacao = [
                'dt_pedido' => date('Y-m-d'),
                'request_id' => $request_id ,
                'status' => 1, //ativo
                'n_netcaixa' =>  $contrato , 
                'user_id' => 0, 
                'psp_code' =>  $this->payloadJson->psp_code, 
                'customer_identifier' =>  $this->payloadJson->customer_identifier, 
                'customer_identifier_type' => $this->payloadJson->customer_identifier_type, 
                'fiscal_number' => $this->payloadJson->fiscal_number, 
                'customer_type' => $this->payloadJson->customer_type, 
                'iban' => $this->payloadJson->iban, 
                'correlation_id_origin' => $data['correlation_id'], 
                'correlation_id' => $data['correlation_id'],
                'timestamp' => $this->payloadJson->timestamp,
                'http_code' => $this->httpcode 
            ];

            $associacaoBD = PlAssociacao::insertGetId( $associacao );
            $associacao_id =  $associacaoBD;
            \Log::channel($this->logchannel)->info('Associacao saved to db with id ' . $associacaoBD );
      
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Associacao NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }

    private function invalidaAssociacoesExistentes(  )
    {
        \Log::channel($this->logchannel)->info('INVALIDA Associacao to DB'); 
        try {

            $dissociacaoBD = PlAssociacao::where('customer_identifier', $this->payloadJson->customer_identifier )
            ->where('fiscal_number', $this->payloadJson->fiscal_number )
            ->update( ['status'=> 0] );

            \Log::channel($this->logchannel)->info('Sem associacoes ativas para  ' . $this->payloadJson->fiscal_number  .  ' ' . $this->payloadJson->customer_identifier);
      
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('INVALIDAÇÃO NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }

    private function updateDissociacaoToDB( $correlation_id, $correlation_id_ativo )
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

    private function criaPayload( $tipo )
    {
        try{

            $myObj  = new \stdClass();

            switch( $tipo ){
                case 1:
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    //customer identifier
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                    //customer identifier type
                    $myObj->{'customer_identifier_type'} =  (int)$this->customer_identifier_type ;
                    //tipo de customer : singular / coletivo
                    $myObj->{'customer_type'} = (int)$this->customer_type;
                      //nif ou nipc
                    $myObj->{'fiscal_number'} = $this->fiscal_number;
                    //iban
                    $myObj->{'iban'} = $this->iban;
                    break;

                case 2:
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    //customer identifier
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                    //nif ou nipc
                    $myObj->{'fiscal_number'} = $this->fiscal_number;
                    //iban
                    $myObj->{'iban'} = $this->iban;
                    break;

                case 3:
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    //customer identifier
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                    $myObj->{'accounts'} = (object) [];
                    $list[] = ['iban' => $this->iban];
                    $myObj->{'accounts'} = $list; 
                    break;

                case 4:
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    $myObj->{'phone_book'} = (object) [];
                    if ( count($this->phone_book) > 0 ) {
                        $list = [];
                        foreach($this->phone_book as $phone){
                            $list[] = ['phone_number' => $phone];
                        }
                        // foreach($this->phone_book as $phone){
                        //     $list[] = [$phone];
                        // }
                    }
                    $myObj->{'phone_book'} = $list;   
                    break;

                case 5:
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    //customer identifier
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                    break;
                    
                case 6:
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    //customer identifier
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                    //customer identifier type
                    $myObj->{'customer_identifier_type'} =  (int)$this->customer_identifier_type ;
                    //tipo de customer : singular / coletivo
                    $myObj->{'customer_type'} = (int)$this->customer_type;
                        //nif ou nipc
                    $myObj->{'fiscal_number'} = $this->fiscal_number;
                    //iban
                    $myObj->{'iban'} = $this->iban;
                    //correlation_id_origin
                    $myObj->{'correlation_id_origin'} = $this->correlation_id_origin;
                    break;
                    
                case 7:
                    //psp code
                    $myObj->{'psp_code'} = $this->psp_code;
                    //customer identifier
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                    //nif ou nipc
                    $myObj->{'fiscal_number'} = $this->fiscal_number;
                    //iban
                    $myObj->{'iban'} = $this->iban;
                    //correlation_id_origin
                    $myObj->{'correlation_id_origin'} = $this->correlation_id_origin;
                    break;
    
                default:
                    return null;
            }

            
             //timestamp
             if ( config('app.sigla') == 'TVD') {
                $myObj->{'timestamp'} = $this->getPreviousTimestamp();
             }elseif ( config('app.sigla') == 'BOM' ) {
                $myObj->{'timestamp'} = $this->getPreviousTimestampBOM();
             }else {
                $myObj->{'timestamp'} = $this->timestamp;
             }

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


    // private function getActiveTokenFromDB()
    // {
    //     try{

    //         $tokenADFS = ADFSToken::where( 'dt_pedido', date('Y-m-d') )
    //             ->where('ativo', true)->first();
            
    //         if ( ! $tokenADFS ) {
    //             \Log::channel($this->logchannel)->info('Não foi encontrado nenhum token ativo');
    //             $this->token_valido = false;
    //             return null;
    //         }
    //         \Log::channel($this->logchannel)->info('Foi encontrado na BD um token ativo');

    //         return $tokenADFS;

    //     }catch(\Exception $e){
    //         \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

    // private function inativaTokenDB()
    // {
    //     try{
    //         \Log::channel($this->logchannel)->info('Inativa token DB...');
    //         ADFSToken::where('ativo', true)
    //         ->update(['ativo' => false]);
    //         $this->token_valido = false;
    //         \Log::channel($this->logchannel)->info('Token inativado');
    //         return true;
    //     }catch(\Exception $e){
    //         \Log::channel($this->logchannel)->info('Token NAO inativado');
    //         \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

    // private function getTokenFromADFS()
    // {
    //     try {

    //         //get jwt token bearer from BancoPortugalService
    //         $bpService = new BancoPortugalService( $this->logchannel, $this->isConnProduction );

    //        //generate jwt assertion
    //        \Log::channel($this->logchannel)->info('ApiProxyLookup : get jwtToken from BancoPortugalService');
    //        $jwtToken = $bpService->getJwtToken();
    //        if( $jwtToken  == '' ){
    //            \Log::channel($this->logchannel)->info('ApiProxyLookup : jwtToken not set');
    //            return ['success' => false, 'error' => 'erro a criar o JWT' , 'response' => ''];
    //        }
    //        //get token from ADFS 
    //        \Log::channel($this->logchannel)->info('ApiProxyLookup : get bearer token from ADFS');
    //        $tokenBearer = $bpService->getBearerTokenFromADFS(  $this->metaTeste['resourceteste'] , $jwtToken);  
    //        \Log::channel($this->logchannel)->info(print_r($tokenBearer, true));

    //        if ( array_key_exists('error',$tokenBearer)) {
    //            //erro na obtencao de token 
    //            return ['success' => false, 'error' => 'erro na obtencao de token ' , 'response' => $tokenBearer];
    //        }
 
    //        $access_token = $tokenBearer['response']['access_token'];
    //        $this->access_token = $access_token;
    //        \Log::channel($this->logchannel)->info('ApiProxyLookup : access_token: ' . $access_token );

    //        //inativa tokens ativos anteriores
    //        $this->inativaTokenDB();

    //        //save token to database 
    //        try {
    //            $tokendata = [
    //                'dt_pedido' => date('Y-m-d'),
    //                'resource' => $tokenBearer['payload']['resource'], 
    //                'client_id' => $tokenBearer['payload']['client_id'], 
    //                'client_assertion' => $tokenBearer['payload']['client_assertion'], 
    //                'access_token' => trim($tokenBearer['response']['access_token']), 
    //                'token_type' => $tokenBearer['response']['token_type'], 
    //                'expires_in' => $tokenBearer['response']['expires_in'], 
    //                'ativo' => true,
    //                'audience' => $tokenBearer['audience'],
    //                'valid_until' => now()->addSeconds( $tokenBearer['response']['expires_in'] )
    //            ];
    //            $tokenADFS = ADFSToken::insertGetId( $tokendata );
    //            \Log::channel($this->logchannel)->info('Token saved to db'); 
    //            $this->token_id =  $tokenADFS;
    //            \Log::channel($this->logchannel)->info('Token ID ' .  $this->token_id ); 
    //        }catch(\Exception $e){
    //             \Log::channel($this->logchannel)->error('Token NOT saved to db'); 
    //            \Log::channel($this->logchannel)->error($e->getMessage()); 
    //        }

    //        return ['success' => true, 'error' => '' , 'response' => $tokenBearer];

    //     }catch(\Exception $e){
    //         \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
    //         return ['success' => false, 'error' => e->getMessage() , 'response' => ''];
    //     }
    // }

    private function tokenValido()
    {
        return $this->token_valido;
    }

    public function base64_url_decode( $input )
    {
        return base64_decode( str_replace(['-', '_'], ['+', '/'], $input ) );
    }

    public function decode_AccessToken( $token )
    {
        $info = [ 'token_type' => $token['token_type'], 
                'scope' => $token['scope'], 
                'expires_in' => $token['expires_in'], 
                'ext_expires_in' => $token['ext_expires_in'] ];

        // Explode the token to get each of the three parts inside it (Header, Body, Signature)
        $parts = explode(".", $token['access_token']);

        // Decode the header as an Array
        $header = (array)json_decode( $this->base64_url_decode( $parts[0] ) );

        // Decode the Body as an Array
        $body = (array)json_decode( $this->base64_url_decode( $parts[1] ) );

        // Decode the Signature
        $signature = $this->base64_url_decode( $parts[2] );

        // Return value
        return ['Info' => $info,'Header' => $header, 'Body' => $body, 'Signature' => $signature];
    }

    public function get_SigningKeys()
    {   
        $ch = curl_init();
        // Specify the URL to retrieve
        curl_setopt( $ch, CURLOPT_URL, $this->keys_url);
        // Receive a JSON response
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, True );
        // Do not validate SSL Certificates, by default SSL doesn't work with CURL.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // execute request
        $result = curl_exec( $ch );

        curl_close($ch);

        return (array)json_decode( $result );
    }

    public function get_PublicKey( $decodedToken )
    {
        $keys = $this->get_SigningKeys();

        // The ID of the key that was used
        $keyID = $decodedToken['Header']['kid'];

        foreach( $keys['keys'] as $key )
        {
            if( $key->kid === $keyID )
            {
                // Create the Plain Text Certificate Chain
                $plainTextKeyCert  = "-----BEGIN CERTIFICATE-----\r\n";
                $plainTextKeyCert .= chunk_split( str_replace( " ", "", $key->x5c[0] ), 64, "\r\n" );
                $plainTextKeyCert .= "-----END CERTIFICATE-----";
                // Read the Certificate Chain
                $cert              = openssl_x509_read( $plainTextKeyCert );
                // Extract the Public Key from the Certificate Chain
                $pubkey            = openssl_pkey_get_public( $cert );
                // Return the Public Key
                return openssl_pkey_get_details( $pubkey )['key'];
            }
        }

        return False;
    }


    /*
    |--------------------------------------------------------------------------
    | METHODS PUBLIC - EXPOSED SERVICES
    |--------------------------------------------------------------------------
    */



    
 
    /*
    |--------------------------------------------------------------------------
    | CURL CALLS
    |--------------------------------------------------------------------------
    */
  
  
    private function callUsingCURL($audience,  $payload, $headers)
    {
        try {

            \Log::channel($this->logchannel)->info( 'ApiProxyLookup callUsingCURL _________');
            \Log::channel($this->logchannel)->info( 'audience:' . $audience );
            \Log::channel($this->logchannel)->info( 'payload: ' . $payload);
            \Log::channel($this->logchannel)->info( 'headers: ' . print_r($headers, true));
     
            $storageDir = storage_path('app/curlerrproxy-'. date('Y-md').'txt');
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
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POST => true, 
                CURLOPT_POSTFIELDS => $payload ,
            );
  
            curl_setopt_array($curl, $params);
        

            \Log::channel($this->logchannel)->info('----curl_exec----');
            $start = microtime(true);
            $response = curl_exec($curl);
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
         
            \Log::channel($this->logchannel)->info( '----RAW RESPONSE----');
            \Log::channel($this->logchannel)->info( $response );

            //get curl errors
            $err = curl_error($curl);
            \Log::channel($this->logchannel)->info('ERROS----' . $err );
        
            $errno = curl_errno($curl);
            \Log::channel($this->logchannel)->info('ERRO NUMBER----'. $errno);
          
            //get HTTP code 
            $this->httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            \Log::channel($this->logchannel)->info('HTTP CODE----'. $this->httpcode );

            // //get HTTP HEADER 
            // $httpinfo_header = curl_getinfo($curl);
            // \Log::channel($this->logchannel)->info('HTTP HEADER----');
            // \Log::channel($this->logchannel)->info($httpinfo_header);
           
            $data = json_decode($response, true);
            \Log::channel($this->logchannel)->info( 'JSON : ' . print_r($data, true) );

            return $response;

        } catch ( \Symfony\Component\ErrorHandler\Error\FatalError $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
           //throw $e;
            return response()->json(['data' => 'Ocorreram problemas de comunicação com o Banco de Portugal'], 200, ['Content-Type', 'application/json']);
     
        } catch ( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
           throw $e;
            return response()->json(['data' => 'Ocorreram problemas de comunicação com o Banco de Portugal'], 200, ['Content-Type', 'application/json']);
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