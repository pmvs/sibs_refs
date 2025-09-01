<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Services\MessageService\MessageService;
use App\Services\MessageService\SendMessageInterface;
use App\Services\MessageService\SendMessageSMS;
use App\Services\MessageService\SendMessageEmail;

use App\Jobs\SendEmail;
use App\Jobs\SendSMS;
use App\Jobs\ChargeRequestCOPS;
use App\Jobs\ChargeRequestCOPB;
use App\Jobs\ChargeRequestCOPB0010;
use App\Jobs\ChargeRequestPlAccount;
use App\Jobs\ChargeRequestPlContact;

use App\Models\BBalcao;

use Mail;

use App\Services\MessageService\MessageGateway;
use App\Services\TestService;
use App\Services\TestServiceV2;
use App\Services\BancoPortugal\BancoPortugalService;

class TestController extends Controller
{

    private $dt_inicio_gba = '';
    private $dt_resposta_gba = '';
    private $dt_inicio_operacao = '';
    private $dt_resposta_operacao = '';

    private $sigla = '';
    private $canal = '';
    private $tiposms = '';
    private $info = [
        'sigla' => '',
        'canal' => '',
        'tiposms' => '',
        'entidade' => 0,
        'referencia' => '',
        'valor' => 0,
        'iban' => '',
        'contrato' => 0
    ];
    private $logchannel = 'testes';
    private $arraytestes = ['sms', 'email', 'msg', 'hash', 'sibs'];
    private $arrayMensagensSIBS = [ 
        'H008', 'H009', 
        'H201', 'H202', 
        'H310', 'H313', 
        'H472', 'H473', 'H474', 
        'H655', 'H656', 'H657',
    ];
    private $arrayTiposMensagem = [];
    private $arrayTiposMensagem2 = [
        'LOGIN',
        'PASSENCCON' ,
        'PASSENCMOV',
        'PSSU' ,
        'PAG_ESTADO',
        'PAG_SERV',
        'PAG_TSU',
        'TRF',
        'CARREGAMENTO',
        'CONSENT_OTP',
        'PAYMENT_OTP' ,
        'BULKPAYMENT_OTP',
        'BULKPAYMENT_TSU' ,
        'NOTIFICA_TRANSFERENCIA',
        'ENVIO_PINS',
        'OTPC2B',
        'REPPIN',
        'NOTIFY_MULTIAUTH',
        'EXCEPTION',
        'LOGIN_MULTIAUTH',
        'NOTIFY_RESET_PINS',
        'TESTE',
    ];
    private $isConnProduction = false;

    private $testNamesIndisponibilidades = [
        1 => 'Description 1',
        2 => 'Description 2',
        3 => 'Description 3',
        4 => 'Description 4',
        5 => 'Description 5',
        6 => 'Description 6',
        7 => 'Description 7',
        8 => 'Description 8',
        9 => 'Description 9',
        10 => 'Description 10',
        11 => 'Description 11',
        12 => 'Description 12',
        13 => 'Description 13',
        14 => 'Description 14',
        15 => 'Description 15',
        16 => 'Description 16',
        17 => 'Description 17',
        18 => 'Description 18',
        19 => 'Description 19',
        20 => 'Description 20',
        21 => 'Description 21',
        22 => 'Description 22',
        23 => 'Description 23',
        24 => 'Description 24',
    ];


    private $testNamesPL = [
        1 => 'Description 1',
        2 => 'Description 2',
        3 => 'Description 3',
        4 => 'Description 4',
        5 => 'Description 5',
        6 => 'Description 6',
        7 => 'Description 7',
        8 => 'Description 8',
        9 => 'Description 9',
        10 => 'Description 10',
        11 => 'Description 11',
        12 => 'Description 12',
        13 => 'Description 13',
        14 => 'Description 14',
        15 => 'Description 15',
        16 => 'Description 16',
        17 => 'Description 17',
        18 => 'Description 18',
        19 => 'Description 19',
    ];

    /*
    |--------------------------------------------------------------------------
    | CONSTRUTOR
    |--------------------------------------------------------------------------
    */ 
    public function __construct() 
    {
        session(['message' => '']);

        $this->sigla = env('APP_SIGLA');
        $this->canal = 'SMS';
        $this->tiposms = 'LOGIN';
        $this->logchannel = 'testes';
        $this->isConnProduction = config('app.connection_prod');
        \Log::channel($this->logchannel )->info( '');
        \Log::channel($this->logchannel )->info( '--------------------------');
        \Log::channel($this->logchannel )->info( '--------------------------');
        \Log::channel($this->logchannel )->info( '__construct TestController');
        \Log::channel($this->logchannel)->info('Connection PROD ? ' . $this->isConnProduction );
    }

