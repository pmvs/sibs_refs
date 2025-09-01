<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BancoPortugal\ProxyLookup\Cop;
use App\Models\PSPCOPRequest;
use App\Models\CopPayload;

use App\Jobs\SaveRequestCOPS;
use App\Jobs\SaveRequestCOPB;

use App\Services\BancoPortugal\BancoPortugalService;

class CoPApi extends Controller
{

    private $logchannel = 'cop';
    private $withJobs = false;
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    
    /**
     * Operation getNomePrimeiroTitular
     *
     * /getNomePrimeiroTitular/ - POST.
     *
     *
     * @return Http response
     */
    public function getNomePrimeiroTitular(Request $request)
    {
        $this->logchannel = 'hbp';
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( '________getNomePrimeiroTitular HBP COP API________' . print_r($request->input(), true)  );
        $response = [];
        try {

            //verifica pedido 
            $pedido = $request->json()->all();
            \Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $iban= $pedido['iban'];
        
            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter o Nome do Primeiro Titular...' );
            $content = ( new BancoPortugalService(  $this->logchannel  ) )->getNomePrimeiroTitular( $contrato, $iban ) ;
            if ( $content ) {
                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
            }else {
                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
            }
            \Log::channel($this->logchannel)->info( '****************************************************');
            //analisa response TO DO
            //agora esta true ou false
            $response = $content;

        }catch(\Exception $e) {
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    'code' => 'E008',
                    'value' => 'Service unavailable - ' . $e->getMessage()
                ],
            ], 503);
        }
      
        return response()->json($response, 200);
       
   
    }

    public function getCopB(Request $request)
    {
        $this->logchannel = 'hbp';
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( 'getCopB HBP COP API________' . print_r($request->input(), true)  );
        $response = [];
        try {

            //verifica pedido 
            $pedido = $request->json()->all();
            \Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $copb= $pedido['dados']['copb'];
            $psp_destination= $pedido['dados']['psp_code_destination'];
        
            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter COPB...' );
            $content = ( new BancoPortugalService(  $this->logchannel  ) )->getCopB( $contrato, $copb , $psp_destination) ;
            if ( $content ) {
                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
            }else {
                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
            }
            \Log::channel($this->logchannel)->info( '****************************************************');
            //analisa response TO DO
            //agora esta true ou false
            $response = $content;

        }catch(\Exception $e) {
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    'code' => 'E008',
                    'value' => 'Service unavailable - ' . $e->getMessage()
                ],
            ], 503);
        }
      
        return response()->json($response, 200);
       
   
    }

    /**
     * Operation postCopb
     *
     * /copb/ - POST.
     *
     *
     * @return Http response
     */
    public function postCopb(Request $request)
    {
        $start_main = microtime(true);
        $this->logchannel = 'copb';
        //\Log::channel($this->logchannel)->info( '---------------------------------------------');
        $correlation_id_general = '';
        $contrato = 0 ;
        $httpcode = 200;
        $audience = '' ;
        try{
            \Log::channel($this->logchannel)->info( 'CoPApi....postCopb : ' . print_r($request->all(), true) );
            \Log::channel($this->logchannel)->info( 'HEADERS : ' . print_r( $request->header(), true));


            if ( ! $request->isJson() ) {
                throw new \InvalidArgumentException('Request is not JSON');
            }

            //post data
            $input = $request->input();

            //token used
            $token_used = $request->bearerToken();
            
            $input = $request->all();

            //path params validation
            if (!isset($input['correlation_id'])) {
                throw new \InvalidArgumentException('Missing the required parameter correlation_id when calling postCopb');
            }
            $correlation_id = $input['correlation_id'];
            $correlation_id_general =  $correlation_id ;
    
            if (!isset($input['timestamp'])) {
                throw new \InvalidArgumentException('Missing the required parameter $timestamp when calling postCopb');
            }
            $timestamp = $input['timestamp'];
    
            if (!isset($input['items'])) {
                throw new \InvalidArgumentException('Missing the required parameter $items when calling postCopb');
            }
            $items = $input['items'];
            //verifica estrutura do items
            foreach( $items as $item ) {
                if (!array_key_exists('fiscal_number',$item)) {
                    throw new \InvalidArgumentException('Missing the required parameter $iban when calling postCopb');
                }
                if (!isset($item['type'])) {
                    throw new \InvalidArgumentException('Missing the required parameter $type when calling postCopb');
                }
                if (!array_key_exists('iban',$item)) {
                    throw new \InvalidArgumentException('Missing the required parameter $iban when calling postCopb');
                }
            }
             
            $audience = trim(config('app.url')) .'/api/conp/copb' ;
            //\Log::channel($this->logchannel)->info('Audience: ' . $audience);
 
            //start timer
            //\Log::channel($this->logchannel)->info('Save request to BD');

            $header = $request->header();
            $psporigincode = '';
            if (isset($header['psporigincode'])) {
                try{
                    $psporigincode = $header['psporigincode'][0];
                }catch(\Exception $e){
                    \Log::channel($this->logchannel)->error( $e->getMessage() );
                    \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
                }
            }
            \Log::channel($this->logchannel)->info('PSP Origin: ' . $psporigincode );


            $response = [
               'items' => [],
               'message' => 'Request successful',
            ];

            $start_one = microtime(true);

            //avalia a existencia true / false do par NIF/NIPC IBAN
            foreach( $items as $item ) {

                $fiscal_number = trim($item['fiscal_number']);
                $type = $item['type'];
                $iban = trim($item['iban']);

                //verifica na BD a existencia do par
                \Log::channel($this->logchannel)->info('Verifica exitencia do par na BD');
                \Log::channel($this->logchannel)->info('Fiscal Number : ' . $fiscal_number );
                \Log::channel($this->logchannel)->info('Iban : ' . $iban);
                    //start timer
                $start = microtime(true);

                if ( trim($fiscal_number) == '' || trim($iban) == '' ) {
                   
                    \Log::channel($this->logchannel)->info('Iban ou Fiscal Number empty! ');
                    $result = false;
                     //stop timer
                     $time_elapsed_secs = microtime(true) - $start;
                     \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . ($time_elapsed_secs * 1000));
                 
                }else {

            
                    //fetch information
                    $cop = Cop::where('iban', $iban)
                        ->where('nif', $fiscal_number)
                        ->first();
                    //stop timer
                    $time_elapsed_secs = microtime(true) - $start;
                    \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
                
                    $result = false;
                    if ( $cop ) {
                        $result = true;
                        \Log::channel($this->logchannel)->info('Foi encontrada a associação do par');
                    }else {
                        $result = false;
                        \Log::channel($this->logchannel)->info('NÃO foi encontrada a associação do par');
                    }

                }
              
                $itemFound = [
                    'fiscal_number' => $fiscal_number,
                    'type' => $type ,
                    'iban' => $iban,
                    'result' => $result,
                ];

                //add to array
                array_push($response['items'],  $itemFound );
            }

            if ( $this->withJobs ) {
                //grava pedido na BD
                $info = [
                    'input' => $input,
                    'audience' => $audience,
                    'response' => $response,
                    'correlation_id' => $correlation_id,
                    'time_elapsed_secs' => $time_elapsed_secs,
                    'httpcode' => $httpcode,
                    'token_used' => $token_used,
                    'psporigincode' => $psporigincode,
                    'contrato' => $contrato,
                    'user_id' => $userid,
                    'timestamp' => $timestamp,
                ];
                //\Log::channel($this->logchannel)->info('dispatch...SaveRequestCOPB');
                SaveRequestCOPB::dispatch($info);
                //\Log::channel($this->logchannel)->info('dispatched...SaveRequestCOPB');
            }else {
                $time_elapsed_secs = microtime(true) - $start_main;
                //\Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
                $request_id = $this->saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs,$contrato, $httpcode , $token_used, $psporigincode );
            }
          
            // //start timer
            // $start = microtime(true);
            // $request_id = $this->saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs,$contrato, $httpcode , $token_used, $psporigincode );
            // //stop timer
            // $time_elapsed_secs = microtime(true) - $start;
            // // \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
            // // //grava payload na BD
            // // $payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );


            \Log::channel($this->logchannel)->info( 'Vai devolver :' .print_r( $response, true) );
            // $time_elapsed_secs_one = microtime(true) - $start_one;
            //\Log::channel($this->logchannel)->info('time_elapsed_secs total: ' . $time_elapsed_secs_one);
             
            $time_elapsed_secs = microtime(true) - $start_main;
            \Log::channel($this->logchannel)->info('time_elapsed_msecs: ' . ($time_elapsed_secs * 1000));
        
            //\Log::channel($this->logchannel)->info( '---------------------------------------------');

            return response()->json($response, 200);


        }catch(\InvalidArgumentException $e){
            \Log::channel($this->logchannel)->error( 'InvalidArgumentException: ' . $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel($this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'success' => false , 
                'correlation_id' => $correlation_id_general , 
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                           'code' => 'E005',
                    'value' => 'Invalid request'
                    ]
                 
                ]
            ], 400);

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel($this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'success' => false , 
                'correlation_id' => $correlation_id_general , 
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                                  'code' => 'E004',
                    'value' => 'Service unavailable'
                    ]
          
                ]
            ], 503);
        }

    }
    /**
     * Operation postCops
     *
     * /cops/ - POST.
     *
     *
     * @return Http response
     */
    public function postCops(Request $request)
    {
        $start2 = microtime(true);
        $this->logchannel = 'cops';
        $correlation_id_general = '';
        $contrato = 0 ;
        $userid = 0;
        $httpcode = 200;
        $audience = '' ;
    
        try{
            \Log::channel($this->logchannel)->info( '---------------------------------------------');
            \Log::channel($this->logchannel)->info( 'CoPApi....postCops : ' . print_r($request->all(), true) );
            \Log::channel($this->logchannel)->info( 'HEADERS : ' . print_r( $request->header(), true));
            //sleep(120);

            if ( ! $request->isJson() ) {
                throw new \InvalidArgumentException('Request is not JSON');
            }

            //audience
            $audience = trim(config('app.url')) .'/api/conp/cops' ;

            //post data
            $input = $request->input();

            //token used
            $token_used = $request->bearerToken();

            //psp code origin from header
            $header = $request->header();
            $psporigincode = '';
            if (isset($header['psporigincode'])) {
                try{
                    $psporigincode = $header['psporigincode'][0];
                }catch(\Exception $e){
                    \Log::channel($this->logchannel)->error( $e->getMessage() );
                    \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
                }
            }
            \Log::channel($this->logchannel)->info('PSP Origin: ' . $psporigincode );

            //not path params validation
            if (!isset($input['correlation_id'])) {
                throw new \InvalidArgumentException('Missing the required parameter correlation_id when calling postCops');
            }
            $correlation_id = $input['correlation_id'];
            $correlation_id_general =  $correlation_id ;
    
            if (!isset($input['timestamp'])) {
                throw new \InvalidArgumentException('Missing the required parameter $timestamp when calling postCops');
            }
            $timestamp = $input['timestamp'];
    
            if (!isset($input['iban'])) {
                throw new \InvalidArgumentException('Missing the required parameter $iban when calling postCops');
            }
            $iban = htmlspecialchars( filter_var( strip_tags ($input['iban'] ) , FILTER_SANITIZE_STRING ), ENT_QUOTES );

            //valida Iban 
            if ( ! $this->isIbanValido($iban) ) {
                $response = [
                    'account_holder' => '',  
                    'commercial_name' => '' , 
                    'message' => 'IBAN not found'
                ];
                \Log::channel($this->logchannel)->warning( 'IBAN invalido');
                \Log::channel($this->logchannel)->warning( 'Vai devolver :' .print_r( $response, true) );
                
                return response()->json( $response , 200);
            }

            //\Log::channel($this->logchannel)->info('Verifica exitencia do IBAN na BD');
            //fetch information
            $cop = Cop::where('iban', $iban)
                ->where('n_titular', 1)
                ->first();
            //stop timer
            $time_elapsed_secs = microtime(true) - $start;
            //\Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);

            if ( ! $cop ) {
                $response = [
                    'account_holder' => '',  
                    'commercial_name' => '' , 
                    'message' => 'IBAN not found'
                ];

                //\Log::channel($this->logchannel)->info('PSP Origin: ' . $psporigincode );
                // $start = microtime(true);
                $request_id = $this->saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs,$contrato, $httpcode , $token_used, $psporigincode );
                //stop timer
                //$time_elapsed_secs = microtime(true) - $start;
                //\Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
                //grava payload na BD
                //$payload_id = $this->savePayloadToDB( $request_id, $data, $contrato );
                // $time_elapsed_secs2 = microtime(true) - $start2;
                // \Log::channel($this->logchannel)->info('Total time_elapsed_secs: ' . $time_elapsed_secs2);
                //\Log::channel($this->logchannel)->info( '---------------------------------------------');
            
                \Log::channel($this->logchannel)->info( 'Vai devolver :' .print_r( $response, true) );
                
                return response()->json([
                    'account_holder' => '',  
                    'commercial_name' => '' , 
                    'message' => 'IBAN not found'
                    ], 200);
            }

            //so preencher se for empresa
            $commercial_name = '';
            if ( $cop->tp_entidade == 'E' ) {
                $commercial_name = trim($cop->nome);
            }

            $response = [
                'account_holder' => trim($cop->nome) ,  
                'commercial_name' => $commercial_name , 
                'message' => 'Request successful'
            ];

            
            if ( $this->withJobs ) { 
                //grava pedido na BD
                $info = [
                    'input' => $input,
                    'audience' => $audience,
                    'response' => $response,
                    'correlation_id' => $correlation_id,
                    'time_elapsed_secs' => $time_elapsed_secs,
                    'httpcode' => $httpcode,
                    'token_used' => $token_used,
                    'psporigincode' => $psporigincode,
                    'contrato' => $contrato,
                    'user_id' => $userid,
                    'iban' => $iban,
                    'timestamp' => $timestamp,
                ];
                //\Log::channel($this->logchannel)->info('dispatch...SaveRequestCOPS');
                SaveRequestCOPS::dispatch($info);
                //\Log::channel($this->logchannel)->info('dispatched...SaveRequestCOPS');

            }else {
                //\Log::channel($this->logchannel)->info('Save request to BD');
                //start timer
                //$start = microtime(true);
                $request_id = $this->saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs,$contrato, $httpcode , $token_used, $psporigincode );
                //stop timer
                //$time_elapsed_secs = microtime(true) - $start;
                //\Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
                
                //grava payload na BD
                //\Log::channel($this->logchannel)->info('Save payload to BD');
                //start timer
                //$start = microtime(true);
                $payload_id = $this->savePayloadToDB( $request_id, $iban, $timestamp,  $response, $correlation_id, $contrato, $psporigincode);
                //stop timer
                //$time_elapsed_secs = microtime(true) - $start;
                //\Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
            }
           

         
            \Log::channel($this->logchannel)->info( 'Vai devolver :' .print_r( $response, true) );

            $time_elapsed_secs3 = microtime(true) - $start2;
            \Log::channel($this->logchannel)->info('Total time_elapsed_msecs: ' . ($time_elapsed_secs3 * 1000));
            //\Log::channel($this->logchannel)->info('DB time_elapsed_secs: ' . ($time_elapsed_secs3 - $time_elapsed_secs2));

            
            return response()->json($response, 200);

        }catch(\InvalidArgumentException $e){
            \Log::channel($this->logchannel)->error( 'InvalidArgumentException: ' . $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel($this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                    'code' => 'E003',
                    'value' => 'Invalid request'
                    ]
                ]
            ], 400);

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(  'Exception: ' .  $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel($this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                            'code' => 'E001',
                    'value' => 'Service unavailable'
                    ]
                
                ],
            ], 503);
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
    public function getHealth(Request $request)
    {
        $this->logchannel = 'health_cop';
        try{
            \Log::channel($this->logchannel)->info( '___________Health '.strtoupper(config('app.sigla_psp')).' COP API______________' );
            return response('Health '.strtoupper(config('app.sigla_psp')).' COP API', 200);
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            return response('', 400);
        }
    }

    private function saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs , $contrato, $httpcode, $token_used , $psporigincode)
    {
        //\Log::channel($this->logchannel)->info('SAVE Request and response to DB PSPCOPRequest'); 
        try {
            $pedido = [
                'dt_pedido' => date('Y-m-d'),
                'token_used' => $token_used , 
                'payload' => json_encode($input, JSON_UNESCAPED_SLASHES), 
                'response' => json_encode($response, JSON_UNESCAPED_SLASHES),
                'audience' => $audience,
                'correlation_id' => $correlation_id ,
                'n_netcaixa' => $contrato,
                'user_id' => 0, 
                'timeelapsed' => $time_elapsed_secs,
                'http_code_response' => $httpcode,
                'psp_origin_code' => $psporigincode,
            ];
            $requestBP = PSPCOPRequest::insertGetId( $pedido);
            $request_id =  $requestBP;
           // \Log::channel($this->logchannel)->info('Request saved to db with id ' .  $request_id  ); 
            
            return $request_id;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Request NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
            return 0;
        }
    }

    private function savePayloadToDB($request_id,  $iban , $timestamp, $response, $correlation_id,  $contrato, $psporigincode)
    {
        //grava payload na BD
        //\Log::channel($this->logchannel)->info('SAVE Payload to DB'); 
        try {
           // $this->payloadJson = json_decode($this->payloadJson);
            $banco = $this->getCodigoBanco();

            $payload = [
                'dt_pedido' => date('Y-m-d'),
                'request_id' => $request_id ,
                'n_netcaixa' => $contrato, 
                'user_id' => 0, 
                'psp_code' => $banco, 
                'psp_code_destination' => $psporigincode,  
                'iban' => $iban, 
                'account_holder' => $response['account_holder'], 
                'commercial_name' => $response['commercial_name'], 
                'correlation_id_origin' => $correlation_id, 
                'timestamp' => $timestamp,
                'success' => 1,
                'correlation_id' => $correlation_id,
                'message' => $response['message'], 
                'errors_codes' => '',
                'errors_values' => '',
                'http_code' => 200 
            ];
            $payloadBD = CopPayload::insertGetId( $payload );
            $payload_id =  $payloadBD;
            //\Log::channel($this->logchannel)->info('Payload saved to db with id ' . $payload_id );
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Payload NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }

    private function isIbanValido( $iban )
    {
        try{
            //\Log::channel($this->logchannel)->info( 'is Iban Valido ?' . $iban );
           
            if ( strlen($iban) != 25 ) { 
                \Log::channel($this->logchannel)->warning( 'Iban inválido. Nº de carateres diferente de 25' );
                return false;
            }

            $startString = 'PT50';
            $len = strlen( $startString );
            $comeca = ( substr($iban , 0 , $len ) ==  $startString );
            if ( ! $comeca ) {
                \Log::channel($this->logchannel)->warning( 'Iban inválido. Não começa com PT50' );
                return false;
            }

            //\Log::channel($this->logchannel)->info( 'Iban válido para avançar com o pedido.');

            return true;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            return false;
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
