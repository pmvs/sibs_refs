<?php 

namespace App\Services\Testes;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;
use DateTime;

class PLAssociacao extends Controller implements PLInterface
{
    private $logchannel = 'testes';
    private $testNumber = 0;
    private $tabPosition =0;
    private $typeOfIndisponibilidade = ['add', 'update', 'list'];
  
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
        \Log::channel($this->logchannel)->info('__construct PLAssociacao');
    }

    public function __construct_3($logchannel, $tabNumber, $testNumber) 
    {
        $this->logchannel = $logchannel;
        $this->tabPosition =$tabPosition;
        $this->testNumber = $testNumber;

        \Log::channel($this->logchannel)->info('__construct_3 PLAssociacao');
    }

    public function getTimestamp()
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
        $timestamp = $nowFormatted; 
   
        return $timestamp;

    }

    public function getTimestampFuture()
    {
        /*
        * The `DateTime` constructor doesn't create objects with fractional seconds.
        * However, the static method `DateTime::createFromFormat()` does include the
        * fractional seconds in the object.  Finally, since ISO 8601 specifies only
        * millisecond precision, remove the last three decimal places from the timestamp.
        */
        // DateTime object with microseconds
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        
        $datetime =  $now->modify('+1 hour');
        // Truncate to milliseconds
        $nowFormatted = substr($datetime->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'; 
        //set timestamp
        $timestamp = $nowFormatted; 

        \Log::channel($this->logchannel )->info('Timestamp future: ' . $timestamp);
   
        return $timestamp;

    }

    public function getTimestampPast()
    {
        /*
        * The `DateTime` constructor doesn't create objects with fractional seconds.
        * However, the static method `DateTime::createFromFormat()` does include the
        * fractional seconds in the object.  Finally, since ISO 8601 specifies only
        * millisecond precision, remove the last three decimal places from the timestamp.
        */
        // DateTime object with microseconds
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        
        $datetime =  $now->modify('-1 hour');
        // Truncate to milliseconds
        $nowFormatted = substr($datetime->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'; 
        //set timestamp
        $timestamp = $nowFormatted; 

        \Log::channel($this->logchannel )->info('Timestamp past: ' . $timestamp);
   
        return $timestamp;

    }

    public function efetuaPedido($fields )
    {
        try {
           
            \Log::channel($this->logchannel)->info('efetuaPedido em PLAssociacao');
            \Log::channel($this->logchannel)->info('fillData em PLAssociacao...');
           
            $myJSON = $this->fillData($fields);
            if ( ! $myJSON ) {
                \Log::channel($this->logchannel)->warning('fillData em PLAssociacao...NOT OK');
                return 'Erro: Pedido de execução de teste inválido -> fillData em PLAssociacao';
            }
            \Log::channel($this->logchannel)->info('fillData em PLAssociacao...OK');

            \Log::channel($this->logchannel)->info('executaTeste em PLAssociacao');
            \Log::channel($this->logchannel)->info($myJSON);
            $response = $this->executaTeste($fields, $myJSON);
            if ( ! $response ) {
                \Log::channel($this->logchannel)->warning('executaTeste em PLAssociacao...NOT OK');
                return 'Erro: Pedido de execução de teste inválido -> executaTeste em PLAssociacao';
            }
            \Log::channel($this->logchannel)->info('executaTeste em PLAssociacao...OK');

            $data = [
                'response' => $response,
                'data' => $myJSON
            ];

            \Log::channel($this->logchannel)->info('Vai retornar : ' . print_r( $data , true) );

            return $data;

          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: Pedido de execução de teste inválido -> ' . $e->getMessage();
        }
    
    
    }

    public function fillData( $fields )
    {
        try {

            \Log::channel($this->logchannel)->info('fillData em PLAssociacao...teste nr ' . $fields['testNumber'] );
           
            //create object
            $myObj  = new \stdClass();

            switch ( $fields['testNumber'] ){
                case 1:
                case 2: 
                case 3: 
                case 4:
                case 5:
                case 6: 
                case 7: 
                case 9: 
                case 15: 
                case 16: 
                case 17:
                case 18:
                case 19:
                case 21:
                case 24:
                    $myObj = $this->createTeste1( $fields );
                    break;

                case 8: 
                    $myObj = $this->createTeste2( $fields );
                    break;

                case 23:
                case 27:
                    $myObj = $this->createTeste3( $fields );
                    break;

                 default:
                    \Log::channel($this->logchannel)->warning( 'Test number not implemented yet' ) ;
                     return null;
             }
             
             if ( ! $myObj ) {
                \Log::channel($this->logchannel)->error( 'JSON object is null.' ) ;
                return null;
             }

            //encode json object
            $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            \Log::channel($this->logchannel)->info( $myJSON ) ;

        
            return  $myJSON ;

        }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return null;
        }
    
    }

    private function createTeste1( $fields )
    {
        try {

            \Log::channel($this->logchannel )->info('createTeste1 with ' . print_r( $fields , true));

            //create object
            $myObj  = new \stdClass();

         
            if (array_key_exists('pspCode', $fields)) {
                if ( ! empty($fields['pspCode'])) {
                    $myObj->{'psp_code'} = $fields['pspCode'];
                }else {
                    \Log::channel($this->logchannel )->info('iban not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('iban not set ');
                return null;
            }

            if (array_key_exists('iban', $fields)) {
                if ( ! empty($fields['iban'])) {
                    $myObj->{'iban'} = trim($fields['iban']);
                }else {
                    \Log::channel($this->logchannel )->info('iban not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('iban not set ');
                return null;
            }
            if (array_key_exists('nif', $fields)) {
                if ( ! empty($fields['nif'])) {
                    $myObj->{'fiscal_number'} = trim($fields['nif']);
                }else {
                    \Log::channel($this->logchannel )->info('nif not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('nif not set ');
                return null;
            }
            if (array_key_exists('identificador', $fields)) {
                if ( trim($fields['identificador']) != '' ) {
                    $myObj->{'customer_identifier'} = trim($fields['identificador']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_identifier not set 1');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_identifier not set 2');
                return null;
            }
            if (array_key_exists('tipoidentificador', $fields)) {
                if ( trim($fields['tipoidentificador']) != '' ) {
                    $myObj->{'customer_identifier_type'} = (int) trim($fields['tipoidentificador']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_identifier_type not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_identifier_type not set ');
                return null;
            }
            if (array_key_exists('tipocustomer', $fields)) {
                if (trim($fields['tipocustomer']) != '' ) {
                    $myObj->{'customer_type'} = (int) trim($fields['tipocustomer']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_type not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_type not set ');
                return null;
            }
            $myObj->{'timestamp'} = $this->getTimeStamp();
        
            return $myObj; 

         }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return null;
        }
       
    }

    private function createTeste2( $fields )
    {
        try {

            \Log::channel($this->logchannel )->info('createTeste2 with ' . print_r( $fields , true));

            //create object
            $myObj  = new \stdClass();

         
            if (array_key_exists('pspCode', $fields)) {
                if ( ! empty($fields['pspCode'])) {
                    $myObj->{'psp_code'} = $fields['pspCode'];
                }else {
                    \Log::channel($this->logchannel )->info('iban not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('iban not set ');
                return null;
            }

            if (array_key_exists('iban', $fields)) {
                if ( ! empty($fields['iban'])) {
                    $myObj->{'iban'} = trim($fields['iban']);
                }else {
                    \Log::channel($this->logchannel )->info('iban not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('iban not set ');
                return null;
            }
            if (array_key_exists('nif', $fields)) {
                if ( ! empty($fields['nif'])) {
                    $myObj->{'fiscal_number'} = trim($fields['nif']);
                }else {
                    \Log::channel($this->logchannel )->info('nif not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('nif not set ');
                return null;
            }
            if (array_key_exists('identificador', $fields)) {
                if ( trim($fields['identificador']) != '' ) {
                    $myObj->{'customer_identifier'} = '';
                }else {
                    $myObj->{'customer_identifier'} = '';
                    \Log::channel($this->logchannel )->info('customer_identifier not set continue');
                }
            }else{
                $myObj->{'customer_identifier'} = '';
                \Log::channel($this->logchannel )->info('customer_identifier not set ... continue');
            }
            if (array_key_exists('tipoidentificador', $fields)) {
                if ( trim($fields['tipoidentificador']) != '' ) {
                    $myObj->{'customer_identifier_type'} = (int) trim($fields['tipoidentificador']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_identifier_type not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_identifier_type not set ');
                return null;
            }
            if (array_key_exists('tipocustomer', $fields)) {
                if (trim($fields['tipocustomer']) != '' ) {
                    $myObj->{'customer_type'} = (int) trim($fields['tipocustomer']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_type not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_type not set ');
                return null;
            }
            $myObj->{'timestamp'} = $this->getTimeStamp();
        
            return $myObj; 

         }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return null;
        }
       
    }

    private function createTeste3( $fields )
    {
        try {

            \Log::channel($this->logchannel )->info('createTeste3 with ' . print_r( $fields , true));

            //create object
            $myObj  = new \stdClass();

         
            if (array_key_exists('pspCode', $fields)) {
                if ( ! empty($fields['pspCode'])) {
                    $myObj->{'psp_code'} = $fields['pspCode'];
                }else {
                    \Log::channel($this->logchannel )->info('iban not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('iban not set ');
                return null;
            }

            if (array_key_exists('iban', $fields)) {
                if ( ! empty($fields['iban'])) {
                    $myObj->{'iban'} = trim($fields['iban']);
                }else {
                    \Log::channel($this->logchannel )->info('iban not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('iban not set ');
                return null;
            }
            if (array_key_exists('nif', $fields)) {
                if ( ! empty($fields['nif'])) {
                    $myObj->{'fiscal_number'} = trim($fields['nif']);
                }else {
                    \Log::channel($this->logchannel )->info('nif not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('nif not set ');
                return null;
            }
          
            if (array_key_exists('identificador', $fields)) {
                if ( trim($fields['identificador']) != '' ) {
                    $myObj->{'customer_identifier'} = trim($fields['identificador']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_identifier not set 1');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_identifier not set 2');
                return null;
            }

            if (array_key_exists('tipoidentificador', $fields)) {
                if ( trim($fields['tipoidentificador']) != '' ) {
                    $myObj->{'customer_identifier_type'} = (int) trim($fields['tipoidentificador']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_identifier_type not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_identifier_type not set ');
                return null;
            }

            if (array_key_exists('tipocustomer', $fields)) {
                if (trim($fields['tipocustomer']) != '' ) {
                    $myObj->{'customer_type'} = (int) trim($fields['tipocustomer']);
                }else {
                    \Log::channel($this->logchannel )->info('customer_type not set ');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('customer_type not set ');
                return null;
            }

            if (array_key_exists('correlation_id_origin', $fields)) {
                if ( trim($fields['correlation_id_origin']) != '' ) {
                    $myObj->{'correlation_id_origin'} = trim($fields['correlation_id_origin']);
                }else {
                    \Log::channel($this->logchannel )->info('correlation_id_origin not set 1');
                    return null;
                }
            }else{
                \Log::channel($this->logchannel )->info('correlation_id_origin not set 2');
                return null;
            }


            $myObj->{'timestamp'} = $this->getTimeStamp();
        
            return $myObj; 

         }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return null;
        }
       
    }

    private function executaTeste($fields, $myJSON )
    {
        try{

            \Log::channel($this->logchannel)->info('PLAssociacao : executa teste ');
           // $this->token_valido = config('app.access_token_valido');
            $this->token_valido = false;

            $resource = '';
            $resource = config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO');
            if ( config('app.env') == 'prod' ) {
                $resource = config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO');
            }
            \Log::channel($this->logchannel)->info(  'Resource ...' . $resource);
            if (  $resource == '' ) {
                \Log::channel($this->logchannel)->warning('Não foi possivel obter o url de resource');
                return ['Erros Internos : Não foi possivel obter o url de resource'];
            }

            //create service fro oauth2 
            $oauth2  = new \App\Services\Testes\Oauth2Indisponibilidade($resource);

            //get access token 
            $access_token = $oauth2->getAccessToken();
            if (  $access_token == '' ) {
                \Log::channel($this->logchannel)->info('Não foi possivel obter um token para autenticação');
                return ['Erros Internos : Não foi possivel obter um token para autenticação'];
            }
            
            //get token id from DB last inserted token
            $tokenid = $oauth2->getTokenId();
            \Log::channel($this->logchannel)->info('Vai efetuar chamada com o access token obtido');
            \Log::channel($this->logchannel)->info('token id obtido: ' . $tokenid);

            //add auth headers 
            $headers = [];
            $headers[] = 'Content-Type:application/json';
            $headers[] = "Authorization: Bearer ".$access_token;

            //endpoint audience
            $audience = '';
            
            //TO DO : verificar se é um add , ou update 
            switch ( $fields['testNumber'] ){
                case 1: 
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                case 15:
                case 16:
                case 17:
                case 18:
                case 19:
                case 21:
                case 23:
                case 24:
                case 27:
                    $audience = config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
                    if ( config('app.env') == 'prod' ) {
                        $audience = config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC');
                    }
                    \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
                    if (  $audience == '' ) {
                        \Log::channel($this->logchannel)->warning('Não foi possivel obter o url de destino');
                        return ['Erros Internos : Não foi possivel obter o url de destino'];
                    }
                    break;

                default:
                    \Log::channel($this->logchannel)->warning('Erros Internos : Não foi possivel obter o url de destino para teste indefinido');
                    return ['Erros Internos : Não foi possivel obter o url de destino para teste indefinido'];
            }

        
            //make a culr call
            $start = microtime(true);
            $response = $oauth2->callUsingCURL_3( $audience , $myJSON, $headers);
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs . 's');
        
            //verifica validade do JWT
            $data = json_decode($response, true);

            \Log::channel($this->logchannel)->info('Data : ' . print_r($data, true));

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
                    $oauth2->inativaTokenDB();
                    if ( $invalidJWT ) {
                        \Log::channel($this->logchannel)->info('**********'); 
                        \Log::channel($this->logchannel)->info('*****JWT TIMEOUT EXECUTA NOVO PEDIDO*****');
                        \Log::channel($this->logchannel)->info('**********');  
                        $this->executaTeste( $fields, $myJSON );
                    }
                }else {

                    if ( $data['statusCode'] == 400 ) {
                        //erro na chamada 
                        \Log::channel($this->logchannel)->error('Ocorreram erros na chamada ao BP : ' .  $data['message']);
                        \Log::channel($this->logchannel)->error($response);
                        return $response;
                    }

                }
            }else {
                $correlation_id = $data['correlation_id'];
            }

            $contrato = 0;

            try {

                //status code de resposta 
                $httpCode = $oauth2->getHttpCode();
                \Log::channel($this->logchannel)->info('HTTP code na resposta' . $httpCode ); 

                //grava pedido na BD
                $request_id = $oauth2->saveRequestToDB(  $audience, $response, $correlation_id, $time_elapsed_secs,$contrato ,  $myJSON ,$tokenid);
            
                //grava payload na BD
                $payload_id = $oauth2->savePayloadPLToDB( $request_id, $data, $contrato , $myJSON );

                //grava associacao na BD
                if ( $httpCode == '201' ) {
                            
                    \Log::channel($this->logchannel)->info('HTTP code de sucesso na resposta' ); 
                
                    // $this->payloadJson = json_decode($this->payloadJson);
                    // \Log::channel($this->logchannel)->info( print_r($this->payloadJson, true));

                    // $this->invalidaAssociacoesExistentes( );

                    \Log::channel($this->logchannel)->info('Vai efetuar a associação na base de dados bp_pl_assoc...'); 

                    //grava nova associacao
                    $result = $oauth2->saveAssociacaoToDB( $request_id, $data , $contrato , $myJSON );

                }else {
                    \Log::channel($this->logchannel)->info('Status de insucesso na resposta ? Não vai gravar associação.' ); 
                }


            }catch(\Exception $e){
                \Log::channel($this->logchannel)->error('Database data not saved');
                \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            }
           
            return $response;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['Erros Internos : ' . $e->getMessage()];
        }
    }

}