    /*
    |--------------------------------------------------------------------------
    | OTHERS
    |--------------------------------------------------------------------------
    */ 
    public function enqueue(Request $request)
    {

    
        $details = [
            'email' => 'pmvsant@gmail.com', 
            'title' => 'Os seus dados de acesso ao ' . env('SERVICE') . ' Multi-Autenticação',
            'logotipo' => '',
            'assunto' => 'Pedido de acesso ao ' . env('SERVICE') . ' Multi-Autenticação' ,
            'nome' => 'PEDRO SANTOS',
            'servico' => env('SERVICE') ,
            'site' => env('HOMEBANKING_OFICIAL'),
            'sender' => trim( config('app.mail_from_name') ),
            'linha' => __('messages.linhaapoio'),
            'horario' => 'Dias úteis ' .  __('messages.linhaapoio.horario'),
        ];

        switch(env('APP_SIGLA')){
            case 'MAF':
                $details['logotipo'] = 'CCAM_MAFRA_vectores_1.jpg';
                break;
            case 'TVD':
                $details['logotipo'] = 'CCAMTVonline.jpg';
                break;
            case 'CHM':
                $details['logotipo']= 'logo-ccachm.png';
                break;
            case 'BOM':
                $details['logotipo'] = 'logo_bom1.jpg';
                break;
            default:
                $details['logotipo'] = '';
                break;
        }

        SendEmail::dispatch($details);

        return view('multiauth.register');

    }

