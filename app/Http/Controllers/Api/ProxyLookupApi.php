<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PlAssociacao;
use App\Models\PSPNotifications;
use App\Models\PlNotificacaoRemoved;
use App\Models\PlNotificacaoExpired;

use App\Services\BancoPortugal\BancoPortugalService;

class ProxyLookupApi extends Controller
{

    private $logchannel = 'proxylookup';
   
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function inserePL(Request $request)
    {
        $this->logchannel = 'hbp';
        
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( '________INSERT FROM HBP PL API________' . print_r($request->input(), true)  );
        $response = [];
        try {

            //verifica pedido 
            $pedido = $request->json()->all();
            //\Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $iban= $pedido['iban'];
            $nif= $pedido['nif'];
            $tipo_customer = $pedido['tipoCustomer'];
            $tipo_identifier = $pedido['tipoIdentifier'];
            $identificador= '';
            if (  $tipo_customer == "1" ) {
                $identificador= str_replace('_','+',$pedido['identificador']);
            }else {
                $identificador= $pedido['identificador'];
            }
           
            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para criar associacao...' );
            $content = ( new BancoPortugalService(  $this->logchannel  ) )->criaAssociacao( $contrato, $iban,  $identificador, $nif, $tipo_customer,$tipo_identifier ) ;
            if ( $content ) {
                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
            }else {
                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
            }
            \Log::channel($this->logchannel)->info( '****************************************************');
            //analisa response TO DO
            //agora esta true ou false
            $response = $content;

           // $response =  ['________INSERT FROM HBP PL API________' . print_r($request->all(), true) ];

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

    public function removePL(Request $request)
    {
        $this->logchannel = 'hbp';
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( '________DELETE FROM HBP PL API________' . print_r($request->all(), true));
      
        $response = [];
        try {


            //verifica pedido 
            $pedido = $request->json()->all();
            //\Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $iban= $pedido['iban'];
            $nif= $pedido['nif'];
            $tipo_customer = $pedido['tipoCustomer'];
            $tipo_identifier = $pedido['tipoIdentifier'];
            $identificador= '';
            if (  $tipo_customer == "1" ) {
                $identificador= str_replace('_','+',$pedido['identificador']);
            }else {
                $identificador= $pedido['identificador'];
            }
            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para remover associacao...' );
            $content = ( new BancoPortugalService( $this->logchannel ) )->removeAssociacao( $contrato, $iban,  $identificador, $nif, $tipo_customer,$tipo_identifier ) ;
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
                    'value' => 'Service unavailable'
                ],
            ], 503);
        }
      
