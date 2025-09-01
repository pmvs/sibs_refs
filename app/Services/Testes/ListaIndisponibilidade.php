<?php 

namespace App\Services\Testes;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;
use DateTime;
use DateTimeZone;


class ListaIndisponibilidade extends Controller implements IndisponibilidadeInterface
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
        \Log::channel($this->logchannel)->info('__construct ListaIndisponibilidade');
    }

    public function __construct_3($logchannel, $tabNumber, $testNumber) 
    {
        $this->logchannel = $logchannel;
        $this->tabPosition =$tabPosition;
        $this->testNumber = $testNumber;

        \Log::channel($this->logchannel)->info('__construct_3 ListaIndisponibilidade');
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


    private function formatDate( $date ) 
    {
        if ( $date ) {
           
            $data = new DateTime($date); // data e hora previstas para o término da indisponibilidade
            
            $data->setTimezone(new DateTimeZone('UTC')); // definir o fuso horário para UTC
            
            $formatado = $data->format('Y-m-d\TH:i:s.v\Z'); // formatar a data e hora no formato ISO 8601 (UTC)
            
            return $formatado; // saída: "2023-03-22T14:30:00.000Z"
        }

        return '';
    
    }


    public function efetuaPedido($fields )
    {
        try {

            \Log::channel($this->logchannel)->info('efetuaPedido em ListaIndisponibilidade');
            \Log::channel($this->logchannel)->info('fillData em ListaIndisponibilidade...');
        
            $myJSON = $this->fillData($fields);
            if ( ! $myJSON ) {
                \Log::channel($this->logchannel)->warning('fillData em ListaIndisponibilidade...NOT OK');
                return 'Erro: Pedido de execução de teste inválido -> fillData em ListaIndisponibilidade';
            }
            \Log::channel($this->logchannel)->info('fillData em ListaIndisponibilidade...OK');

            \Log::channel($this->logchannel)->info('executaTeste em ListaIndisponibilidade');
            \Log::channel($this->logchannel)->info($myJSON);
            $response = $this->executaTeste($fields, $myJSON);
            if ( ! $response ) {
                \Log::channel($this->logchannel)->warning('executaTeste em ListaIndisponibilidade...NOT OK');
                return 'Erro: Pedido de execução de teste inválido -> executaTeste em ListaIndisponibilidade';
            }
            \Log::channel($this->logchannel)->info('executaTeste em ListaIndisponibilidade...OK');

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

            \Log::channel($this->logchannel)->info('fillData ' . $this->testNumber . ' em ListaIndisponibilidade...' . print_r( $fields, true));
        
            //create object
            $myObj  = new \stdClass();

            switch ( $this->testNumber ){
                case 23: 
                    $myObj  =  $this->createTeste1( $fields );
                    break;
                case 24: 
                    $myObj  =  $this->createTeste1( $fields );
                    break;
                case 25: 
                    $myObj  =  $this->createTeste2( $fields );
                    break;
                case 26: 
                    $myObj  =  $this->createTeste1( $fields );
                    break;
                 default:
                    \Log::channel($this->logchannel)->info('fillData ' . $this->testNumber . ' em ListaIndisponibilidade...NOT OK');
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

        \Log::channel($this->logchannel)->info('createTeste1 em ListaIndisponibilidade...');
        

        try {
            //create object
            $myObj  = new \stdClass();

            $myObj->{'psp_code'} = $fields['pspCode'];
          //  $myObj->{'description'} = $fields['description'];
            if (array_key_exists('startDate', $fields)) {
                if ( ! empty($fields['startDate'])) {
                    $myObj->{'start_date'} = $this->formatDate($fields['startDate']);
                }else{
                    \Log::channel($this->logchannel )->error('startDate doesnt exist. is mandatory. return null');
                    return null;
                }
            }else {
                \Log::channel($this->logchannel )->error('startDate doesnt exist. is mandatory. return null');
                return null;
            }
            if (array_key_exists('endDate', $fields)) {
                if ( ! empty($fields['endDate'])) {
                    $myObj->{'end_date'} = $this->formatDate($fields['endDate']);
                }else{
                    \Log::channel($this->logchannel )->error('endDate doesnt exist. is mandatory. return null');
                    return null;
                }
            }else {
                \Log::channel($this->logchannel )->error('endDate doesnt exist. is mandatory. return null');
                return null;
            }

            if (array_key_exists('is_in_progress', $fields)) {
                if ( $fields['is_in_progress'] == 'false' ){
                    Log::channel($this->logchannel )->error('is_in_progress: FALSE');
                   // $myObj->{'is_in_progress'} =  (bool) $fields['is_in_progress'];
                    $myObj->{'is_in_progress'} = (bool) false;
                }else {
                    Log::channel($this->logchannel )->error('is_in_progress: TRUE');
                    $myObj->{'is_in_progress'} = (bool) true;// Returns true
                }        
                
            }else {
                \Log::channel($this->logchannel )->error('is_in_progress doesnt exist. is mandatory. return null');
                return null;
            }

            \Log::channel($this->logchannel)->info('createTeste1 em ListaIndisponibilidade...OK');
        
            //\Log::channel($this->logchannel)->info($myObj);
        

            return $myObj; 

         }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return null;
        }
       
    }


    private function createTeste2( $fields )
    {

        \Log::channel($this->logchannel)->info('createTeste2 em ListaIndisponibilidade...');
        

        try {
            //create object
            $myObj  = new \stdClass();

            $myObj->{'psp_code'} = $fields['pspCode'];
          //  $myObj->{'description'} = $fields['description'];
            if (array_key_exists('startDate', $fields)) {
                if ( ! empty($fields['startDate'])) {
                    $myObj->{'start_date'} = $this->formatDate($fields['startDate']);
                }else{
                    \Log::channel($this->logchannel )->error('startDate doesnt exist. is mandatory. return null');
                    return null;
                }
            }else {
                \Log::channel($this->logchannel )->error('startDate doesnt exist. is mandatory. return null');
                return null;
            }
            if (array_key_exists('endDate', $fields)) {
                if ( ! empty($fields['endDate'])) {
                    $myObj->{'end_date'} = $this->formatDate($fields['endDate']);
                }else{
                    \Log::channel($this->logchannel )->error('endDate doesnt exist. is mandatory. return null');
                    return null;
                }
            }else {
                \Log::channel($this->logchannel )->error('endDate doesnt exist. is mandatory. return null');
                return null;
            }

            \Log::channel($this->logchannel)->info('createTeste2 em ListaIndisponibilidade...OK');
        
            //\Log::channel($this->logchannel)->info($myObj);
        

            return $myObj; 

         }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return null;
        }
       
    }

    private function executaTeste($fields, $myJSON )
    {
        try{

            \Log::channel($this->logchannel)->info('ListaIndisponibilidade : executa teste ');
           // $this->token_valido = config('app.access_token_valido');
            $this->token_valido = false;
            $resource = '';
            $resource = config('enums.apibp_dev.resources_plcp.COPB');
            if ( config('app.env') == 'prod' ) {
                $resource = config('enums.apibp.resources_plcp.COPB');
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
            $audience = config('enums.apibp_dev.endpoints_plcp.INDISPONIBILIDADE_LIST');
            if ( config('app.env') == 'prod' ) {
                $audience = config('enums.apibp.endpoints_plcp.INDISPONIBILIDADE_LIST');
            }
            \Log::channel($this->logchannel)->info(  'Audience ...' . $audience);
            if (  $audience == '' ) {
                \Log::channel($this->logchannel)->warning('Não foi possivel obter o url de destino');
                return ['Erros Internos : Não foi possivel obter o url de destino'];
            }

        
            //make a culr call
            $start = microtime(true);
            $response = $oauth2->callUsingCURL_3( $audience , $myJSON, $headers);
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs . 's');
        
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

            //grava pedido na BD
            $request_id = $oauth2->saveRequestToDB(  $audience, $response, $correlation_id, $time_elapsed_secs,$contrato ,  $myJSON ,$tokenid);
           
            //grava payload na BD
           //  $payload_id = $oauth2->savePayloadToDB( $request_id, $data, $contrato );

        
            return $response;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

}