    /*
    |--------------------------------------------------------------------------
    | TESTES VERSAO 2 DO PL
    |--------------------------------------------------------------------------
    */  
    public function teste_v2(Request $request)
    {
        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( 'TESTES 2---VIEW---');
        try {

            //test service init
            $testService = new TestServiceV2();
            //get array of names
            $namesPL = $testService->getTestNamesPL();
            $namesIndisponibilidades = $testService->getTestNamesIndisponibilidades();
            //psps code from env
            $pspcode = config('app.pspcode');
            //iban from env
            $iban = config('app.iban_v2');
            //nif from env
            $nif = config('app.nif_v2');
            //telemovel from env
            $telemovel = config('app.telemovel_v2');
            //nipc code from env
            $nipc = config('app.nipc_v2');
            //nif 2 from env
            $nif2 = config('app.nif2_v2');
            //nif nao conforme from env
            $nifnaoconforme = config('app.nif_nao_conforme_v2'); 
            //nif invalido from env
            $nifinvalido = config('app.nif_invalido_v2'); 
            //nif tipo 45 from env
            $niftipo45 = config('app.nif_tipo45_v2'); 
            //lista de ibans from env
            $listaibans = config('app.lista_ibans_v2'); 
            //iban tipo 45 from env
            $ibantipo45 = config('app.iban_tipo45_v2'); 
            //phone book from env
            $phone_book= config('app.phone_book_v2'); 
            //iban 2 from env
            $iban2= config('app.iban2_v2'); 

            //phone book from env
            $ibanempresa1= config('app.iban_empresa_v2'); 
            //phone book from env
            $ibanempresa2= config('app.iban_empresa2_v2'); 


            return view('testes.main', compact( 'pspcode', 'phone_book','iban','iban2','ibanempresa1','ibanempresa2','listaibans','ibantipo45','nif','nif2','nifnaoconforme','nifinvalido','niftipo45','nipc','telemovel','namesPL', 'namesIndisponibilidades' ))->withInput();
      
        }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }
    public function execTests(Request $request)
    {

        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( 'Executing tests...');
        try {

            //input data from view post
            $testNumber = $request->input('testNumber');
            $tabPosition = $request->input('tabPosition');
            $data = $request->all();
            \Log::channel($this->logchannel )->info( 'Tab Position :' . $tabPosition );
            \Log::channel($this->logchannel )->info( 'Test Number..' . $testNumber );
            \Log::channel($this->logchannel )->info( 'Data ..' . print_r( $data, true) );

            // You can add your logic here to process the test
            if ($testNumber < 1 || $tabPosition < 1) {
                \Log::channel($this->logchannel )->warning( 'Test Number failed. Tabposition or testNumber incorrect'  );
                return response()->json(['error' => 'Invalid request #1 : Tabposition or testNumber incorrect'], 400);
            }

            //test service init
            $testService = new TestServiceV2($this->logchannel, $tabPosition , $testNumber );

            //descricao do caso de teste
            $descricaoTeste = $testService->getDescricaoTeste();
            \Log::channel($this->logchannel )->info( 'Test description :' . $descricaoTeste );

            // You can add your logic here to process the test
            \Log::channel($this->logchannel )->info( 'Executing test number...' . $testNumber );
            $response = $testService->executeTest( $data );
            \Log::channel($this->logchannel )->info( $response );
            \Log::channel($this->logchannel )->info( 'Results from test :' . print_r($response,true) );

            //$response2 = 'Test executed' . $testNumber . ' in tab ' . $tabPosition . ' was successful';
            
            return response()->json($response, 200);

            return response()->json([
                'status' => 200,
                'mensagem' => 'Operação bem-sucedida',
                'dados' => $response
            ]);
            //return response($response);

          

        }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid request #2'], 400);
        }

    }

    private function testPlv2($testNumber)
    {

    }

    private function testIndisponibilidades($testNumber)
    {
        
    }

    /*
    |--------------------------------------------------------------------------
    | TESTES SERVICE CALL
    |--------------------------------------------------------------------------
    */  
    public function test(Request $request)
    {
        return response()->json(['message' => 'Teste de serviço']);
    }


    public function testes(Request $request)
    {
        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( 'TESTES---VIEW---');
        try {
            $result = '';
            return view('dashboard', compact('result'))->withInput();
       }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }

    public function testespost(Request $request)
    {
        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( 'TESTES---POST---');
        \Log::channel($this->logchannel )->info( print_r($request->all(), true));
        try {

    
            $sessionid = 'not set';
            $contrato = 0;
            $customer_identifier = $request->identificador1;
            $iban = $request->iban;
            $result = [];
            $tipo_identifier = $request->tipoidentificador1[0];
            $tipo_customer = 0;
            $ibancop = $request->ibancop;

        

            if ( $ibancop != '' ) { 
                $nif = 'NOTSET';
                \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter nome COp do ...' . $ibancop);
                
                $content = ( new BancoPortugalService( $this->logchannel ) )->getNomePrimeiroTitular($contrato, $ibancop);
                if ( $content ) {
                    \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
                }else {
                    \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
                }
                \Log::channel($this->logchannel)->info( '****************************************************');
                //analisa response TO DO
                //agora esta true ou false
                $result = $content;

              //  return redirect()->back()->with('result')->withInput($request->input());

            }else {
                
                if ( $customer_identifier == '' ) {

                        //reativar associacoes pendentes 
                        $customer_identifier_pendente = $request->identificador_pendente;
                        $iban_pendente = $request->iban_pendente;
                        $nif_pendente = $request->nif_pendente;
                        $tipo_identifier_pendente = $request->tipoidentificador_pendente[0];
                        $tipo_customer_pendente = $request->tipocustomer_pendente[0];
                        $correlationidorigin = $request->correlationidorigin;
                        $acao = $request->acao2[0];

                    if ( $customer_identifier_pendente  == '' ) {
                        $result = 'Phone not set';
                    }else {

                        //está nas associações pendentes a reativar/eliminar
                        if (  $tipo_identifier_pendente == 1) {
                            $customer_identifier = $this->formataTelemovel( $customer_identifier_pendente );
                        }else {
                            $customer_identifier = $this->formataNif( $customer_identifier_pendente );
                        }
                        
                        if ( $acao == 1 ) {
                            \Log::channel($this->logchannel)->info( 'Vai invocar o servico INSERT do BP... ');
                            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP... reativarAssociacaoPendente');
                            $content = ( new BancoPortugalService( $this->logchannel ) )->reativarAssociacao($contrato, $iban_pendente,  $customer_identifier_pendente, $nif_pendente , $tipo_customer_pendente,$tipo_identifier_pendente, $correlationidorigin  );
                        }else {
                            \Log::channel($this->logchannel)->info( 'Vai invocar o servico DELETE do BP... ');
                            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP... eliminarAssociacaoPendente');
                            $content = ( new BancoPortugalService( $this->logchannel ) )->eliminarAssociacaoPendente($contrato, $iban_pendente,  $customer_identifier_pendente, $nif_pendente , $tipo_customer_pendente,$tipo_identifier_pendente, $correlationidorigin  );
                        }
                       
                        if ( $content ) {
                            \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
                        }else {
                            \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
                        }
                        \Log::channel($this->logchannel)->info( '****************************************************');
                        //analisa response TO DO
                        //agora esta true ou false
                        $result = $content;

                    }


                  
                }else {
                
                    if (  $tipo_identifier == 1 ) {
                        $customer_identifier = $this->formataTelemovel( $customer_identifier );
                        $tipo_customer = 1;
                    }else {
                        $customer_identifier = $this->formataNif( $customer_identifier );
                        $tipo_customer = 2;
                    }
  
                    if ( trim($iban) != '' ) {

                            $nif = 'NOTSET';

                            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter confirmaAssociacao IBAN...' . $customer_identifier);
                    
                            $content = ( new BancoPortugalService( $this->logchannel ) )->confirmaAssociacao($contrato, $iban,  $customer_identifier, $nif , $tipo_customer,$tipo_identifier );
                            if ( $content ) {
                                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
                            }else {
                                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
                            }
                            \Log::channel($this->logchannel)->info( '****************************************************');
                            //analisa response TO DO
                            //agora esta true ou false
                            $result = $content;

                    }else {
        
                        \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter IBAN...' . $customer_identifier);
                    
                            $content = ( new BancoPortugalService( $this->logchannel ) )->obtemIban( $contrato, $customer_identifier ) ;
                            if ( $content ) {
                                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
                            }else {
                                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
                            }
                            \Log::channel($this->logchannel)->info( '****************************************************');
                            //analisa response TO DO
                            //agora esta true ou false
                            $result = $content;
                    }
                
                }
            }

            session()->flashInput($request->input());
           
            return view('dashboard', compact('result'));
            
       }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }

    public function testescopb(Request $request)
    {
        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( 'TESTES---COPB POST---');
        \Log::channel($this->logchannel )->info( print_r($request->all(), true));
        try {

            $sessionid = 'not set';
            $contrato = 0;
            $copb = $request->copb;
            $result = 'not set';
            $ambiente = $request->ambiente;
            $nrteste = $request->nrcopb;
            $psp_destination = $request->psp_destination;

            if ( $ambiente ) {

                // \Log::channel($this->logchannel)->info( count($ambiente) );
                // \Log::channel($this->logchannel)->info( $ambiente[0]);

                if ( count($ambiente) == 1 ) {

                    if ($ambiente[0] == 1) { //teste

                        if ($nrteste != '' ) { //nr teste

                            \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter COPB Testes...');

                            $content = ( new BancoPortugalService( $this->logchannel ) )->invocaCopBTeste($contrato, $nrteste, $copb, $psp_destination);
                            if ( $content ) {
                                \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
                            }else {
                                \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
                            }
                            \Log::channel($this->logchannel)->info( '****************************************************');
                            //analisa response TO DO
                            //agora esta true ou false
                            $result = $content;
                        }
                    }else {
                        //producao
                        if ( $copb ) {

                            if ( count($copb) > 0 ) {
            
                                \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter COPB ...');
            
                                $content = ( new BancoPortugalService( $this->logchannel ) )->invocaCopB($contrato, $copb, $psp_destination);
                                if ( $content ) {
                                    \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
                                }else {
                                    \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
                                }
                                \Log::channel($this->logchannel)->info( '****************************************************');
                                //analisa response TO DO
                                //agora esta true ou false
                                $result = $content;
                            }
              
                        }
                    }
                }else {
                    $result = 'ambiente not set';
                }
            }

       
           

            return view('dashboard', compact('result'));
            
       }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }

    public function testesassociar(Request $request)
    {
        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( 'TESTES---ASSOCIAR---');
        \Log::channel($this->logchannel )->info( print_r($request->all(), true));
        try {

            $sessionid = 'not set';
            $contrato = 0;
            $customer_identifier = $request->identificador;
            $iban = $request->iban2;
            $nif = $request->nif;
            $tipo_customer = $request->tipocustomer[0];
            $tipo_identifier = $request->tipoidentificador[0];
            $acao = $request->acao[0];
            $result = [];

     
            if ( $customer_identifier == '' || $iban == '' ||  $nif == '') {
                $result = 'Data not set';
            }else {
               
                if (  $tipo_identifier == 1) {
                    $customer_identifier = $this->formataTelemovel( $customer_identifier );
                }

                //$customer_identifier = $this->formataTelemovel( $customer_identifier );
                $nif = $this->formataNif( $nif );
                \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP... ');
               
               if ( trim($iban) != '' ) {

                    if ($acao == '1') {
                        \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP... criaAssociacao');
                        $content = ( new BancoPortugalService( $this->logchannel ) )->criaAssociacao($contrato, $iban,  $customer_identifier, $nif , $tipo_customer,$tipo_identifier );
                 
                    }else {
                        \Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP... removeAssociacao');
                        $content = ( new BancoPortugalService( $this->logchannel ) )->removeAssociacao($contrato, $iban,  $customer_identifier, $nif , $tipo_customer,$tipo_identifier );
                 
                    }

                    if ( $content ) {
                        \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
                    }else {
                        \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
                    }
                    \Log::channel($this->logchannel)->info( '****************************************************');
                    //analisa response TO DO
                    //agora esta true ou false
                    $result = $content;

               }
               
             
            }

            session()->flashInput($request->input());
           
            return view('dashboard', compact('result'));
            
       }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }

    public function testescarga(Request $request)
    {
        $this->logchannel = 'carga';
        \Log::channel($this->logchannel )->info( 'TESTES---CARGA---');
        try {
            $result = '';
            return view('dashboard_carga', compact('result'))->withInput();
       }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }

    public function testescargaplpost(Request $request)
    {
        $this->logchannel = 'carga';
        \Log::channel($this->logchannel )->info( 'TESTES---CARGA PL POST---');
        //\Log::channel($this->logchannel )->info( print_r($request->all(), true));
        try {


            // //cria payload para COPB
            // $copb_0010 = 'PT50001000003686396000181;PT125983050;1';
            // $copb_0014 = 'PT50001400000127128310146;PT244660204;1';
            // $copb_0018 = 'PT50001800035553921702059;PT268385084;1';
            // $copb_0036 = 'PT50003600449910000008002;PT101509030;1';
            // //$copb_5200 = 'PT50520052000000575100174;PT209105445;1';
            
            // $copb_0007 = 'PT50000704100058974000041;PT108681173;1';
            // $copb_0023 = 'PT50002300004520270800494;PT165102705;1';
            // $copb_0035 = 'PT50003504300002086190039;PT123456789;1';
            // $copb_0045 = 'PT50004510104011151211872;PT168446367;1';
            // $copb_0781 = 'PT50078101129112500000743;PT699998646;2';

            // // $list_5200 = [];
            // // for ( $i = 1; $i <= 1000; $i++ ) {
            // //     $copb = explode(';', $copb_5200);
            // //     if ( count( $copb ) == 3) {
            // //         $iban = $copb[0];
            // //         $nif = $copb[1];
            // //         $tipo = $copb[2];
            // //         $item = [
            // //             'fiscal_number' => $nif,
            // //             'type' => (int) $tipo,
            // //             'iban' => $iban
            // //         ];
            // //         array_push($list_5200, $item);
            // //     }
            // // }
            // $list_0010 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0010);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0010, $item);
            //     }
            // }
            // $list_0014 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0014);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0014, $item);
            //     }
            // }
            // $list_0018 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0018);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0018, $item);
            //     }
            // }
            // $list_0036 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0036);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0036, $item);
            //     }
            // }
            // $list_0007 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0007);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0007, $item);
            //     }
            // }
            // $list_0023 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0023);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0023, $item);
            //     }
            // }
            // $list_0045 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0045);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0045, $item);
            //     }
            // }
            // $list_0781 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_0781);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_0781, $item);
            //     }
            // }

            //cria payload para COPS
            // $ibancop_0010 = 'PT50001000003686396000181';
            // $ibancop_0014 = 'PT50001400000127128310146';
            // $ibancop_0018 = 'PT50001800035553921702059';
            // $ibancop_0036 = 'PT50003600449910000008002';
            // $ibancop_5200 = 'PT50520052000000575100174';

            $i = 1;
            
            ini_set('max_execution_time',0);
            ini_set('memory_limit', '-1');

            $customer_identifier_active = '+351964255708';
            $customer_identifier_inactive = '+351964255707';
            $plcontact = '';

            while( $i <= 15 ){

                \Log::channel($this->logchannel)->info('*****************' . $i . '****************');
           
                \Log::channel($this->logchannel)->info('dispatch pl account...5200 '. $i);
                ChargeRequestPlAccount::dispatch($customer_identifier_active, $i);
                \Log::channel($this->logchannel)->info('dispatched pl account ...5200 '. $i);

                sleep(2);

                \Log::channel($this->logchannel)->info('dispatch pl account...5200 '. $i);
                ChargeRequestPlAccount::dispatch($customer_identifier_inactive, $i);
                \Log::channel($this->logchannel)->info('dispatched pl account ...5200 '. $i);


                // \Log::channel($this->logchannel)->info('dispatch pl contact...5200 '. $i);
                // ChargeRequestPlContact::dispatch($plcontact, $i);
                // \Log::channel($this->logchannel)->info('dispatched cops ...5200 '. $i);

                sleep(1);

                $i++;

                //dispacth for 0010
                // \Log::channel($this->logchannel)->info('dispatch...copb0010 '. $i);
                // ChargeRequestCOPB0010::dispatch('0010',$copb_0010,$list_0010,  $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb0010 '. $i);

                // \Log::channel($this->logchannel)->info('dispatch...0010 ' . $i);
                // ChargeRequestCOPS::dispatch($ibancop_0010, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0010 '. $i);
              
                // \Log::channel($this->logchannel)->info('dispatch...0014 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_0014, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0014 '. $i);
             
                // \Log::channel($this->logchannel)->info('dispatch...0018 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_0018, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0018 '. $i);
              
                // \Log::channel($this->logchannel)->info('dispatch...0036 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_0036, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0036 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch cops...5200 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_5200, $i);
                //\Log::channel($this->logchannel)->info('dispatched cops ...5200 '. $i);

                //sleep(1);

                // //\Log::channel($this->logchannel)->info('dispatch...copb 0010 ' . $i);
                // ChargeRequestCOPB::dispatch('0010',$copb_0010,$list_0010,  $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0010 '. $i);
               
                // //\Log::channel($this->logchannel)->info('dispatch...copb 0014 '. $i);
                // ChargeRequestCOPB::dispatch('0014',$copb_0014, $list_0014,$i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0014 '. $i);
               
                // //\Log::channel($this->logchannel)->info('dispatch...copb 0018 '. $i);
                // ChargeRequestCOPB::dispatch('0018',$copb_0018,$list_0018, $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0018 '. $i);
                
                // //\Log::channel($this->logchannel)->info('dispatch...copb 0036 '. $i);
                // ChargeRequestCOPB::dispatch('0036',$copb_0036,$list_0036, $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0036 '. $i);

                // //\Log::channel($this->logchannel)->info('dispatch...copb 0007 '. $i);
                // ChargeRequestCOPB::dispatch('0007',$copb_0007, $list_0007, $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0007 '. $i);

                // //\Log::channel($this->logchannel)->info('dispatch...copb 0023 '. $i);
                // ChargeRequestCOPB::dispatch('0023',$copb_0023, $list_0023, $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0023 '. $i);

                // //\Log::channel($this->logchannel)->info('dispatch...copb 0045 '. $i);
                // ChargeRequestCOPB::dispatch('0045',$copb_0045, $list_0045, $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0045 '. $i);

                // //\Log::channel($this->logchannel)->info('dispatch...copb 0781 '. $i);
                // ChargeRequestCOPB::dispatch('0781',$copb_0781, $list_0781, $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb 0781 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch...copb 5200 '. $i);
                //ChargeRequestCOPB::dispatch('5200',$copb_5200,$list_5200, $i);
                //\Log::channel($this->logchannel)->info('dispatched...copb 5200 '. $i);


               // break;
            }

            $result = [];

            session()->flashInput($request->input());
           
            return view('dashboard_carga', compact('result'));
            
       }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }

    public function testescargapost(Request $request)
    {
        $this->logchannel = 'carga';
        \Log::channel($this->logchannel )->info( 'TESTES---CARGA POST---');
        //\Log::channel($this->logchannel )->info( print_r($request->all(), true));
        try {


            //cria payload para COPB
            $copb_0010 = 'PT50001000003686396000181;PT125983050;1';
            $copb_0014 = 'PT50001400000127128310146;PT244660204;1';
            $copb_0018 = 'PT50001800035553921702059;PT268385084;1';
            $copb_0036 = 'PT50003600449910000008002;PT101509030;1';
            //$copb_5200 = 'PT50520052000000575100174;PT209105445;1';
            
            $copb_0007 = 'PT50000704100058974000041;PT108681173;1';
            $copb_0023 = 'PT50002300004520270800494;PT165102705;1';
            $copb_0035 = 'PT50003504300002086190039;PT123456789;1';
            $copb_0045 = 'PT50004510104011151211872;PT168446367;1';
            $copb_0781 = 'PT50078101129112500000743;PT699998646;2';

            // $list_5200 = [];
            // for ( $i = 1; $i <= 1000; $i++ ) {
            //     $copb = explode(';', $copb_5200);
            //     if ( count( $copb ) == 3) {
            //         $iban = $copb[0];
            //         $nif = $copb[1];
            //         $tipo = $copb[2];
            //         $item = [
            //             'fiscal_number' => $nif,
            //             'type' => (int) $tipo,
            //             'iban' => $iban
            //         ];
            //         array_push($list_5200, $item);
            //     }
            // }
            $list_0010 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0010);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0010, $item);
                }
            }
            $list_0014 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0014);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0014, $item);
                }
            }
            $list_0018 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0018);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0018, $item);
                }
            }
            $list_0036 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0036);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0036, $item);
                }
            }
            $list_0007 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0007);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0007, $item);
                }
            }
            $list_0023 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0023);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0023, $item);
                }
            }
            $list_0045 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0045);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0045, $item);
                }
            }
            $list_0781 = [];
            for ( $i = 1; $i <= 1000; $i++ ) {
                $copb = explode(';', $copb_0781);
                if ( count( $copb ) == 3) {
                    $iban = $copb[0];
                    $nif = $copb[1];
                    $tipo = $copb[2];
                    $item = [
                        'fiscal_number' => $nif,
                        'type' => (int) $tipo,
                        'iban' => $iban
                    ];
                    array_push($list_0781, $item);
                }
            }

            //cria payload para COPS
            // $ibancop_0010 = 'PT50001000003686396000181';
            // $ibancop_0014 = 'PT50001400000127128310146';
            // $ibancop_0018 = 'PT50001800035553921702059';
            // $ibancop_0036 = 'PT50003600449910000008002';
            // $ibancop_5200 = 'PT50520052000000575100174';

            $i = 1;
            
            ini_set('max_execution_time',0);
            ini_set('memory_limit', '-1');

            while( $i <= 500 ){

                //dispacth for 0010
                // \Log::channel($this->logchannel)->info('dispatch...copb0010 '. $i);
                // ChargeRequestCOPB0010::dispatch('0010',$copb_0010,$list_0010,  $i);
                // \Log::channel($this->logchannel)->info('dispatched...copb0010 '. $i);

                //dispatch for 0014 



                //\Log::channel($this->logchannel)->info('*****************' . $i . '****************');

                // \Log::channel($this->logchannel)->info('dispatch...0010 ' . $i);
                // ChargeRequestCOPS::dispatch($ibancop_0010, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0010 '. $i);
              
                // \Log::channel($this->logchannel)->info('dispatch...0014 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_0014, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0014 '. $i);
             
                // \Log::channel($this->logchannel)->info('dispatch...0018 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_0018, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0018 '. $i);
              
                // \Log::channel($this->logchannel)->info('dispatch...0036 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_0036, $i);
                // \Log::channel($this->logchannel)->info('dispatched...0036 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch cops...5200 '. $i);
                // ChargeRequestCOPS::dispatch($ibancop_5200, $i);
                //\Log::channel($this->logchannel)->info('dispatched cops ...5200 '. $i);

                //sleep(1);

                //\Log::channel($this->logchannel)->info('dispatch...copb 0010 ' . $i);
                ChargeRequestCOPB::dispatch('0010',$copb_0010,$list_0010,  $i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0010 '. $i);
               
                //\Log::channel($this->logchannel)->info('dispatch...copb 0014 '. $i);
                ChargeRequestCOPB::dispatch('0014',$copb_0014, $list_0014,$i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0014 '. $i);
               
                //\Log::channel($this->logchannel)->info('dispatch...copb 0018 '. $i);
                ChargeRequestCOPB::dispatch('0018',$copb_0018,$list_0018, $i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0018 '. $i);
                
                //\Log::channel($this->logchannel)->info('dispatch...copb 0036 '. $i);
                ChargeRequestCOPB::dispatch('0036',$copb_0036,$list_0036, $i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0036 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch...copb 0007 '. $i);
                ChargeRequestCOPB::dispatch('0007',$copb_0007, $list_0007, $i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0007 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch...copb 0023 '. $i);
                ChargeRequestCOPB::dispatch('0023',$copb_0023, $list_0023, $i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0023 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch...copb 0045 '. $i);
                ChargeRequestCOPB::dispatch('0045',$copb_0045, $list_0045, $i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0045 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch...copb 0781 '. $i);
                ChargeRequestCOPB::dispatch('0781',$copb_0781, $list_0781, $i);
                \Log::channel($this->logchannel)->info('dispatched...copb 0781 '. $i);

                //\Log::channel($this->logchannel)->info('dispatch...copb 5200 '. $i);
                //ChargeRequestCOPB::dispatch('5200',$copb_5200,$list_5200, $i);
                //\Log::channel($this->logchannel)->info('dispatched...copb 5200 '. $i);

                sleep(1);

                $i++;

               // break;
            }

            $result = [];

            session()->flashInput($request->input());
           
            return view('dashboard_carga', compact('result'));
            
       }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
        }
       
    }


    public function testa(Request $request, $nometeste, $parametro )
    {
        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( '');
        \Log::channel($this->logchannel )->info( '--------------------------');
        \Log::channel($this->logchannel )->info( 'CALL: testa ' . print_r($request->all(), true));
        \Log::channel($this->logchannel )->info( '--------------------------');
        \Log::channel($this->logchannel )->info( 'Parametro : ' . $parametro );
        try {

            //parametros para o servico
            //nome teste like sms, email, msg, etc...
            //parametro like telefone or email or tipomensagem , etc
            $nomeTest = trim( htmlentities(filter_var(strip_tags($nometeste), FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8') );
            $parametro = trim( filter_var(strip_tags($parametro), FILTER_SANITIZE_STRING));
            //$sessionid = trim( filter_var(strip_tags($request->session()->getId() )));
            $sessionid = 'not set';
            //inicia servico  de testes
            $service = new TestService($this->logchannel, $sessionid, $this->isConnProduction);
            //set parametros de teste
            
            \Log::channel($this->logchannel )->info( 'Nome : ' . $nomeTest );
            \Log::channel($this->logchannel )->info( 'Parametro : ' . $parametro );
            \Log::channel($this->logchannel )->info( '--------------------------');

            // echo $parametro;
            // return;

            $service->setTesteParameters( $nomeTest, $parametro );
            //inicia servico de validacao dos testes
            $this->initOperationTimerInfo('Iniciação do serviço de Testes...valida pedido...');
            $result = $service->validateTest();
            if ( $result != '' ) {
                $this->writeTemposOperation();
                echo $result;
                return;
            }
            $this->writeTemposOperation();

            $this->initTimerCounter();
            $result = $service->executeTeste();
            //\Log::channel($this->logchannel)->info( 'Resultado da Execução do TESTE: ' . print_r( $result, true) );

            if ( ! $result ) {
                $this->writeTempos();
                \Log::channel($this->logchannel )->info( 'Testes executados com erros');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                echo '-----------FIM DE TESTE COM ERROS---------------';
                return;
            }
            $this->writeTempos();
            \Log::channel($this->logchannel )->info( 'Testes executados sem erros');
            \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
 
            if ( $nomeTest == 'sibs' ) {
                if ( strtoupper(trim($parametro)) == 'H002' ) {
                    $response = array(
                        'connection' => ( $this->isConnProduction ? 'prod' : 'dev' ),
                        $parametro => $result,
                        'mensagemPedido' => $service->getMensagemPedidoH2H(),
                        'mensagemResposta' => $service->getMensagemRespostaH2H(),
                        'dados_ultimo_pagamento' => $service->getDadosUltimoPagamento(),
                        'resumo' => $service->getResumo(),
                        'tempo_sibs' => $service->getTempoOperacao(),
                    );
                }
                else 
                {
                    $response = array(
                        'connection' => ( $this->isConnProduction ? 'prod' : 'dev' ),
                        $parametro => $result,
                        'mensagemPedido' => $service->getMensagemPedidoH2H(),
                        'mensagemResposta' => $service->getMensagemRespostaH2H(),
                        'tempo_sibs' => $service->getTempoOperacao(),
                    );
                }
            }
            else 
            {
                $response = array(
                    'connection' => ( $this->isConnProduction ? 'prod' : 'dev' ),
                    $parametro => $this->convert_from_latin1_to_utf8_recursively($result),
                );
            }

            // $json = Collection::make([
            //    $response
            //   ])->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                
      

            $aspspcdes = null;
            if ( $nomeTest == 'oba_v1' || $nomeTest == 'oba_v2' || $nomeTest == 'oba_v3' ||  $nomeTest == 'oba_v4' ) {
                $decoded_json = json_decode($json, true);
                //\Log::channel($this->logchannel )->info( $decoded_json );
                $aspspcdes = $decoded_json['LIST_ALL_ASPSP']['content'];
                $aspspcdes['version'] = $nomeTest;
                $aspspcdes['url'] = $decoded_json['LIST_ALL_ASPSP']['url'];
                \Log::channel($this->logchannel )->info( print_r( $aspspcdes , true) );
 
            }
            
            //$decoded_json = json_decode($json, true);
            //return response()->json($json);
            header('Content-Type: application/json; charset=utf-8');

            return view( 'testresult', compact('json', 'parametro', 'aspspcdes') );

            //return response()->json($response);
            // return response()->json($json);
            // return $json;

            // $json_string = json_encode($response, JSON_PRETTY_PRINT);

            // echo $json_string;

        }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            echo 'fim teste com erros e exceção';
            throw $e;
        }
    }

    public function testevalor(Request $request, $nometeste, $parametro, $valor )
    {
        $this->logchannel = 'testes';
        \Log::channel($this->logchannel )->info( '');
        \Log::channel($this->logchannel )->info( '--------------------------');
        \Log::channel($this->logchannel )->info( 'CALL: testa ' . print_r($request->all(), true));
        \Log::channel($this->logchannel )->info( '--------------------------');
        try {

            //parametros para o servico
            //nome teste like sms, email, msg, etc...
            //parametro like telefone or email or tipomensagem , etc
            $nomeTest = trim( htmlentities(filter_var(strip_tags($nometeste), FILTER_SANITIZE_STRING), ENT_QUOTES, 'UTF-8') );
            $parametro = trim( filter_var(strip_tags($parametro), FILTER_SANITIZE_STRING));
            $sessionid = trim( filter_var(strip_tags($request->session()->getId() )));

            //inicia servico  de testes
            $service = new TestService($this->logchannel, $sessionid, $this->isConnProduction);
            //set parametros de teste
            $service->setTesteParameters( $nomeTest, $parametro );
            //inicia servico de validacao dos testes
            $this->initOperationTimerInfo('Iniciação do serviço de Testes...valida pedido...');
            $result = $service->validateTest();
            if ( $result != '' ) {
                $this->writeTemposOperation();
                echo $result;
                return;
            }
            $this->writeTemposOperation();

            $this->initTimerCounter();
            $result = $service->executeTeste();
            \Log::channel($this->logchannel)->info( 'Resultado da Execução do TESTE: ' . print_r( $result, true) );

            if ( ! $result ) {
                $this->writeTempos();
                \Log::channel($this->logchannel )->info( 'Testes executados com erros');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                echo $result;
                return;
            }
            $this->writeTempos();
            \Log::channel($this->logchannel )->info( 'Testes executados sem erros');
            \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
 
            if ( $nomeTest == 'sibs' ) {
                if ( strtoupper(trim($parametro)) == 'H002' ) {
                    $response = array(
                        'connection' => ( $this->isConnProduction ? 'prod' : 'dev' ),
                        $parametro => $result,
                        'mensagemPedido' => $service->getMensagemPedidoH2H(),
                        'mensagemResposta' => $service->getMensagemRespostaH2H(),
                        'dados_ultimo_pagamento' => $service->getDadosUltimoPagamento(),
                        'resumo' => $service->getResumo(),
                        'tempo_sibs' => $service->getTempoOperacao(),
                    );
                }
                else 
                {
                    $response = array(
                        'connection' => ( $this->isConnProduction ? 'prod' : 'dev' ),
                        $parametro => $result,
                        'mensagemPedido' => $service->getMensagemPedidoH2H(),
                        'mensagemResposta' => $service->getMensagemRespostaH2H(),
                        'tempo_sibs' => $service->getTempoOperacao(),
                    );
                }
            }
            else 
            {
                $response = array(
                    'connection' => ( $this->isConnProduction ? 'prod' : 'dev' ),
                    $parametro => $this->convert_from_latin1_to_utf8_recursively($result),
                );
            }

            // $json = Collection::make([
            //    $response
            //   ])->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                
            $aspspcdes = null;
            if ( $nomeTest == 'oba_v1' || $nomeTest == 'oba_v2' || $nomeTest == 'oba_v3' ||  $nomeTest == 'oba_v4' ) {
                $decoded_json = json_decode($json, true);
                //\Log::channel($this->logchannel )->info( $decoded_json );
                $aspspcdes = $decoded_json['LIST_ALL_ASPSP']['content'];
                $aspspcdes['version'] = $nomeTest;
                $aspspcdes['url'] = $decoded_json['LIST_ALL_ASPSP']['url'];
                \Log::channel($this->logchannel )->info( print_r( $aspspcdes , true) );
 
            }
            
          
            return view( 'testes.testresult', compact('json', 'parametro', 'aspspcdes') );

            //return response()->json($response);
            // return response()->json($json);
            return $json;

            $json_string = json_encode($response, JSON_PRETTY_PRINT);

            echo $json_string;

        }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            echo 'fim teste com erros e exceção';
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

    private function formataNif( $nif )
    {
        try{
        
            if ( trim($nif) != '' ) {
        
                $startString = 'PT';
                $len = strlen($startString); 
                $comeca = (substr($nif, 0, $len) === $startString); 
                if ( $comeca ) { return $nif; }
               
                return 'PT' . $nif;   

            }

        }catch( \Exception $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Contadores
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

        \Log::channel($this->logchannel)->info($datainfo . ' : ' . $dt);


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

            $datainfo = PHP_EOL . 'Inicio Pedido: ' .  $this->dt_inicio_gba . PHP_EOL . 
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

            $datainfo = PHP_EOL . 'Inicio Pedido: ' .  $this->dt_inicio_operacao . PHP_EOL . 
                        'Resposta ao pedido : ' . $this->dt_resposta_operacao . PHP_EOL . 
                        'Tempos Operação : ' . $differencegba ;
 
            \Log::channel($this->logchannel)->info($datainfo);

        } catch(Exception $e){
            \Log::channel($this->logchannel)->error($e->getMessage());
        }
    }


    
    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) {
                $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);
            }

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) {
                $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);
            }

            return $dat;
        } else {
            return $dat;
        }
    }

}