        return response()->json($response, 200);
    }

    public function confirmPL(Request $request)
    {
        $this->logchannel = 'hbp';
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( '________CONFIRMATION FROM HBP PL API________' . print_r($request->all(), true));
      
        $response = [];
        try {


            //verifica pedido 
            $pedido = $request->json()->all();
            //\Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $iban= $pedido['iban'];
            $nif= $pedido['nif'];
            $tipo_customer = $pedido['tipoCustomer'];
            $tipo_identifier = $pedido['tipoIdentifier'];
            $identificador= '';
            if (  $tipo_customer == "1" ) {
                $identificador= str_replace('_','+',$pedido['identificador']);
            }else {
                $identificador= $pedido['identificador'];
            }
            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para confirmar associacao...' );
            $content = ( new BancoPortugalService( $this->logchannel ) )->confirmaAssociacao( $contrato, $iban,  $identificador, $nif, $tipo_customer,$tipo_identifier ) ;
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
                    'value' => 'Service unavailable'
                ],
            ], 503);
        }
      
        return response()->json($response, 200);
    }

    public function contactsPL(Request $request)
    {
        $this->logchannel = 'hbp';
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( '________CONTACTS FROM HBP PL API________' . print_r($request->all(), true));
      
        $response = [];
        try {


            //verifica pedido 
            $pedido = $request->json()->all();
            //\Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $phone_book= $pedido['phone_book'];

            if ( count($phone_book) == 0) {
                \Log::channel($this->logchannel)->error('Phone Book EMPTY.');
                return response()->json([
                    'message' => 'Request unsuccessful. The following errors were found.',
                    'errors' => [
                        'code' => 'E008',
                        'value' => 'Service unavailable'
                    ],
                ], 503);
            }

            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para consultar se telemoveis sao aderentes...' );
            $content = ( new BancoPortugalService( $this->logchannel ) )->consultaContatos( $contrato, $phone_book ) ;
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
                    'value' => 'Service unavailable'
                ],
            ], 503);
        }
      
        return response()->json($response, 200);
    }

    public function accountPL(Request $request)
    {
        $this->logchannel = 'hbp';
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( 'LOOKUP ACCOUNT FROM HBP PL API________' . print_r($request->all(), true));
      
        $response = [];
        try {


            //verifica pedido 
            $pedido = $request->json()->all();
            //\Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $customer_identifier= $pedido['customer_identifier'];

            if ( trim($customer_identifier) == '') {
                \Log::channel($this->logchannel)->error('Customer identifier EMPTY.');
                return response()->json([
                    'message' => 'Request unsuccessful. The following errors were found.',
                    'errors' => [
                        'code' => 'E008',
                        'value' => 'Service unavailable'
                    ],
                ], 503);
            }

            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter IBAN...' );
            $content = ( new BancoPortugalService( $this->logchannel ) )->obtemIban( $contrato, $customer_identifier ) ;
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
                    'value' => 'Service unavailable'
                ],
            ], 503);
        }
      
        return response()->json($response, 200);
    }

    public function reativatePL(Request $request)
    {
        $this->logchannel = 'hbp';
        
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( '________REATIVATE FROM HBP PL API________' . print_r($request->input(), true)  );
        $response = [];
        try {

            //verifica pedido 
            $pedido = $request->json()->all();
            \Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $iban= $pedido['iban'];
            $nif= $pedido['nif'];
            $tipo_customer = $pedido['tipoCustomer'];
            $tipo_identifier = $pedido['tipoIdentifier'];
            $identificador= '';
            if (  $tipo_customer == "1" ) {
                $identificador= str_replace('_','+',$pedido['identificador']);
            }else {
                $identificador= $pedido['identificador'];
            }
            $correlation_id_origin = $pedido['correlation_id_origin'];

            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para reativate associacao...' );
            $content = ( new BancoPortugalService(  $this->logchannel  ) )->reativarAssociacao( $contrato, $iban,  $identificador, $nif, $tipo_customer,$tipo_identifier,$correlation_id_origin ) ;
            if ( $content ) {
                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
            }else {
                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
            }
            \Log::channel($this->logchannel)->info( '****************************************************');
            //analisa response TO DO
            //agora esta true ou false
            $response = $content;

           // $response =  ['________INSERT FROM HBP PL API________' . print_r($request->all(), true) ];

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

    public function eliminatePL(Request $request)
    {
        $this->logchannel = 'hbp';
        
        \Log::channel($this->logchannel)->info( '****************************NEW REQUEST FROM HOMEBANKING************************');
        \Log::channel($this->logchannel)->info( '________REATIVATE FROM HBP PL API________' . print_r($request->input(), true)  );
        $response = [];
        try {

            //verifica pedido 
            $pedido = $request->json()->all();
            \Log::channel($this->logchannel)->info( $pedido );
            //valida pedido TO DO
            $contrato = $pedido['contrato'];
            $iban= $pedido['iban'];
            $nif= $pedido['nif'];
            $tipo_customer = $pedido['tipoCustomer'];
            $tipo_identifier = $pedido['tipoIdentifier'];
            $identificador= '';
            if (  $tipo_customer == "1" ) {
                $identificador= str_replace('_','+',$pedido['identificador']);
            }else {
                $identificador= $pedido['identificador'];
            }
            $correlation_id_origin = $pedido['correlation_id_origin'];

            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para eliminar associacao pendente...' );
            $content = ( new BancoPortugalService(  $this->logchannel  ) )->eliminarAssociacaoPendente( $contrato, $iban,  $identificador, $nif, $tipo_customer,$tipo_identifier , $correlation_id_origin) ;
            if ( $content ) {
                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
            }else {
                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
            }
            \Log::channel($this->logchannel)->info( '****************************************************');
            //analisa response TO DO
            //agora esta true ou false
            $response = $content;

           // $response =  ['________INSERT FROM HBP PL API________' . print_r($request->all(), true) ];

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
     * Operation getHealth
     *
     * /health - GET.
     *
     *
     * @return Http response
     */
    public function getHealth()
    {
        $this->logchannel = 'health_pl';
        try{
            \Log::channel($this->logchannel)->info( '________Health '.strtoupper(config('app.sigla_psp')).' PL API________' );
            return response('Health '.strtoupper(config('app.sigla_psp')).' PL API', 200);
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    'code' => 'E008',
                    'value' => 'Service unavailable'
                ],
            ], 503);
        }
    }


    /**
     * Operation removedAssociation
     *
     * /pl/notification/removed_association - POST.
     *
     *
     * @return Json response
     */
    public function removedAssociation(Request $request)
    {
        $start2 = microtime(true); 
        $this->logchannel = 'pl-removed';
        try{
           
            \Log::channel( $this->logchannel)->info( '---------------------------------------------');
            \Log::channel( $this->logchannel)->info( 'removedAssociation: ' . print_r($request->all(), true) );
       
            //token used
            $token_used = $request->bearerToken();
            //post data
            $input = $request->all();

            //path params validation
            if (!isset($input['correlation_id'])) {
                throw new \InvalidArgumentException('Missing the required parameter correlation_id when calling removedAssociation');
            }
            $correlation_id = $input['correlation_id'];
      
            if (!isset($input['correlation_id_origin'])) {
                throw new \InvalidArgumentException('Missing the required parameter $correlation_id_origin when calling removedAssociation');
            }
            $correlation_id_origin = $input['correlation_id_origin'];

            if (!isset($input['customer_identifier'])) {
                throw new \InvalidArgumentException('Missing the required parameter $customer_identifier when calling removedAssociation');
            }
            $customer_identifier = $input['customer_identifier'];

            if (!isset($input['customer_identifier_type'])) {
                throw new \InvalidArgumentException('Missing the required parameter $customer_identifier_type when calling removedAssociation');
            }
            $customer_identifier_type = $input['customer_identifier_type'];
       
            if (!isset($input['fiscal_number'])) {
                throw new \InvalidArgumentException('Missing the required parameter $fiscal_number when calling removedAssociation');
            }
            $fiscal_number = $input['fiscal_number'];
       
            if (!isset($input['customer_type'])) {
                throw new \InvalidArgumentException('Missing the required parameter $customer_type when calling removedAssociation');
            }
            $customer_type = $input['customer_type'];

            if (!isset($input['iban'])) {
                throw new \InvalidArgumentException('Missing the required parameter $iban when calling removedAssociation');
            }
            $iban = $input['iban'];

            if (!isset($input['message'])) {
                throw new \InvalidArgumentException('Missing the required parameter $message when calling removedAssociation');
            }
            $message = $input['message'];
       
            
            $response = [ 'message'=> 'Notification received successfully'];

            //grava pedido na BD
            $contrato = 0 ;
            $httpcode = 200;
            $audience = '' ;
            // switch( config('app.sigla')){
            //     case 'MAF':
            //         $audience = 'https://plqual.ccammafra.pt/api/pl/notification/removed_association' ;
            //         break;
            //     case 'TVD':
            //         $audience = 'https://plqual.ccamtv.pt/api/pl/notification/removed_association' ;
            //         break;
            //     default: 
            //         $audience = 'not defined' ;
            // }
            $audience = trim(config('app.url')) .'/api/pl/notification/removed_association' ;
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);

            $time_elapsed_secs2 = microtime(true) - $start2;

            //start timer
            \Log::channel($this->logchannel)->info('Save request to BD');
            $start = microtime(true);
            $request_id = $this->saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs2,$contrato, $httpcode , $token_used );
            //stop timer
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
            //grava payload na BD
            \Log::channel($this->logchannel)->info('Save payload to BD');
            $start = microtime(true);
            $payload_id = $this->savePayloadToDB( 1, $request_id, $input, $contrato );
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);

             //UPDATE STATUS INATIVO BD
             \Log::channel($this->logchannel)->info('Update status inativo...');
             $start = microtime(true);
             $updated = $this->updateStatusAssociacao( 0, $input );
             $time_elapsed_secs = microtime(true) - $start;
             \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);

            \Log::channel( $this->logchannel)->info( 'Notification received successfully---------------------------------------------');

            return response()->json($response, 200);
       
        }catch(\InvalidArgumentException $e){
            \Log::channel($this->logchannel)->error( 'InvalidArgumentException: ' . $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel( $this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                         'code' => 'E007',
                    'value' => 'Invalid notification'
                    ]
                   
                ],
            ], 400);

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel( $this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                          'code' => 'E006',
                    'value' => 'Service unavailable'
                    ]
                  
                ],
            ], 503);
        }
    }


    /**
     * Operation expiredAssociation
     *
     * /pl/notification/removed_association - POST.
     *
     *
     * @return Json response
     */
    public function expiredAssociation(Request $request)
    {
        $this->logchannel = 'pl-expired';
       
        try{
         
            $start2 = microtime(true); 
            \Log::channel( $this->logchannel)->info( '---------------------------------------------');
            \Log::channel($this->logchannel)->info( 'expiredAssociation: ' . print_r($request->all(), true) );
        
            //token used
            $token_used = $request->bearerToken();
            //post data
            $input = $request->all();

            //path params validation
            if (!isset($input['correlation_id'])) {
                throw new \InvalidArgumentException('Missing the required parameter correlation_id when calling expiredAssociation');
            }
            $correlation_id = $input['correlation_id'];
      
            if (!isset($input['correlation_id_origin'])) {
                throw new \InvalidArgumentException('Missing the required parameter $correlation_id_origin when calling expiredAssociation');
            }
            $correlation_id_origin = $input['correlation_id_origin'];

            if (!isset($input['customer_identifier'])) {
                throw new \InvalidArgumentException('Missing the required parameter $customer_identifier when calling expiredAssociation');
            }
            $customer_identifier = $input['customer_identifier'];

            if (!isset($input['customer_identifier_type'])) {
                throw new \InvalidArgumentException('Missing the required parameter $customer_identifier_type when calling expiredAssociation');
            }
            $customer_identifier_type = $input['customer_identifier_type'];
       
            if (!isset($input['fiscal_number'])) {
                throw new \InvalidArgumentException('Missing the required parameter $fiscal_number when calling expiredAssociation');
            }
            $fiscal_number = $input['fiscal_number'];
       
            if (!isset($input['customer_type'])) {
                throw new \InvalidArgumentException('Missing the required parameter $customer_type when calling expiredAssociation');
            }
            $customer_type = $input['customer_type'];

            if (!isset($input['iban'])) {
                throw new \InvalidArgumentException('Missing the required parameter $iban when calling expiredAssociation');
            }
            $iban = $input['iban'];

            if (!isset($input['message'])) {
                throw new \InvalidArgumentException('Missing the required parameter $message when calling expiredAssociation');
            }
            $message = $input['message'];

            $response = [ 'message'=> 'Notification received successfully'];

            //grava pedido na BD
            $contrato = 0 ;
            $httpcode = 200;
            $audience = '' ;
            // switch( config('app.sigla')){
            //     case 'MAF':
            //         $audience = 'https://plqual.ccammafra.pt/api/pl/notification/expired_association' ;
            //         break;
            //     case 'TVD':
            //         $audience = 'https://plqual.ccamtv.pt/api/pl/notification/expired_association' ;
            //         break;
            //     default: 
            //         $audience = 'not defined' ;
            // }
            $audience = trim(config('app.url')) .'/api/pl/notification/expired_association' ;
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);

            $time_elapsed_secs2 = microtime(true) - $start2;

            //start timer
            \Log::channel($this->logchannel)->info('Save request to BD');
            $start = microtime(true);
            $request_id = $this->saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs2,$contrato, $httpcode , $token_used );
            //stop timer
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);
            //grava payload na BD
            \Log::channel($this->logchannel)->info('Save payload to BD');
            $start = microtime(true);
            $payload_id = $this->savePayloadToDB( 2, $request_id, $input, $contrato );
            $time_elapsed_secs = microtime(true) - $start;
            \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);

            //UPDATE STATUS PENDENTE BD
             \Log::channel($this->logchannel)->info('Update status pendente...');
             $start = microtime(true);
             $updated = $this->updateStatusAssociacao( 2, $input );
             $time_elapsed_secs = microtime(true) - $start;
             \Log::channel($this->logchannel)->info('time_elapsed_secs: ' . $time_elapsed_secs);

            \Log::channel( $this->logchannel)->info( 'Notification received successfully---------------------------------------------');

            return response()->json($response, 200);
       
        }catch(\InvalidArgumentException $e){
            \Log::channel($this->logchannel)->error( 'InvalidArgumentException: ' . $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel( $this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                      'code' => 'E009',
                    'value' => 'Invalid notification' 
                    ]

                ],
            ], 400);

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            \Log::channel( $this->logchannel)->info( '---------------------------------------------');
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    [
                           'code' => 'E008',
                    'value' => 'Service unavailable'
                    ]
                 
                ],
            ], 503);
        }
    }

    private function saveRequestToDB($audience, $input, $response, $correlation_id, $time_elapsed_secs , $contrato, $httpcode, $token_used )
    {
        \Log::channel($this->logchannel)->info('SAVE Request and response to DB PSPNotifications'); 
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
                'http_code_response' => $httpcode 
            ];
            $requestBP = PSPNotifications::insertGetId( $pedido);
            $request_id =  $requestBP;
            \Log::channel($this->logchannel)->info('Request saved to db with id ' .  $request_id  ); 
            
            return $request_id;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Request NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
            return 0;
        }
    }

    private function savePayloadToDB($tipo, $request_id, $data ,$contrato)
    {
        //grava payload na BD
        \Log::channel($this->logchannel)->info('SAVE Payload to DB'); 
        try {
  
            \Log::channel($this->logchannel)->info(print_r($data, true)); 
  
            $payload = [
                'dt_pedido' => date('Y-m-d'),
                'request_id' => $request_id ,
                'n_netcaixa' => $contrato, 
                'user_id' => 0, 
                'correlation_id' => $data['correlation_id'],
                'correlation_id_origin' => $data['correlation_id_origin'],
                'customer_identifier' =>   $data['customer_identifier'],
                'customer_identifier_type' =>  $data['customer_identifier_type'],
                'fiscal_number' =>  $data['fiscal_number'],
                'customer_type' =>  $data['customer_type'], 
                'iban' => $data['iban'],
                'message' => $data['message'],
                'status' => 'P',
                'dt_status' => date('Y-m-d H:i:s'),
            ];

            $payloadBD = 0;
            if ( $tipo == 1 ) { 
                //removed
                $payloadBD = PlNotificacaoRemoved::insertGetId( $payload );
            }else {
                //expired
                $payloadBD = PlNotificacaoExpired::insertGetId( $payload );
            }
            $payload_id =  $payloadBD;
            \Log::channel($this->logchannel)->info('Payload saved to db with id ' . $payload_id );
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Payload NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
        }
    }

    private function updateStatusAssociacao($status, $data )
    {
        \Log::channel($this->logchannel)->info('updateStatusAssociacao para ' . $status); 
        try {

            $updated = PlAssociacao::where('correlation_id', $data['correlation_id_origin'])
            ->update( [
                    'status' => $status, 
                    'updated_at' => date('Y-m-d H:i:s'), 
                    'correlation_id_origin' => $data['correlation_id'] 
                ] 
            );

            if ( $updated  > 0 ) {
                \Log::channel($this->logchannel)->info('Status changed' );
            }else {
                \Log::channel($this->logchannel)->info('Status not updated' );
            }
            
            return $updated;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Status NOT changed'); 
            \Log::channel($this->logchannel)->error($e->getMessage());
            return 0; 
        }
    }

}
