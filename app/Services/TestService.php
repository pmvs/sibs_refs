<?php 

namespace App\Services;

use DB;
use Mail;
use Swift_SmtpTransport;
use Swift_Mailer;
use File;
use Storage;
use DateTime;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;

use App\Services\MessageService\MessageGateway;
use App\Services\MessageService\MessageService;
use App\Services\MessageService\SendMessageInterface;
use App\Services\MessageService\SendMessageSMS;
use App\Services\MessageService\SendMessageEmail;
use App\Services\AuthService\AuthService;
use App\Services\SibsService\SibsService;
use App\Services\ApiService\ApiService;
use App\Services\BancoPortugal\ApiCop;
use App\Services\BancoPortugal\ApiProxyLookup;

use App\Jobs\SendEmail;
use App\Jobs\SendSMS;
use App\Jobs\ProcessaListaSancionados;

use App\Models\BBalcao;
use App\Models\Gba\Iban;
use App\Models\Gba\DestinatariosFrequentes;


class TestService extends Controller
{
    private $logchannel = 'testes';
    private $sessionid = '';
    private $valorTeste = '';
    private $arrayTestes = [];
    private $arrayTiposMensagensTeste = [];
    private $arrayTiposMensagensOBA = [];

    private $executaTesteSMS = ['false', 'false','false','false','true','false','false'];
    private $executaTesteEmail = ['false', 'false', 'true', 'false'];
    private $executaTesteMSG = ['false','false', 'false', 'true'];
    private $executaTesteHASH = ['false', 'false', 'true'];
    private $executaTesteSIBS = ['true'];
    private $executaTesteAPI = ['true'];
    private $executaTesteListas = ['true'];
    private $executaTesteExceptions = ['true'];
    private $executaTesteOBA = ['true'];
    private $executaTesteAttacks = ['true'];
    private $executaTesteIAText = ['true'];
    private $executaTesteMovimentos = ['true'];
    private $executaTesteIBAN = ['true'];
    private $executaTesteProxyLookup = ['true'];
    private $arrayMensagensH2H = [
        'H002',
        'H004',
    ];
    private $arrayMensagensAPI = [
        'bic',
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
    private $connproducao = true;
    private $queueName = 'sibs';
    private $isConnProduction = false;
    private $nomeTeste = '';
    private $parametro = '';
    private $tamanhoMensagensH2H = [
        'H002' => 41,
        'S002OK' => 199,
        'S002NOK' => 135,
        'H004' => 73,
        'S004OK' => 176,
        'S004NOK' => 135,
        'H524' => 302,
        'S524OK' => 141,
        'S524NOK' => 135,
    ];
    private $msgPedidoH2H = '';
    private $msgRespostaH2H = '';
    private $resumo = '';
    private $arrayListas= [];
    private $arrayExceptions = [];
    private $arrayAtaquesPermitidos = [];
    private $timestamp = '';
    private $dataTestes = [];
    
    /*
    |--------------------------------------------------------------------------
    | CONSTRUTORES
    |--------------------------------------------------------------------------
    */ 
    public function __construct() 
    {
        $this->sessionid = '';
        $this->logchannel = 'testes';
        $this->queueName = 'sibs';
        $this->nomeTeste = 'testes';
        $this->parametro = 'testes';
        $this->valorTeste = '';
        $this->isConnProduction = config('app.connection_prod');
        $this->arrayTestes = config('enums.testes');
        $this->arrayTiposMensagensTeste = config('enums.tiposmensagensteste');
        $this->arrayTiposMensagensOBA = config('enums.tiposmensagensoba');
        $this->arrayMensagensH2H = config('enums.mensagensH2H');
        $this->arrayMensagensAPI = config('enums.mensagensAPI');
        $this->tamanhoMensagensH2H = config('enums.tamanhoMensagensH2H');
        $this->arrayListas = config('enums.listas');
        $this->arrayExceptions = config('enums.exceptions');
        $this->arrayAtaquesPermitidos = config('enums.attacks');
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct TestService');
        \Log::channel($this->logchannel)->info('Connection PROD ? ' . ( $this->isConnProduction ? 'Sim' : 'Não' ) ) ;
    }

    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 TestService');
    }

    public function __construct_2($logchannel, $sessionid) 
    {
        $this->logchannel = $logchannel;
        $this->sessionid = $sessionid;
        \Log::channel($this->logchannel)->info('__construct_2 TestService'); 
    }

    public function __construct_3($logchannel, $sessionid, $isConnProduction) 
    {
        $this->logchannel = $logchannel;
        $this->sessionid = $sessionid;
        $this->isConnProduction = $isConnProduction;
        \Log::channel($this->logchannel)->info('__construct_3 TestService'); 
    }


    /*
    |--------------------------------------------------------------------------
    | SETTERS
    |--------------------------------------------------------------------------
    */  
    public function setLogChannel($logchannel)
    {
        $this->logchannel = $logchannel;
    }

    public function setSessionId($sessionid)
    {
        $this->sessionid = $sessionid;
    }

    public function setConnectionProducao( $connproducao )
    {
        $this->connproducao = $connproducao;
        $this->isConnProduction = $connproducao;
        //\Log::channel($this->logchannel)->info('Connection PROD ? ' . $this->connproducao );
    }

    public function setIsConnProduction( $isConnProduction )
    {
        $this->isConnProduction = $isConnProduction;
    }

    public function setTesteParameters( $nomeTest, $parametro )
    {
        $this->nomeTeste = $nomeTest;
        $this->parametro = $parametro;
    }

    public function setTesteValor( $valor )
    {
        $this->valorTeste = $valor;
    }



    /*
    |--------------------------------------------------------------------------
    | PUBLIC TESTES SERVICE CALLS
    |--------------------------------------------------------------------------
    */  
    public function validateTest()
    {
        try{
            \Log::channel($this->logchannel )->info( 'validateTest...');
            if ( trim($this->nomeTeste) == '' ) {
                \Log::channel($this->logchannel )->info( 'Nome do TESTE não se encontra preenchido.');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                return 'Nome do TESTE não se encontra preenchido.';
            }
            if ( trim($this->parametro) == '' ) {
                \Log::channel($this->logchannel )->info( 'Nome do PARAMETRO não se encontra preenchido.');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                return 'Nome do PARAMETRO não se encontra preenchido.';
            }

            //valida nome do teste
            if ( ! in_array( trim($this->nomeTeste), $this->arrayTestes) ) {
                \Log::channel($this->logchannel )->info( 'Nome do TESTE não se encontra implementado.');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                return 'Nome do TESTE não se encontra implementado.';
            }

            //valida parametros do teste
            switch( $this->nomeTeste )
            {

                case 'bp':
                    //valida parametro
                     if ( ! in_array( $this->parametro, $this->arrayTiposBancoPortugal) ) {
                         \Log::channel($this->logchannel )->info( 'PARAMETRO BP com valores incorretos.');
                         \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                         return 'PARAMETRO BP com valores incorretos.';
                     }
                     break;


                case 'movimentos':
                    //valida parametro
                    if (  $this->parametro == '' ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO MOVIMENTOS com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO MOVIMENTOS com valores incorretos.';
                    }
                    break;
                
                case 'cartoes':
                    //valida parametro
                    if (  $this->parametro == '' ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO CARTOES com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO CARTOES com valores incorretos.';
                    }
                    break;
    
                case 'pan':
                    //valida parametro
                    if (  $this->parametro == '' ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO PAN com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO PAN com valores incorretos.';
                    }
                    break;
    
                case 'ia-models':
                    return '';
                    break;

                case 'ia-text':
                    //valida parametro
                    \Log::channel($this->logchannel )->info( 'PARAMETROS IA TEXT PERMITIDOS APENAS STRINGS');
                    if ( ! filter_var( $this->parametro, FILTER_SANITIZE_STRING) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO IA com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO IA com valores incorretos.';
                    }
                    break;


                case 'attacks':
                case 'email':
                    //valida parametro
                    if ( ! filter_var( $this->parametro, FILTER_SANITIZE_EMAIL) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO EMAIL com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO EMAIL com valores incorretos.';
                    } 
                    break;

                case 'sms':
                    //valida parametro
                    if ( ! is_numeric( $this->parametro )  ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO TELEFONE com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO TELEFONE com valores incorretos.';
                    }
                    break;
                
                case 'sibs':
                    //valida parametro
                    \Log::channel($this->logchannel )->info( 'Mensagens H2H permitidas : ' . print_r( $this->arrayMensagensH2H , true));
                    if ( ! in_array( strtoupper($this->parametro),  $this->arrayMensagensH2H ) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO SIBS ' . $this->parametro . ' com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO SIBS com valores incorretos.';
                    }
                    break;

                case 'msg':
                   //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayTiposMensagensTeste) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO MSG com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO MSG com valores incorretos.';
                    }
                    break;

                case 'oba_v1':
                case 'oba_v2':
                case 'oba_v3':
                case 'oba_v4':
                    //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayTiposMensagensOBA) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO OBA com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO OBA com valores incorretos.';
                    }
                    break;

                case 'api':
                     //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayMensagensAPI) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO API com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO API com valores incorretos.';
                    }
                    break;

                case 'hash':
                    //valida parametro
                    if (  $this->parametro != '' ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO HASH com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO HASH com valores incorretos.';
                    }
                    break;

                case 'listas':
                    //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayListas) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO LISTAS com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO LISTAS com valores incorretos.';
                    }
                    break;

                case 'exceptions':
                    //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayExceptions) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO EXCEPTION com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO EXCEPTION com valores incorretos.';
                    }
                    break;

                case 'proxylookup':

                    //valida parametro
                    if (  $this->parametro == '' ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO PROXYLOOKUP com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO PROXYLOOKUP com valores incorretos.';
                    }
                    //valida parametro
                    if ( $this->parametro != 'all' ) {
                        // if ( ! is_numeric($this->parametro)  ) {
                        //     \Log::channel($this->logchannel )->info( 'PARAMETRO PROXYLOOKUP com tamanho incorreto.');
                        //     \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        //     return 'PARAMETRO PROXYLOOKUP só pode ser all ou um numero.';
                        // }
                    }
                    break;
    
    
                default:
                    return trim($this->nomeTeste) . ' não implementado.';
            }

            return '';

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return 'Erro interno.';
        }
    }

    public function validateTestWithValor()
    {
        \Log::channel($this->logchannel )->info( 'validateTestWithValor...');
        try{

            if ( trim($this->nomeTeste) == '' ) {
                \Log::channel($this->logchannel )->info( 'Nome do TESTE não se encontra preenchido.');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                return 'Nome do TESTE não se encontra preenchido.';
            }
            if ( trim($this->parametro) == '' ) {
                \Log::channel($this->logchannel )->info( 'Nome do PARAMETRO não se encontra preenchido.');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                return 'Nome do PARAMETRO não se encontra preenchido.';
            }
            if ( trim($this->valorTeste) == '' ) {
                \Log::channel($this->logchannel )->info( 'Nome do VALOR não se encontra preenchido.');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                return 'Nome do VALOR não se encontra preenchido.';
            }

            //valida nome do teste
            if ( ! in_array( trim($this->nomeTeste), $this->arrayTestes) ) {
                \Log::channel($this->logchannel )->info( 'Nome do TESTE não se encontra implementado.');
                \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                return 'Nome do TESTE não se encontra implementado.';
            }

            //valida parametros do teste
            switch( $this->nomeTeste ){

                case 'bp':
                    //valida parametro
                     if ( ! in_array( $this->parametro, $this->arrayTiposBancoPortugal) ) {
                         \Log::channel($this->logchannel )->info( 'PARAMETRO BP com valores incorretos.');
                         \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                         return 'PARAMETRO BP com valores incorretos.';
                     }
                     break;

                case 'ia-models':
                    return '';
                    break;

                case 'ia-text':
                    //valida parametro
                    \Log::channel($this->logchannel )->info( 'PARAMETROS IA TEXT PERMITIDOS APENAS STRINGS');
                    if ( ! filter_var( $this->parametro, FILTER_SANITIZE_STRING) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO IA com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO IA com valores incorretos.';
                    }
                    break;


                case 'attacks':
                    //valida parametro
                    \Log::channel($this->logchannel )->info( 'PARAMETROS ATTACKS PERMITIDOS APENAS STRINGS');
                    if ( ! filter_var( $this->parametro, FILTER_SANITIZE_STRING) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO ATTACKS com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'ATTACKS ATTACKS com valores incorretos.';
                    } 
                    \Log::channel($this->logchannel )->info( 'PARAMETROS PERMITIDOS : ' . print_r( $this->arrayAtaquesPermitidos, true));
                    if ( ! in_array( $this->parametro ,  $this->arrayAtaquesPermitidos ) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO ATTACKS ' . $this->parametro . ' com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO ATTACKS com valores incorretos.';
                    }
                    break;

                case 'email':
                    //valida parametro
                    if ( ! filter_var( $this->parametro, FILTER_SANITIZE_EMAIL) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO EMAIL com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO EMAIL com valores incorretos.';
                    } 
                    break;

                case 'sms':
                    //valida parametro
                    if ( ! is_numeric( $this->parametro )  ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO TELEFONE com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO TELEFONE com valores incorretos.';
                    }
                    break;
                
                case 'sibs':
                    //valida parametro
                    \Log::channel($this->logchannel )->info( 'Mensagens H2H permitidas : ' . print_r( $this->arrayMensagensH2H , true));
                    if ( ! in_array( strtoupper($this->parametro),  $this->arrayMensagensH2H ) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO SIBS ' . $this->parametro . ' com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO SIBS com valores incorretos.';
                    }
                    break;

                case 'msg':
                   //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayTiposMensagensTeste) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO MSG com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO MSG com valores incorretos.';
                    }
                    break;

                case 'api':
                    //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayMensagensAPI) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO API com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO API com valores incorretos.';
                    }
                    break;

                case 'movimentos':
                    //valida parametro
                    if (  $this->parametro == '' ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO MOVIMENTOS com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO MOVIMENTOS com valores incorretos.';
                    }
                    break;

                case 'oba_v1':
                case 'oba_v2':
                case 'oba_v3':
                case 'oba_v4':
                    //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayTiposMensagensOBA) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO OBA com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO OBA com valores incorretos.';
                    }
                    break;

                case 'api':
                       //valida parametro
                       if ( ! in_array( $this->parametro, $this->arrayMensagensAPI) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO API com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO API com valores incorretos.';
                    }
                    break;
                case 'hash':
                    //valida parametro
                    if (  $this->parametro != '' ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO HASH com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO HASH com valores incorretos.';
                    }
                    break;

                case 'listas':
                    //valida parametro
                    if ( ! in_array( $this->parametro, $this->arrayListas) ) {
                        \Log::channel($this->logchannel )->info( 'PARAMETRO LISTAS com valores incorretos.');
                        \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                        return 'PARAMETRO LISTAS com valores incorretos.';
                    }
                    break;
                
                case 'exceptions':
                        //valida parametro
                        if ( ! in_array( $this->parametro, $this->arrayExceptions) ) {
                            \Log::channel($this->logchannel )->info( 'PARAMETRO EXCEPTION com valores incorretos.');
                            \Log::channel($this->logchannel )->info( '-----------FIM DE TESTE---------------');
                            return 'PARAMETRO EXCEPTION com valores incorretos.';
                        }
                        break;
        
                default:
                    return trim($this->nomeTeste) . ' não implementado.';
            }

            return '';

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function executeTeste()
    {
        \Log::channel($this->logchannel)->info('TestService::executeTeste : ' . $this->nomeTeste );
   
        if( trim($this->nomeTeste) == ''){
            \Log::channel($this->logchannel)->error('TestService::executeTest não tem o nome do teste definido.');
            return false;
        }
         if( trim($this->parametro) == ''){
            \Log::channel($this->logchannel)->error('TestService::executeTest não tem o nome do parametro definido.');
            return false;
        }

        switch( trim($this->nomeTeste) ){

            case 'ia-models':
                return $this->executeTestIaModels();

            case 'ia-text':
                return $this->executeTestIaText();

            case 'attacks':
                return $this->executeTestAttacks();

            case 'exceptions': 
                return $this->executeTestExceptions();

            case 'listas': 
                return $this->executeTestListas();

            case 'api': 
                return $this->executeTestAPI();

            case 'movimentos': 
                return $this->executeTestMovimentos();

            case 'pan': 
                return $this->executeTestPan();
    
            case 'cartoes': 
                return $this->executeTestCartoes();

            case 'sms': 
                return $this->executeTestSMS( $this->parametro );

            case 'email': 
                return $this->executeTestEmail( $this->parametro );

            case 'msg': 
                return $this->executeTestMSG( $this->parametro );

            case 'oba_v1': 
                return $this->executeTestOBA( 'v1' );
        
            case 'oba_v2': 
                return $this->executeTestOBA( 'v2' );

            case 'oba_v3': 
                return $this->executeTestOBA( 'v3' );

            case 'oba_v4': 
                return $this->executeTestOBA( 'v4' );

            case 'hash': 
                return $this->executeTestHASH( $this->parametro );

            case 'sibs': 
                return $this->executeTestSIBS( strtoupper($this->parametro) );
                
            case 'proxylookup': 
                return $this->executeTestProxyLookup();
    
            default: 
                \Log::channel($this->logchannel)->info('TestService::executeTeste : ' . $this->nomeTeste . ' NÃO implementado.');
                return false;
        }

        return true;

    }

 

    /*
    |--------------------------------------------------------------------------
    | SERVICE PROXYLOOKUP
    |--------------------------------------------------------------------------
    */ 

    private function executeTestProxyLookup()
    {
        \Log::channel($this->logchannel)->info('TestService::executeTestProxyLookup...com parametro : ' . $this->parametro);
       try{

            $executaTeste1 = filter_var( $this->executaTesteProxyLookup[0] , FILTER_VALIDATE_BOOLEAN );
  
            \Log::channel($this->logchannel )->info( 'TESTE : PROXY LOOKUP... ' . $this->parametro);
            \Log::channel($this->logchannel )->info( 'EXECUTA TESTE ...' . ($executaTeste1 ? 'SIM': 'NÃO'));

            if ( $executaTeste1 )  $executaTeste1 = $this->executaTestesBancoPortugal();
         
    
            return $executaTeste1;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true , 'message'=> $e->getMessage()];
        }
    }

    private function executaTestesBancoPortugal()
    {
        \Log::channel($this->logchannel)->info('TestService::executaTestesBancoPortugal...com parametro : ' . $this->parametro);
        try{

            //array com dados de teste   
            $this->dataTestes = $this->fillDataBancoPortugalTestes();
            //\Log::channel($this->logchannel)->info('Dados para testes...: ' . print_r($this->dataTestes, true));

            //array de testes
            $this->testes = [];

            if ( $this->parametro == 'all' ) {

                foreach(  $this->dataTestes as $metateste ){
                    \Log::channel($this->logchannel)->info('...............................................' );
                    \Log::channel($this->logchannel)->info('Execução do teste ' . $metateste['nrteste']. ' : ' . $metateste['nmteste'] );
                    \Log::channel($this->logchannel)->info('Ficheiro a ser usado: ' . $metateste['fileteste'] );
                    \Log::channel($this->logchannel)->info('Endpoint: ' . $metateste['endpointteste'] );
                    \Log::channel($this->logchannel)->info('...............................................' );
                    if ( trim($metateste['fileteste']) != '' ) {
                        //invocacao das APIs COP e PL
                        $this->testes = $this->executaTesteBancoPortugal( $metateste );
                    }else{
                        //invocacao das APIs HEALTH
                        $this->testes = $this->executaTesteHealthBancoPortugal( $metateste );
                    }
                    \Log::channel($this->logchannel)->info('...............................................' );
                }

            }else{

                $nrTesteToExecute = $this->parametro;
                \Log::channel($this->logchannel)->info('Execução do teste ' . $nrTesteToExecute );
                $nrTesteToExecute = str_replace('-','.',$this->parametro);
                \Log::channel($this->logchannel)->info('Execução do teste ' . $nrTesteToExecute );
                $this->parametro = $nrTesteToExecute;

                foreach(  $this->dataTestes as $metateste ){

                    \Log::channel($this->logchannel)->info( '['. $nrTesteToExecute . '] [' . $metateste['nrteste'] .']');

                    if ( $metateste['nrteste'] == substr($nrTesteToExecute, 0, 1) && $nrTesteToExecute < 100  ) {
                        \Log::channel($this->logchannel)->info('...............................................' );
                        \Log::channel($this->logchannel)->info('Execução do teste ' . $metateste['nrteste']. ' : ' . $metateste['nmteste'] );
                        \Log::channel($this->logchannel)->info('Ficheiro a ser usado: ' . $metateste['fileteste'] );
                        \Log::channel($this->logchannel)->info('Endpoint: ' . $metateste['endpointteste'] );
                        \Log::channel($this->logchannel)->info('...............................................' );
                        if ( trim($metateste['fileteste']) != '' ) {
                            //invocacao das APIs COP e PL
                            $this->testes = $this->executaTesteBancoPortugal( $metateste );
                        }
                        \Log::channel($this->logchannel)->info('...............................................' );
                    }else {
                        if($metateste['nrteste'] == $nrTesteToExecute) {
                            \Log::channel($this->logchannel)->info('...............................................' );
                            \Log::channel($this->logchannel)->info('Execução do teste ' . $metateste['nrteste']. ' : ' . $metateste['nmteste'] );
                            \Log::channel($this->logchannel)->info('Ficheiro a ser usado: ' . $metateste['fileteste'] );
                            \Log::channel($this->logchannel)->info('Endpoint: ' . $metateste['endpointteste'] );
                            \Log::channel($this->logchannel)->info('...............................................' );
                          
                            //invocacao das APIs HEALTH
                            $this->testes = $this->executaTesteHealthBancoPortugal( $metateste );
                           
                            \Log::channel($this->logchannel)->info('...............................................' );
                        }
                       
                    }
                }
            }

            return $this->testes;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return ['error' => true , 'message' => $e->getMessage()];
        }
    }

    private function fillDataBancoPortugalTestes()
    {
        try{

            $this->dataTestes = [];

            //get values to replace from env
            $pspcode = config('app.pspcode');
            $pspcodeother = config('app.pspcodeother');
            $pspcodeinvalido = config('app.pspcodeinvalido');
            $pspcodediferenteiban = config('app.pspcodediferenteiban');
            $fiscalnumberparticular = config('app.fiscalnumberparticular');
            $fiscalnumberparticularother = config('app.fiscalnumberparticularother');
            $fiscalnumberparticularother2 = config('app.fiscalnumberparticularother2');
            $fiscalnumberparticularother3 = config('app.fiscalnumberparticularother3');
            $fiscalnumberparticularinvalido = config('app.fiscalnumberparticularinvalido');
            $fiscalnumberempresa = config('app.fiscalnumberempresa');
            $fiscalnumberempresanaoaderente = config('app.fiscalnumberempresanaoaderente');
            $fiscalnumberempresainvalido = config('app.fiscalnumberempresainvalido');
            $telemovelnacional = config('app.telemovelnacional');
            $telemovelnacionalinvalido = config('app.telemovelnacionalinvalido');
            $telemovelnacionalparticular = config('app.telemovelnacionalparticular');
            $telemovelnacionalempresa = config('app.telemovelnacionalempresa');
            $telemovelinternacional = config('app.telemovelinternacional');
            $telemovelnacionalnaoassociado = config('app.telemovelnacionalnaoassociado');
            $ibanparticular = config('app.ibanparticular');
            $ibanempresa = config('app.ibanempresa');
            $ibanempresa2 = config('app.ibanempresa2');
            $ibanempresa3 = config('app.ibanempresa3');
            $telemovelnacionalsemindicativo = config('app.telemovelnacionalsemindicativo');
            $ibanparticularinvalido = config('app.ibanparticularinvalido');
            $ibanparticular2 = config('app.ibanparticular2');
            $ibanparticular3 = config('app.ibanparticular3');
            $ibanparticularvarios = config('app.ibanparticularvarios');
            $ibanother = config('app.ibanother');
            $ibanempresainvalido = config('app.ibanempresainvalido');
            //lista de contatos
            // $listacontatos = config('app.listacontatos');
            // $listacontatosnenhumaderente = config('app.listacontatosnenhumaderente');
            // $listacontatosexcede = config('app.listacontatosexcede');
            // $listacontatosigual = config('app.listacontatosigual');

            $listacontatos = $this->getListaContatosHomebanking(1);
            // $listacontatosnenhumaderente = $listacontatos;
             $listacontatosexcede =  $this->getListaContatosHomebanking(2);
            // $listacontatosigual =  $this->getListaContatosHomebanking(3);
  
            //$listacontatos = '';
            $listacontatosnenhumaderente ='';
            // $listacontatosexcede = '';
            $listacontatosigual ='';

            $this->dataTestes = [
                'pspcode' => $pspcode,
                'pspcodeother' => $pspcodeother,
                'pspcodeinvalido' => $pspcodeinvalido,
                'pspcodediferenteiban' => $pspcodediferenteiban,
                'fiscalnumberparticular' => $fiscalnumberparticular,
                'fiscalnumberparticularother' => $fiscalnumberparticularother,
                'fiscalnumberparticularother2' => $fiscalnumberparticularother2,
                'fiscalnumberparticularother3' => $fiscalnumberparticularother3,
                'fiscalnumberparticularinvalido' => $fiscalnumberparticularinvalido,
                'fiscalnumberempresa' => $fiscalnumberempresa,
                'fiscalnumberempresanaoaderente' => $fiscalnumberempresanaoaderente,
                'fiscalnumberempresainvalido' => $fiscalnumberempresainvalido,
                'telemovelnacional' => $telemovelnacional,
                'telemovelnacionalinvalido' => $telemovelnacionalinvalido,
                'telemovelnacionalsemindicativo' => $telemovelnacionalsemindicativo,
                'telemovelnacionalparticular' => $telemovelnacionalparticular,
                'telemovelnacionalempresa' => $telemovelnacionalempresa,
                'telemovelinternacional' => $telemovelinternacional,
                'telemovelnacionalnaoassociado' => $telemovelnacionalnaoassociado,
                'ibanparticular' => $ibanparticular,
                'ibanparticular2' => $ibanparticular2,
                'ibanparticular3' => $ibanparticular3,
                'ibanparticularvarios' => $ibanparticularvarios,
                'ibanP' =>  ( new IBAN ( $ibanparticular, $this->logchannel ) )->getInfoIban(),
                'ibanempresa' => $ibanempresa,
                //'ibanE' => serialize( ( new IBAN ( $ibanempresa, $this->logchannel ) ) ),
                'ibanempresa2' => $ibanempresa2,
                //'ibanE2' => serialize( ( new IBAN ( $ibanempresa2, $this->logchannel ) ) ),
                'ibanempresa3' => $ibanempresa3,
                // 'ibanE3' => serialize( ( new IBAN ( $ibanempresa3, $this->logchannel ) ) ),
                'ibanparticularinvalido' => $ibanparticularinvalido,
                'ibanparticular2' => $ibanparticular2,
                'ibanother' => $ibanother,
                'listacontatos' => $listacontatos,
                'listacontatosnenhumaderente' => $listacontatosnenhumaderente,
                'listacontatosexcede' => $listacontatosexcede,
                'listacontatosigual' => $listacontatosigual,
            ];

            //todos os dados para testes
            $this->metaDataTestes = [ 
                    '1' => [
                            'nrteste' => 1,
                            'nmteste' => 'PL - Associação',
                            'fileteste' => 'bpplcp\proxylookup-dummy-data-association.txt',
                            'endpointteste' => config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_INSERT_ASSOC'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 31,
                            'ncolunasteste' => 10,
                            'dados' =>  $this->dataTestes,
                        ],
                    '2' => [
                            'nrteste' => 2,
                            'nmteste' => 'PL - Confirmação da associação PL',
                            'fileteste' => 'bpplcp\proxylookup-dummy-data-association-confirmation.txt',
                            'endpointteste' =>  config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_CONFIRMATION'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 17,
                            'ncolunasteste' => 10,
                            'dados' =>  $this->dataTestes,
                        ],
                    '3' => [
                            'nrteste' => 3,
                            'nmteste' => 'PL - Consulta lista de aderentes',
                            'fileteste' => 'bpplcp\proxylookup-dummy-data-consulta-aderentes.txt',
                            'endpointteste' =>  config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_CONTACTS'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_CONSULTAS'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 8,
                            'ncolunasteste' => 5,
                            'dados' =>  $this->dataTestes,
                        ],
                    '4' => [
                            'nrteste' => 4,
                            'nmteste' => 'PL - Consulta de IBAN',
                            'fileteste' => 'bpplcp\proxylookup-dummy-data-consulta-iban.txt',
                            'endpointteste' =>  config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_ACCOUNT'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_CONSULTAS'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 9,
                            'ncolunasteste' => 5,
                            'dados' =>  $this->dataTestes,
                        ],
                    '5' => [
                            'nrteste' => 5,
                            'nmteste' => 'PL - Dissociação',
                            'fileteste' => 'bpplcp\proxylookup-dummy-data-dissociation.txt',
                            'endpointteste' => config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_DELETE_ASSOC'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 15,
                            'ncolunasteste' => 7,
                            'dados' =>  $this->dataTestes,
                        ],
                    '8' => [
                            'nrteste' => 8,
                            'nmteste' => 'CoPS - Consulta no nome 1º titular',
                            'fileteste' => 'bpplcp\cops-dummy-data.txt',
                            'endpointteste' =>  config('enums.apibp_dev.endpoints_plcp.COPS'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.COPB'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 12,
                            'ncolunasteste' => 6,
                            'dados' =>  $this->dataTestes,
                        ],
                    '100' => [
                            'nrteste' => 100,
                            'nmteste' => 'Invocação da API Health COP',
                            'fileteste' => '',
                            'endpointteste' =>  config('enums.apibp_dev.endpoints_plcp.COP_HEALTH'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.COPB'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 1,
                            'ncolunasteste' => 0,
                            'dados' =>  [],
                        ],
                    '101' => [
                            'nrteste' => 101,
                            'nmteste' => 'Invocação da API Health PL_GESTAO_HEALTH',
                            'fileteste' => '',
                            'endpointteste' =>  config('enums.apibp_dev.endpoints_plcp.PL_GESTAO_HEALTH'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_GESTAO'),
                            'audience' => config('enums.apibp_dev.resources_plcp.OAUTH2'),
                            'ntestes' => 1,
                            'ncolunasteste' => 0,
                            'dados' =>  [],
                        ],
                    '102' => [
                            'nrteste' => 102,
                            'nmteste' => 'Invocação da API Health PL_CONSULTA_HEALTH',
                            'fileteste' => '',
                            'endpointteste' =>  config('enums.apibp_dev.endpoints_plcp.PL_CONSULTA_HEALTH'),
                            'resourceteste' =>  config('enums.apibp_dev.resources_plcp.PROXYLOOKUP_CONSULTAS'),
                            'audience' => config('enums.apibp.apibp_dev.OAUTH2'),
                            'ntestes' => 1,
                            'ncolunasteste' => 0,
                            'dados' =>  [],
                        ],
                ];
            
                if ( config('app.env') == 'prod' ) {

                    $this->metaDataTestes = [ 
                        '1' => [
                                'nrteste' => 1,
                                'nmteste' => 'PL - Associação',
                                'fileteste' => 'bpplcp\proxylookup-dummy-data-association.txt',
                                'endpointteste' => config('enums.apibp.endpoints_plcp.PL_GESTAO_INSERT_ASSOC'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 31,
                                'ncolunasteste' => 10,
                                'dados' =>  $this->dataTestes,
                            ],
                        '2' => [
                                'nrteste' => 2,
                                'nmteste' => 'PL - Confirmação da associação PL',
                                'fileteste' => 'bpplcp\proxylookup-dummy-data-association-confirmation.txt',
                                'endpointteste' =>  config('enums.apibp.endpoints_plcp.PL_CONSULTA_CONFIRMATION'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 17,
                                'ncolunasteste' => 10,
                                'dados' =>  $this->dataTestes,
                            ],
                        '3' => [
                                'nrteste' => 3,
                                'nmteste' => 'PL - Consulta lista de aderentes',
                                'fileteste' => 'bpplcp\proxylookup-dummy-data-consulta-aderentes.txt',
                                'endpointteste' =>  config('enums.apibp.endpoints_plcp.PL_CONSULTA_CONTACTS'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.PROXYLOOKUP_CONSULTAS'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 8,
                                'ncolunasteste' => 5,
                                'dados' =>  $this->dataTestes,
                            ],
                        '4' => [
                                'nrteste' => 4,
                                'nmteste' => 'PL - Consulta de IBAN',
                                'fileteste' => 'bpplcp\proxylookup-dummy-data-consulta-iban.txt',
                                'endpointteste' =>  config('enums.apibp.endpoints_plcp.PL_CONSULTA_ACCOUNT'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.PROXYLOOKUP_CONSULTAS'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 9,
                                'ncolunasteste' => 5,
                                'dados' =>  $this->dataTestes,
                            ],
                        '5' => [
                                'nrteste' => 5,
                                'nmteste' => 'PL - Dissociação',
                                'fileteste' => 'bpplcp\proxylookup-dummy-data-dissociation.txt',
                                'endpointteste' => config('enums.apibp.endpoints_plcp.PL_GESTAO_DELETE_ASSOC'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 15,
                                'ncolunasteste' => 7,
                                'dados' =>  $this->dataTestes,
                            ],
                        '8' => [
                                'nrteste' => 8,
                                'nmteste' => 'CoPS - Consulta no nome 1º titular',
                                'fileteste' => 'bpplcp\cops-dummy-data.txt',
                                'endpointteste' =>  config('enums.apibp.endpoints_plcp.COPS'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.COPB'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 12,
                                'ncolunasteste' => 6,
                                'dados' =>  $this->dataTestes,
                            ],
                        '100' => [
                                'nrteste' => 100,
                                'nmteste' => 'Invocação da API Health COP',
                                'fileteste' => '',
                                'endpointteste' =>  config('enums.apibp.endpoints_plcp.COP_HEALTH'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.COPB'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 1,
                                'ncolunasteste' => 0,
                                'dados' =>  [],
                            ],
                        '101' => [
                                'nrteste' => 101,
                                'nmteste' => 'Invocação da API Health PL_GESTAO_HEALTH',
                                'fileteste' => '',
                                'endpointteste' =>  config('enums.apibp.endpoints_plcp.PL_GESTAO_HEALTH'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.PROXYLOOKUP_GESTAO'),
                                'audience' => config('enums.apibp.resources_plcp.OAUTH2'),
                                'ntestes' => 1,
                                'ncolunasteste' => 0,
                                'dados' =>  [],
                            ],
                        '102' => [
                                'nrteste' => 102,
                                'nmteste' => 'Invocação da API Health PL_CONSULTA_HEALTH',
                                'fileteste' => '',
                                'endpointteste' =>  config('enums.apibp.endpoints_plcp.PL_CONSULTA_HEALTH'),
                                'resourceteste' =>  config('enums.apibp.resources_plcp.PROXYLOOKUP_CONSULTAS'),
                                'audience' => config('enums.apibp.apibp.OAUTH2'),
                                'ntestes' => 1,
                                'ncolunasteste' => 0,
                                'dados' =>  [],
                            ],
                    ];
              
            }

          
            return $this->metaDataTestes;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function executaTesteBancoPortugal( $metateste )
    {
        \Log::channel($this->logchannel)->info( 'executaTesteBancoPortugal : ' . $metateste['nrteste'] .  ' ' . $metateste['nmteste']  );

        try{


            //open file with dummy data for tests
            $content = fopen(Storage::path( $metateste['fileteste'] ),'r');

            $count = 0;

            while(!feof($content)){
    
                //get line
                $line = fgets($content);

                //replace values in line 
                $line = str_ireplace('{pspcode}', $metateste['dados']['pspcode'], $line );
                $line = str_ireplace('{pspcodeother}', $metateste['dados']['pspcodeother'], $line );
                $line = str_ireplace('{pspcodediferenteiban}', $metateste['dados']['pspcodediferenteiban'], $line );
                $line = str_ireplace('{pspcodeinvalido}', $metateste['dados']['pspcodeinvalido'], $line );
                $line = str_ireplace('{fiscalnumberparticular}', $metateste['dados']['fiscalnumberparticular'], $line );
                $line = str_ireplace('{fiscalnumberparticularother}', $metateste['dados']['fiscalnumberparticularother'], $line );
                $line = str_ireplace('{fiscalnumberparticularother2}', $metateste['dados']['fiscalnumberparticularother2'], $line );
                $line = str_ireplace('{fiscalnumberparticularother3}', $metateste['dados']['fiscalnumberparticularother3'], $line );
                $line = str_ireplace('{fiscalnumberparticularinvalido}', $metateste['dados']['fiscalnumberparticularinvalido'], $line );
                $line = str_ireplace('{fiscalnumberempresa}', $metateste['dados']['fiscalnumberempresa'], $line );
                $line = str_ireplace('{fiscalnumberempresanaoaderente}', $metateste['dados']['fiscalnumberempresanaoaderente'], $line );
                $line = str_ireplace('{fiscalnumberempresainvalido}', $metateste['dados']['fiscalnumberempresainvalido'], $line );
                $line = str_ireplace('{telemovelnacional}', $metateste['dados']['telemovelnacional'], $line );
                $line = str_ireplace('{telemovelnacionalinvalido}', $metateste['dados']['telemovelnacionalinvalido'], $line );
                $line = str_ireplace('{telemovelnacionalsemindicativo}', $metateste['dados']['telemovelnacionalsemindicativo'], $line );
                $line = str_ireplace('{telemovelnacionalempresa}', $metateste['dados']['telemovelnacionalempresa'], $line );
                $line = str_ireplace('{telemovelinternacional}', $metateste['dados']['telemovelinternacional'], $line );
                $line = str_ireplace('{telemovelnacionalnaoassociado}', $metateste['dados']['telemovelnacionalnaoassociado'], $line );
                $line = str_ireplace('{telemovelnacionalparticular}', $metateste['dados']['telemovelnacionalparticular'], $line );
                $line = str_ireplace('{ibanparticular}', $metateste['dados']['ibanparticular'], $line );
                $line = str_ireplace('{ibanparticular2}', $metateste['dados']['ibanparticular2'], $line );
                $line = str_ireplace('{ibanparticular3}', $metateste['dados']['ibanparticular3'], $line );
                $line = str_ireplace('{ibanempresa}', $metateste['dados']['ibanempresa'], $line );
                $line = str_ireplace('{ibanempresa2}', $metateste['dados']['ibanempresa2'], $line );
                $line = str_ireplace('{ibanempresa3}', $metateste['dados']['ibanempresa3'], $line );
                $line = str_ireplace('{ibanparticularvarios}', $metateste['dados']['ibanparticularvarios'], $line );
                
                $line = str_ireplace('{ibanparticularinvalido}', $metateste['dados']['ibanparticularinvalido'], $line );
                $line = str_ireplace('{ibanother}', $metateste['dados']['ibanother'], $line );
                $line = str_ireplace('{listacontatos}', $metateste['dados']['listacontatos'], $line );
                $line = str_ireplace('{listacontatosnenhumaderente}', $metateste['dados']['listacontatosnenhumaderente'], $line );
                $line = str_ireplace('{listacontatosexcede}', $metateste['dados']['listacontatosexcede'], $line );
                $line = str_ireplace('{listacontatosigual}', $metateste['dados']['listacontatosigual'], $line );

        
                //counter
                $count += 1;
                \Log::channel($this->logchannel)->info( str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line );
               
                //separate line by |
                $arrfields = explode('|', $line);
                \Log::channel($this->logchannel)->info( 'LINE : ' . print_r( $arrfields, true) );
                \Log::channel($this->logchannel)->info( 'PARAMETRO : ' . $this->parametro  );

                //verifica nº de colunas no ficheiro de teste
                if ( count( $arrfields) != $metateste['ncolunasteste'] ) {
                    \Log::channel($this->logchannel)->info( 'Line NOT OK . Nº de colunas : ' .   count( $arrfields) );
                    continue;
                }
                //verifica se este teste é para executar
                if ( $this->parametro != 'all' ) {
                    if ( $this->parametro != $arrfields[0] ) { 
                        //nao é para executar
                        \Log::channel($this->logchannel)->info( 'Line does not match test number ['.$this->parametro.'] ['.$arrfields[0].'] '  );
                        continue;
                    }else {
                        //parte o parametro
                        $parte1 = explode( '.', $this->parametro );
                        $parte2 = explode( '.', $arrfields[0] );
                        \Log::channel($this->logchannel)->info( 'Parte 1: ' . print_r($parte1, true) );
                        \Log::channel($this->logchannel)->info( 'Parte 2: ' . print_r($parte2, true) );
                        if( $parte1 != $parte2  ) {
                            \Log::channel($this->logchannel)->info( 'Line does not match test number ['.$this->parametro.'] ['.$arrfields[0].'] '  );
                            continue;
                        }
                    }
                }

                //inicializa campos a vazio
                $this->psp_code = '';
                $this->customer_identifier = '';
                $this->customer_identifier_type = '';
                $this->fiscal_number = '';
                $this->customer_type = '';
                $this->iban = '';
                $this->correlation_id_origin = '';
                $this->timestamp = '';
                $this->phone_book = [];
                $this->ibanparticular2 = '';

                //campos comuns a todos os ficheiros
                $this->testNumber = $arrfields[0]; //1.1 , 1.2, etc
                $this->testDescription = utf8_decode($arrfields[1]);
                
                \Log::channel($this->logchannel)->info( 'Teste number: ' .   $this->testNumber );
                \Log::channel($this->logchannel)->info( 'Meta Teste number: ' .   $metateste['nrteste'] );

                //verifica nr do teste
                switch( $metateste['nrteste'] ){

                    //teste 1
                    case '1':
                        $this->psp_code = strval( $arrfields[2] );
                        $this->customer_identifier = strval($arrfields[3]);
                        $this->customer_identifier_type = $arrfields[4];
                        $this->fiscal_number = strval($arrfields[5]);
                        $this->customer_type = $arrfields[6];
                        $this->iban = strval($arrfields[7]);
                        $this->correlation_id_origin = strval($arrfields[8]);
                        $this->timestamp = $this->setTimestamp();
                        break;
                        
                    //teste 2
                    case '2':
                        $this->psp_code = $arrfields[2];
                        $this->customer_identifier = $arrfields[3];
                        \Log::channel($this->logchannel)->info( 'customer identifier: ' .   $this->customer_identifier );

                        $this->customer_identifier_type = $arrfields[4];
                        $this->fiscal_number = $arrfields[5];
                        $this->customer_type = $arrfields[6];
                        $this->iban = $arrfields[7];
                        try {
                           // $this->ibanparticular2 = $this->dataTestes['ibanparticular2'];
                            $this->ibanparticular2 = config('app.ibanparticular2');
                        }catch(\Exception $e ){
                            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
                            $this->ibanparticular2 = config('app.ibanparticular2');
                        }
                        
                        $this->correlation_id_origin = $arrfields[8];
                        $this->timestamp = $this->setTimestamp();
                        break;

                    //teste 3
                    case '3':
                        $this->psp_code = $arrfields[2];
                        $this->phone_book = $arrfields[3];
                        $this->timestamp = $this->setTimestamp();
                        break;

                    case '4':
                        $this->psp_code = $arrfields[2];
                        $this->customer_identifier = $arrfields[3];
                        $this->timestamp = $this->setTimestamp();
                        break;

                    case '5':
                        $this->psp_code = $arrfields[2];
                        $this->customer_identifier = $arrfields[3];
                        $this->fiscal_number = $arrfields[4];
                        $this->iban = $arrfields[5];
                        $this->timestamp = $this->setTimestamp();
                        break;

                    case '8':
                        \Log::channel($this->logchannel)->info( 'Entrei no init vars ' );

                        $this->psp_code = $arrfields[2];
                        $this->psp_destination_code = $arrfields[3];
                        $this->iban = $arrfields[4];
                        $this->timestamp = $this->setTimestamp();
                        break;

                    default:
                        \Log::channel($this->logchannel)->info(  'Teste nr '. $metateste['nrteste'] . ' não implementado.' );
                }

                //create JSON object
                \Log::channel($this->logchannel)->info(  'Cria payload de testes...' );
                $myObj  = $this->criaPayloadTestes();
                //\Log::channel($this->logchannel)->info( $myObj);

                //encode json object
                $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                \Log::channel($this->logchannel)->info( $myJSON);

                $access_token = '';
                //initialize service
                switch( $metateste['nrteste'] ){
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                        $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
                        //set metateste
                        $apiProxyLookup->setMetaTeste($metateste);
                        //set payload
                        $apiProxyLookup->setPayload($myJSON);
                        //call action 
                        $result = $apiProxyLookup->executaTeste();

                        $access_token = $apiProxyLookup->getAccessTokenUsed();

                        $http_status_recebido = $apiProxyLookup->getHttpStatusCode();

                        \Log::channel($this->logchannel)->info( '***************************' );


                        break;

                    case '8':
                        $apiCop = new ApiCop( $this->logchannel, $this->isConnProduction );
                        //set metateste
                        $apiCop->setMetaTeste($metateste);
                        //set payload
                        $apiCop->setPayload($myJSON);
                        //call action 
                        $result = $apiCop->executaTeste();

                        $access_token = $apiCop->getAccessTokenUsed();

                        $http_status_recebido = $apiCop->getHttpStatusCode();
                     
                        \Log::channel($this->logchannel)->info( '***************************' );

                        break;

                    default:
                        \Log::channel($this->logchannel)->info(  'Teste nr '. $metateste['nrteste'] . ' não implementado.' );
                }


            //array de resultados do teste
            $teste = [
                'meta_dados' => $metateste,
                'test_id' => $this->testNumber,
                'test_description' => $this->testDescription,
                'payload_obj' => $myJSON,
                'resultado' => $result,
                'payload_recebido' => [],
                'http_status_recebido' => $http_status_recebido,
                'correlation_id' => 0,
                'message' => '',
                'error_code' => '',
                'error_message' => '',
                'access_token' => $access_token,
            ];

            array_push($this->testes, $teste); 

               
            }

            fclose($content);

            return $this->testes;
            

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function criaPayloadTestes()
    {
        try{

            
            $myObj  = new \stdClass();
            if ( $this->testNumber == '1.28') { 
                //payload vazio

            }else {
                //psps code
                if ( $this->testNumber == '4.7' || $this->testNumber == '3.4') { 
                    $myObj->{'psp_code'} = '';
                }else {
                    $myObj->{'psp_code'} = $this->psp_code;
                }
                
                if ( substr( $this->testNumber, 0, 1 ) === "3" ) {
                    
                    $myObj->{'phone_book'} = (object) [];
                    $phone = [];
                    //os contatos ja estao carregados aqui : $this->phone_book 
                    \Log::channel($this->logchannel)->info('PhoneBook :'. $this->phone_book);
                    \Log::channel($this->logchannel)->info('PhoneBook COUNT :'. count( explode(';',$this->phone_book) ));
                    
                    $list = [];
                    if ( $this->testNumber == '3.1' ) {
                        // \Log::channel($this->logchannel)->info('AQUIIIIII');
                        $phoneaderente = config('app.telemovelnacional');
                        $list[] = ['phone_number' => $phoneaderente];
                        $phones = explode(';',$this->phone_book);
                        foreach( $phones as $phone ){
                            $arrPhone = ['phone_number' => trim($phone)];
                            array_push($list, $arrPhone);
                        }
                    }elseif ( $this->testNumber == '3.3' ) {
                        $list = [];
                    }elseif( $this->testNumber == '3.2'  ) {

                        // $phoneaderente = config('app.telemovelnacional');
                        // $list[] = ['phone_number' => $phoneaderente];
                        $phones = explode(';',$this->phone_book);
                        foreach( $phones as $phone ){
                            $arrPhone = ['phone_number' => trim($phone)];
                            array_push($list, $arrPhone);
                        }
                    }elseif ( $this->testNumber === '3.7' ) {
                        // $phoneaderente = config('app.telemovelnacional');
                        // $list[] = ['phone_number' => $phoneaderente];
                        $phones = explode(';',$this->phone_book);
                        foreach( $phones as $phone ){
                            $arrPhone = ['phone_number' => trim($phone)];
                            array_push($list, $arrPhone);
                        }
                    }
                    \Log::channel($this->logchannel)->info('PhoneBook # :'. count($list));
                    $myObj->{'phone_book'} = $list;
                    if ( $this->testNumber == '3.5' ) { 

                    }elseif ( $this->testNumber == '3.6' ) { 
                        $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                    }else {
                        if ( config('app.sigla') == 'TVD') {
                            $myObj->{'timestamp'} = $this->getPreviousTimestamp();
                        }else {

                            $myObj->{'timestamp'} = $this->timestamp;
                        }
                    }
                    

                    return $myObj;

                }

                //psps destination code
                if ( $this->testNumber == '8.1' ||  $this->testNumber == '8.2' || $this->testNumber == '8.3'|| $this->testNumber == '8.4' ||  $this->testNumber == '8.5'  ||  $this->testNumber == '8.6'  ||  $this->testNumber == '8.7' ||  $this->testNumber == '8.8' ||  $this->testNumber == '8.9' ||  $this->testNumber == '8.10')  {
                    \Log::channel($this->logchannel)->info( 'Entrei no payload ' . $this->testNumber  );
                   
                    $myObj->{'psp_code_destination'} = $this->psp_destination_code ;
                    $myObj->{'iban'} = $this->iban;
                    if ( $this->testNumber === '8.10' ) {
                        \Log::channel($this->logchannel)->info( 'Entrei sem timestamp ' .  $this->testNumber  );
                    }else {
                        \Log::channel($this->logchannel)->info( 'Entrei no timestamp ' .  $this->testNumber  );
                        if ( config('app.sigla') == 'TVD') {
                            $myObj->{'timestamp'} = $this->getPreviousTimestamp();
                        }else {

                            $myObj->{'timestamp'} = $this->timestamp;
                        }
                    }
       
                } else {
                   
                    if ( $this->testNumber == '4.6' ) {
                        //customer identifier : telemovel ou nipc
                        $myObj->{'customer_identifier'} = '' ;
                    }else {
                        //customer identifier : telemovel ou nipc
                        $myObj->{'customer_identifier'} =  $this->customer_identifier ;
                    }
                   

                    //tipo de customer identifier
                    if ( $this->testNumber == '2.7' ||   $this->testNumber == '2.8' ||  $this->testNumber == '2.3' ||  $this->testNumber == '2.4' ||  $this->testNumber == '2.17' || $this->testNumber == '2.16' || $this->testNumber == '2.15'  || $this->testNumber == '2.14' || $this->testNumber == '2.13' ||$this->testNumber == '2.11'  || $this->testNumber == '2.12' ||$this->testNumber == '2.9'  ||  $this->testNumber == '2.5'  ||$this->testNumber == '5.1'  ||  $this->testNumber == '5.15'  ||$this->testNumber == '5.14' || $this->testNumber == '5.13' || $this->testNumber == '5.12' || $this->testNumber == '5.11' || $this->testNumber == '5.10' || $this->testNumber == '5.9' || $this->testNumber == '5.8' || $this->testNumber == '5.7' ||  $this->testNumber == '5.6' ||  $this->testNumber == '5.5' || $this->testNumber == '5.3' || $this->testNumber == '5.4' || $this->testNumber == '1.9' ||  $this->testNumber == '1.18' ||   $this->testNumber == '2.1' || $this->testNumber == '2.2' || $this->testNumber == '4.1' || $this->testNumber == '4.2' || $this->testNumber == '4.3' || $this->testNumber == '4.4' || $this->testNumber == '4.5' || $this->testNumber == '4.6' || $this->testNumber == '4.7'  || $this->testNumber == '4.8' || $this->testNumber == '4.9'  || $this->testNumber == '8.1') {
                        //nao vai no payload
                    }else {
                        $myObj->{'customer_identifier_type'} = (int)$this->customer_identifier_type ;
                    }
                
                    if ($this->testNumber == '2.7' || $this->testNumber == '2.8' ||  $this->testNumber == '2.3' || $this->testNumber == '2.9' ||  $this->testNumber == '2.17' || $this->testNumber == '2.16' || $this->testNumber == '2.15' ||$this->testNumber == '2.14'||  $this->testNumber == '2.13'  ||$this->testNumber == '2.10'  ||  $this->testNumber == '2.11' || $this->testNumber == '2.12' || $this->testNumber == '2.1' || $this->testNumber == '2.5' || $this->testNumber == '2.2' ||  $this->testNumber == '2.4' ) { 

                        $myObj->{'accounts'} = (object) [];
                        
                        \Log::channel($this->logchannel)->info('test number ' . $this->testNumber);

                        if (  $this->testNumber === '2.1') {
                            \Log::channel($this->logchannel)->info('entrei aqui 0');
                            $list[] = ['iban' => $this->iban];

                        }elseif (  $this->testNumber == '2.5' || $this->testNumber == '2.4' || $this->testNumber == '2.9' || $this->testNumber == '2.3' ||  $this->testNumber == '2.17' || $this->testNumber == '2.16' ||  $this->testNumber === '2.10'  || $this->testNumber == '2.13'  || $this->testNumber == '2.12' ||  $this->testNumber === '2.11' || $this->testNumber == '2.14') {
                           
                            \Log::channel($this->logchannel)->info('entrei aqui 1');

                            $list[] = ['iban' => $this->iban];
                            $arrIban = ['iban' => $this->ibanparticular2];
                            array_push($list, $arrIban);

                        }elseif ( $this->testNumber == '2.8' ) {
                            \Log::channel($this->logchannel)->info('entrei aqui2');
                            $list = []; //fill list wiyh 10 ibans
                            $lista_ibans_10 = config('app.lista_ibans_10');
                            $lista_ibans_10 = explode(',', $lista_ibans_10 );
                            \Log::channel($this->logchannel)->info('Accounts COUNT :'. count( $lista_ibans_10 ) );
                            if ( count($lista_ibans_10) == 10 ) {
                                foreach($lista_ibans_10 as $iban) {
                                    $arrIban = ['iban' => $iban];
                                    array_push($list, $arrIban);
                                }
                            }
                        }elseif ( $this->testNumber == '2.7' ) {
                            \Log::channel($this->logchannel)->info('entrei aqui 3');
                            $list = []; //fill list wiyh 10 ibans
                            $lista_ibans_11 = config('app.lista_ibans_11');
                            $lista_ibans_11 = explode(',', $lista_ibans_11 );
                            \Log::channel($this->logchannel)->info('Accounts COUNT :'. count( $lista_ibans_11 ) );
                            if ( count($lista_ibans_11) > 10 ) {
                                foreach($lista_ibans_11 as $iban) {
                                    $arrIban = ['iban' => $iban];
                                    array_push($list, $arrIban);
                                }
                            }
                        }elseif ( $this->testNumber == '2.15' ) {
                            \Log::channel($this->logchannel)->info('entrei aqui 4');
                            $list = [];
                        }else {
                            \Log::channel($this->logchannel)->info('entrei aqui 5');
                            $list[] = ['iban' => $this->iban];
                        }
                       
                        $myObj->{'accounts'} = $list;

                        if ( $this->testNumber == '2.16' ) {

                        }elseif ( $this->testNumber == '2.17' ) {
                            $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                        }else {
                            if ( config('app.sigla') == 'TVD') {
                                $myObj->{'timestamp'} = $this->getPreviousTimestamp();
                            }else {

                                $myObj->{'timestamp'} = $this->timestamp;
                            }
                        }

            
                        

                    }else {  

                       
                        if ($this->testNumber == '4.7'  ||$this->testNumber == '4.8'  ||  $this->testNumber == '4.1' ||  $this->testNumber == '4.2' || $this->testNumber == '4.3' || $this->testNumber == '4.5' || $this->testNumber == '4.4' || $this->testNumber == '4.6' || $this->testNumber == '4.9'  ) {

                        }else {

                            if ($this->testNumber == '5.1' || $this->testNumber == '5.2' ||  $this->testNumber == '5.15'  ||$this->testNumber == '5.14' || $this->testNumber == '5.13' || $this->testNumber == '5.12' || $this->testNumber == '5.11'  || $this->testNumber == '5.10' || $this->testNumber == '5.9' ||  $this->testNumber == '5.8' ||$this->testNumber == '5.7' ||  $this->testNumber == '5.6' || $this->testNumber == '5.5' || $this->testNumber == '5.3' || $this->testNumber == '5.4' ) {
                                //nif ou nipc
                                $myObj->{'fiscal_number'} = $this->fiscal_number;
                                //iban
                                $myObj->{'iban'} = $this->iban;
                            }else {
                                  //nif ou nipc
                                $myObj->{'fiscal_number'} = $this->fiscal_number;
                                //tipo de customer : singular / coletivo
                                $myObj->{'customer_type'} = (int)$this->customer_type;
                                //iban
                                $myObj->{'iban'} = $this->iban;
                            }

                           
                        }

                       
                        //timestamp
                        if ( $this->testNumber == '4.8'  || $this->testNumber == '5.13'  ||  $this->testNumber == '1.23') {
                            //nao vai no payload
                        }else {
                            if ( $this->testNumber == '1.24'  || $this->testNumber == '5.14' || $this->testNumber == '4.9'  ) {
                                $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                            }else {

                                if (  $this->testNumber == '5.15'  ) {
                                    $myObj->{'timestamp'} = $this->setPreviousTimestamp();
                                }else {

                                     if ( config('app.sigla') == 'TVD') {
                                        $myObj->{'timestamp'} = $this->getPreviousTimestamp();
                                    }else {

                                        $myObj->{'timestamp'} = $this->timestamp;
                                    }
                                }
                            }
                        }
                    }

                }
            }

            return $myObj;

          }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function executaTesteHealthBancoPortugal( $metateste )
    {
        \Log::channel($this->logchannel)->info( 'executaTesteHealthBancoPortugal : ' . $metateste['nrteste'] .  ' ' . $metateste['nmteste']  );

        try{
            
            $audience = $metateste['endpointteste'];
            \Log::channel($this->logchannel)->info('Audience: ' . $audience);

            $method = 'GET';
            $payload = [];
            $headers = ['accept: text/plain'];
            $requestContent = [];

            $result =  $this->makeCurlCall2( $audience,  $requestContent, $headers, $method , $payload );
            
            $content = $result['content'];
            $httpcode = $result['httpcode'];

            //array de resultados do teste
            $teste = [
                'meta_dados' => $metateste,
                'test_id' => $metateste['nrteste'],
                'test_description' => $metateste['nmteste'],
                'payload_obj' => '',
                'resultado' => $content,
                'payload_recebido' => $result,
                'http_status_recebido' =>  $httpcode,
                'correlation_id' => 0,
                'message' => '',
                'error_code' => '',
                'error_message' => '',
            ];

            array_push($this->testes, $teste); 

            return $this->testes;
            

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }


    private function getListaContatosHomebanking( $tipo )
    {
        try {
            \Log::channel($this->logchannel)->info('vai buscar a lista de contatos dos dest freqs');
            $listacontatos = [];
            switch( $tipo ){
                case 1: 
                    $listacontatos = DestinatariosFrequentes::whereNotNull('telefone')->where('activo', 'S')->distinct('telefone')->get(['telefone'])->take(10)->toArray();
                    break;
                case 2: 
                    $listacontatos = DestinatariosFrequentes::whereNotNull('telefone')->where('activo', 'S')->distinct('telefone')->get(['telefone'])->take(60)->toArray();
                    break;
                case 3: 
                    $listacontatos = DestinatariosFrequentes::whereNotNull('telefone')->where('activo', 'S')->distinct('telefone')->get(['telefone'])->take(54)->toArray();
                    break;
                default: 
                    $listacontatos = [];
            }

            
            //\Log::channel($this->logchannel)->info('Lista: ' . print_r($listacontatos,true));
            \Log::channel($this->logchannel)->info('Lista #' .count($listacontatos));
            $lista = '';
            $i=0;
            $j=1;
            foreach($listacontatos as $contacto){
             
                if($i < 60 ) {
                   
                    if( trim($contacto['telefone']) != '' && strlen(trim($contacto['telefone'])) == 13){
                       
                        $pattern = "/^\\+351[1-9][0-9]{8}$/";
                        if ( preg_match ($pattern, $contacto['telefone']) ) {
                            $lista .= trim($contacto['telefone']) . ';';
                            $i++;
                            \Log::channel($this->logchannel)->info($j . ' de ' . $i . ' Telefone ADDED : ' . trim($contacto['telefone']) );
                        }else {
                            \Log::channel($this->logchannel)->info($j . ' de ' . $i . ' Telefone NOT ADDED : ' . trim($contacto['telefone']) );
                        }

                    }else {
                        \Log::channel($this->logchannel)->info($j . ' de ' . $i . ' Telefone NOT ADDED : ' . trim($contacto['telefone']) );
                    }
                }
                $j++;
            }
            \Log::channel($this->logchannel)->info('Lista: ' . $lista);
            \Log::channel($this->logchannel)->info('Contador: ' . $i);
            return rtrim($lista,';');

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            \Log::channel($this->logchannel)->info('vai devolver uma lista de contatos vazia');
            return [];
        }
    }

    private function executaTesteBancoPortugal_1()
    {
        try{

           
            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
   
            $content = fopen(Storage::path('public\bp\proxylookup-dummy-data-association.txt'),'r');

            $count = 0;

            while(!feof($content)){
    
                //get line
                $line = fgets($content);
    
                //replace values from env
                $line = str_ireplace('{pspcode}', $pspcode, $line );
                $line = str_ireplace('{pspcodediferenteiban}', $pspcodediferenteiban, $line );
                $line = str_ireplace('{fiscalnumberparticular}', $fiscalnumberparticular, $line );
                $line = str_ireplace('{fiscalnumberempresa}', $fiscalnumberempresa, $line );
                $line = str_ireplace('{telemovelnacional}', $telemovelnacional, $line );
                $line = str_ireplace('{telemovelnacionalempresa}', $telemovelnacionalempresa, $line );
                $line = str_ireplace('{telemovelinternacional}', $telemovelinternacional, $line );
                $line = str_ireplace('{telemovelnacionalnaoassociado}', $telemovelnacionalnaoassociado, $line );
                $line = str_ireplace('{telemovelnacionalparticular}', $telemovelnacionalparticular, $line );
                $line = str_ireplace('{ibanparticular}', $ibanparticular, $line );
                $line = str_ireplace('{ibanempresa}', $ibanempresa, $line );

                $count += 1;
                \Log::channel($this->logchannel)->info( str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line );
                
                //separate line by |
                $arrfields = explode('|', $line);
                if ( count( $arrfields) != 10 ) {
                    \Log::channel($this->logchannel)->info( 'Line NOT OK . Nº de colunas : ' .   count( $arrfields) );
                    continue;
                }
                
                $this->endpoint = 'https://wwwcert.bportugal.net/apigw/pl/mgmt/insert';
                $this->testNumber = $arrfields[0];
                $this->testDescription = utf8_decode($arrfields[1]);
                $this->psp_code = $arrfields[2];
                $this->customer_identifier = $arrfields[3];
                $this->customer_identifier_type = $arrfields[4];
                $this->fiscal_number = $arrfields[5];
                $this->customer_type = $arrfields[6];
                $this->iban = $arrfields[7];
                $this->correlation_id_origin = $arrfields[8];
                $this->timestamp = $this->setTimestamp();

                 //create JSON object
                 $myObj  = new \stdClass();
       
                
                if( trim($this->psp_code) != '' ) {
                    $myObj->{'psp_code'} = $this->psp_code;
                }else{
                    if ($this->testNumber == '1.26') {
                        $myObj->{'psp_code'} = '';
                    }
                }
               

                if( trim($this->customer_identifier) != '' ) {
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                // }elseif ($this->testNumber = '1.5') {
                //     $myObj->{'customer_identifier'} = $this->customer_identifier ;
                }

                if( trim($this->customer_identifier_type) != '' ) {
                    $myObj->{'customer_identifier_type'} = $this->customer_identifier_type ;
                }

                if( trim($this->fiscal_number) != '' ) {
                    $myObj->{'fiscal_number'} = $this->fiscal_number;
                }

                if( trim($this->customer_type) != '' ) {
                    $myObj->{'customer_type'} = $this->customer_type;
                }

                if( trim($this->iban) != '' ) {
                    $myObj->{'iban'} = $this->iban;
                }
                if( trim($this->correlation_id_origin) != '' ) {
                    $myObj->{'correlation_id_origin'} = $this->correlation_id_origin;
                }

                if ($this->testNumber != '1.23' ) {
                    if ($this->testNumber == '1.24') {
                        $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                    } elseif ($this->testNumber == '1.28') {
                        //empty
                    }else { 
                        if( trim($this->timestamp) != '' ) {
                            $myObj->{'timestamp'} = $this->timestamp;
                        } 
                    }
                }
                

                $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $myJSON = json_encode( $myObj,true);

                // //cria dados para envio
                // $data = [
                //     'psp_code' => $this->psp_code, 
                //     'customer_identifier' => $this->customer_identifier,
                //     'customer_identifier_type' => $this->customer_identifier_type,
                //     'fiscal_number' => $this->fiscal_number,
                //     'customer_type' => $this->customer_type,
                //     'iban' => $this->iban,
                //     'correlation_id_origin' => $this->correlation_id_origin,
                //     'timestamp' => $this->timestamp
                // ];
                // //encode data to json 
                // $jsonPayloadTotal = json_encode($data,JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                // //cria dados para envio
                // $data = [ $straux  ];

                // //encode data to json 
                // $jsonPayload = json_encode($data);

                //set variables
                //$apiProxyLookup->setData($data);
                $apiProxyLookup->setPayload($myJSON);

                //call action 
                $result = $apiProxyLookup->executaTestesPL();

                //array de resultados do teste
                $teste = [
                    'endpoint' => $this->endpoint,
                    'test_id' => $this->testNumber,
                    'test_description' => $this->testDescription,
                    // 'payload_enviado' =>  print_r($jsonPayload, true),
                    // 'payload_total' =>  print_r($jsonPayloadTotal, true),
                    'payload_obj' =>  $myJSON,
                    'resultado' => $result,
                    'payload_recebido' => [],
                    'http_status_recebido' => 0,
                    'correlation_id' => 0,
                    'message' => '',
                    'error_code' => '',
                    'error_message' => ''
                ];

                array_push($this->testes, $teste); 
               
            }
    
            fclose($content);

           // $lines = File::get(storage_path(''));

            //le ficheiro de texto com dados de teste
            //$lines = file('bp/proxylookup-dummy-data.txt');
            // $count = 0;

            // foreach($lines as $line) {
               
              

            // }

            //\Log::channel($this->logchannel)->info( print_r($this->testes, true) );

            return $this->testes;
            

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function executaTesteBancoPortugal_2()
    {
        try{

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
   
            $content = fopen(Storage::path('public\bp\proxylookup-dummy-data-association-confirmation.txt'),'r');

            $count = 0;

            while(!feof($content)){
    
                $line = fgets($content);
    
                $count += 1;
                \Log::channel($this->logchannel)->info( str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line );
                
                $arrfields = explode('|', $line);

                //\Log::channel($this->logchannel)->info( ' Nº de colunas : ' .   count( $arrfields));

                if ( count( $arrfields) != 10 ) {
                    \Log::channel($this->logchannel)->info( 'Line NOT OK . Nº de colunas : ' .   count( $arrfields) );
                    continue;
                }
                
                $this->endpoint = 'https://wwwcert.bportugal.net/apigw/pl/lookup/confirmation';
                $this->testNumber = $arrfields[0];
                $this->testDescription = utf8_decode($arrfields[1]);
                $this->psp_code = $arrfields[2];
                $this->customer_identifier = $arrfields[3];
                $this->customer_identifier_type = $arrfields[4];
                $this->fiscal_number = $arrfields[5];
                $this->customer_type = $arrfields[6];
                $this->iban = $arrfields[7];
                $this->correlation_id_origin = $arrfields[8];
                $this->timestamp = $this->setTimestamp();

                 //create JSON object
                 $myObj  = new \stdClass();
       
                
                if( trim($this->psp_code) != '' ) {
                    $myObj->{'psp_code'} = $this->psp_code;
                }else{
                    if ($this->testNumber == '1.26') {
                        $myObj->{'psp_code'} = '';
                    }
                }
               

                if( trim($this->customer_identifier) != '' ) {
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                // }elseif ($this->testNumber = '1.5') {
                //     $myObj->{'customer_identifier'} = $this->customer_identifier ;
                }

                if( trim($this->customer_identifier_type) != '' ) {
                    $myObj->{'customer_identifier_type'} = $this->customer_identifier_type ;
                }

                if( trim($this->fiscal_number) != '' ) {
                    $myObj->{'fiscal_number'} = $this->fiscal_number;
                }

                if( trim($this->customer_type) != '' ) {
                    $myObj->{'customer_type'} = $this->customer_type;
                }

                if( trim($this->iban) != '' ) {
                    $myObj->{'iban'} = $this->iban;
                }
                if( trim($this->correlation_id_origin) != '' ) {
                    $myObj->{'correlation_id_origin'} = $this->correlation_id_origin;
                }

                if ($this->testNumber != '1.23' ) {
                    if ($this->testNumber == '1.24') {
                        $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                    } elseif ($this->testNumber == '1.28') {
                        //empty
                    }else { 
                        if( trim($this->timestamp) != '' ) {
                            $myObj->{'timestamp'} = $this->timestamp;
                        } 
                    }
                }
                

                $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $myJSON = json_encode( $myObj,true);

                // //cria dados para envio
                // $data = [
                //     'psp_code' => $this->psp_code, 
                //     'customer_identifier' => $this->customer_identifier,
                //     'customer_identifier_type' => $this->customer_identifier_type,
                //     'fiscal_number' => $this->fiscal_number,
                //     'customer_type' => $this->customer_type,
                //     'iban' => $this->iban,
                //     'correlation_id_origin' => $this->correlation_id_origin,
                //     'timestamp' => $this->timestamp
                // ];
                // //encode data to json 
                // $jsonPayloadTotal = json_encode($data,JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                // //cria dados para envio
                // $data = [ $straux  ];

                // //encode data to json 
                // $jsonPayload = json_encode($data);

                //set variables
                //$apiProxyLookup->setData($data);
                $apiProxyLookup->setPayload($myJSON);

                //call action 
                $result = $apiProxyLookup->executaTestesPL();

                //array de resultados do teste
                $teste = [
                    'endpoint' => $this->endpoint,
                    'test_id' => $this->testNumber,
                    'test_description' => $this->testDescription,
                    // 'payload_enviado' =>  print_r($jsonPayload, true),
                    // 'payload_total' =>  print_r($jsonPayloadTotal, true),
                    'payload_obj' =>  $myJSON,
                    'resultado' => $result,
                    'payload_recebido' => [],
                    'http_status_recebido' => 0,
                    'correlation_id' => 0,
                    'message' => '',
                    'error_code' => '',
                    'error_message' => ''
                ];

                array_push($this->testes, $teste); 
               
            }
    
            fclose($content);

           // $lines = File::get(storage_path(''));

            //le ficheiro de texto com dados de teste
            //$lines = file('bp/proxylookup-dummy-data.txt');
            // $count = 0;

            // foreach($lines as $line) {
               
              

            // }

            //\Log::channel($this->logchannel)->info( print_r($this->testes, true) );

            return $this->testes;
            

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function executaTesteBancoPortugal_3()
    {
        try{
  
            
              //initialize service
              $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
     
              $content = fopen(Storage::path('public\bp\proxylookup-dummy-data-consulta-aderentes.txt'),'r');
  
              $count = 0;
  
              while(!feof($content)){
      
                  $line = fgets($content);
      
                  $count += 1;
                  \Log::channel($this->logchannel)->info( str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line );
                  
                  $arrfields = explode('|', $line);
  
                  //\Log::channel($this->logchannel)->info( ' Nº de colunas : ' .   count( $arrfields));
  
                  if ( count( $arrfields) != 5 ) {
                      \Log::channel($this->logchannel)->info( 'Line NOT OK . Nº de colunas : ' .   count( $arrfields) );
                      continue;
                  }
  
                  $this->endpoint = 'https://wwwcert.bportugal.net/apigw/pl/lookup/contacts';
                  $this->testNumber = $arrfields[0];
                  $this->testDescription = utf8_decode($arrfields[1]);
                  $this->psp_code = $arrfields[2];
                  $this->phone_book = $arrfields[3];
                  $this->timestamp = $this->setTimestamp();
  
                   //   'body' => "{
                    //     "psp_code": "string",
                    //     "phone_book": [
                    //       {
                    //         "phone_number": "string"
                    //       }
                    //     ],
                    //     "timestamp": "string"
                    //   }",

                   //create JSON object
                   $myObj  = new \stdClass();
         
                  if( trim($this->psp_code) != '' ) {
                      $myObj->{'psp_code'} = $this->psp_code;
                  }else {
                      if( $this->testNumber == '3.4' ) {
                          $myObj->{'psp_code'} = '';
                      }
                  }

                  $phones = [];
                  if( trim($this->phone_book) != '' ) {

                    $phone_book = explode(';', $this->phone_book);

                    foreach( $phone_book as $phone) {

                        //$arr[] = ['id' => '9999', 'name' => 'Name'];
                        $myObj2  = new \stdClass();
                        $myObj2->{'phone_number'} = $phone;
                        $myJSON2 = json_encode( $myObj2,true);
                        array_push($phones, $myJSON2);
                    }

                    $myObj->{'phone_book'} = $phones;
                    $myObj->{'phone_book_count'} = count($phones);

                }
                else 
                {
                    if ($this->testNumber == '3.3') {
                        $myObj->{'phone_book'} = $phones;
                        $myObj->{'phone_book_count'} = count($phones);
                    }

                    if ($this->testNumber == '3.7') {
                        for($i=0;$i<501;$i++){
   
                            $myObj2  = new \stdClass();
                            $myObj2->{'phone_number'} = '+351964255708';
                            $myJSON2 = json_encode( $myObj2,true);
                            array_push($phones, $myJSON2);
                        }
                        $myObj->{'phone_book'} = $phones;
                        $myObj->{'phone_book_count'} = count($phones);
                    }

                    if ($this->testNumber == '3.8') {
                        
                        for($i=0;$i<500;$i++){
                            $myObj2  = new \stdClass();
                            $myObj2->{'phone_number'} = '+351964255708';
                            $myJSON2 = json_encode( $myObj2,true);
                            array_push($phones, $myJSON2);
                        }
                        $myObj->{'phone_book'} = $phones;
                        $myObj->{'phone_book_count'} = count($phones);
                    }
                }
 
                  if( $this->testNumber != '3.5' ) {
                      if ($this->testNumber == '3.6') {
                          $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                      }else {
                          if( trim($this->timestamp) != '' ) {
                              $myObj->{'timestamp'} = $this->timestamp;
                          } 
                      }
                  }
                  
                  $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                  $myJSON = json_encode( $myObj,true);
  
                  //set variables
                  //$apiProxyLookup->setData($data);
                  $apiProxyLookup->setPayload($myJSON);
  
                  //call action 
                  $result = $apiProxyLookup->executaTestesPL();
  
                  //array de resultados do teste
                  $teste = [
                      'endpoint' => $this->endpoint,
                   
                      'test_id' => $this->testNumber,
                      'test_description' => $this->testDescription,
                      // 'payload_enviado' =>  print_r($jsonPayload, true),
                      // 'payload_total' =>  print_r($jsonPayloadTotal, true),
                      'payload_obj' =>  $myJSON,
                      'resultado' => $result,
                      'payload_recebido' => [],
                      'http_status_recebido' => 0,
                      'correlation_id' => 0,
                      'message' => '',
                      'error_code' => '',
                      'error_message' => ''
                  ];
  
                  array_push($this->testes, $teste); 
                 
              }
      
              fclose($content);
  
              return $this->testes;
              
  
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function executaTesteBancoPortugal_4()
    {
        try{

          
            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
   
            $content = fopen(Storage::path('public\bp\proxylookup-dummy-data-consulta-iban.txt'),'r');

            $count = 0;

            while(!feof($content)){
    
                $line = fgets($content);
    
                $count += 1;
                \Log::channel($this->logchannel)->info( str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line );
                
                $arrfields = explode('|', $line);

                //\Log::channel($this->logchannel)->info( ' Nº de colunas : ' .   count( $arrfields));

                if ( count( $arrfields) != 5 ) {
                    \Log::channel($this->logchannel)->info( 'Line NOT OK . Nº de colunas : ' .   count( $arrfields) );
                    continue;
                }

                $this->endpoint = 'https://wwwcert.bportugal.net/apigw/pl/lookup/account';
                $this->testNumber = $arrfields[0];
                $this->testDescription = utf8_decode($arrfields[1]);
                $this->psp_code = $arrfields[2];
                $this->customer_identifier = $arrfields[3];
                $this->timestamp = $this->setTimestamp();

                 //create JSON object
                 $myObj  = new \stdClass();
       
                if( trim($this->psp_code) != '' ) {
                    $myObj->{'psp_code'} = $this->psp_code;
                }else {
                    if( $this->testNumber == '4.7' ) {
                        $myObj->{'psp_code'} = '';
                    }
                }
                if( trim($this->customer_identifier) != '' ) {
                    $myObj->{'customer_identifier'} = $this->customer_identifier;
                }else {
                    if( $this->customer_identifier == '4.6' ) {
                        $myObj->{'customer_identifier'} = '';
                    }
                }
     
                if( $this->testNumber != '4.8' ) {
                    if ($this->testNumber == '4.9') {
                        $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                    }else {
                        if( trim($this->timestamp) != '' ) {
                            $myObj->{'timestamp'} = $this->timestamp;
                        } 
                    }
                }
                
                $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $myJSON = json_encode( $myObj,true);

                //set variables
                //$apiProxyLookup->setData($data);
                $apiProxyLookup->setPayload($myJSON);

                //call action 
                $result = $apiProxyLookup->executaTestesPL();

                //array de resultados do teste
                $teste = [
                    'endpoint' => $this->endpoint,
                    'test_id' => $this->testNumber,
                    'test_description' => $this->testDescription,
                    // 'payload_enviado' =>  print_r($jsonPayload, true),
                    // 'payload_total' =>  print_r($jsonPayloadTotal, true),
                    'payload_obj' =>  $myJSON,
                    'resultado' => $result,
                    'payload_recebido' => [],
                    'http_status_recebido' => 0,
                    'correlation_id' => 0,
                    'message' => '',
                    'error_code' => '',
                    'error_message' => ''
                ];

                array_push($this->testes, $teste); 
               
            }
    
            fclose($content);

            return $this->testes;
            

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function executaTesteBancoPortugal_5()
    {
        try{

            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
   
            $pspcode = config('app.pspcode');
            $pspcodediferenteiban = config('app.pspcodediferenteiban');
            $fiscalnumberparticular = config('app.fiscalnumberparticular');
            $fiscalnumberempresa = config('app.fiscalnumberempresa');
            $telemovelnacional = config('app.telemovelnacional');
            $telemovelnacionalparticular = config('app.telemovelnacionalparticular');
            $telemovelnacionalempresa = config('app.telemovelnacionalempresa');
            $telemovelinternacional = config('app.telemovelinternacional');
            $telemovelnacionalnaoassociado = config('app.telemovelnacionalnaoassociado');
            $ibanparticular = config('app.ibanparticular');
            $ibanempresa = config('app.ibanempresa');

            $content = fopen(Storage::path('public\bp\proxylookup-dummy-data-dissociation.txt'),'r+');

            $count = 0;

            while(!feof($content)){
    
                $line = fgets($content);

                $line = str_ireplace('{pspcode}', $pspcode, $line );
                $line = str_ireplace('{pspcodediferenteiban}', $pspcodediferenteiban, $line );
                $line = str_ireplace('{fiscalnumberparticular}', $fiscalnumberparticular, $line );
                $line = str_ireplace('{fiscalnumberempresa}', $fiscalnumberempresa, $line );
                $line = str_ireplace('{telemovelnacional}', $telemovelnacional, $line );
                $line = str_ireplace('{telemovelnacionalempresa}', $telemovelnacionalempresa, $line );
                $line = str_ireplace('{telemovelinternacional}', $telemovelinternacional, $line );
                $line = str_ireplace('{telemovelnacionalnaoassociado}', $telemovelnacionalnaoassociado, $line );
                $line = str_ireplace('{telemovelnacionalparticular}', $telemovelnacionalparticular, $line );
                $line = str_ireplace('{ibanparticular}', $ibanparticular, $line );
                $line = str_ireplace('{ibanempresa}', $ibanempresa, $line );

                $count += 1;
                \Log::channel($this->logchannel)->info( str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line );
                
                $arrfields = explode('|', $line);

                if ( count( $arrfields) != 7 ) {
                    \Log::channel($this->logchannel)->info( 'Line NOT OK . Nº de colunas : ' .   count( $arrfields) );
                    continue;
                }
                
                $this->endpoint = 'https://wwwcert.bportugal.net/apigw/pl/mgmt/delete';
                $this->testNumber = $arrfields[0];
                $this->testDescription = utf8_decode($arrfields[1]);
                //body fields
                $this->psp_code = $arrfields[2];
                $this->customer_identifier = $arrfields[3];
                $this->fiscal_number = $arrfields[4];
                $this->iban = $arrfields[5];
                $this->timestamp = $this->setTimestamp();

                 //create JSON object
                 $myObj  = new \stdClass();
 
                if( trim($this->psp_code) != '' ) {
                    if ($this->testNumber == '5.10') {
                        $myObj->{'psp_code'} = '';
                    }else {
                        $myObj->{'psp_code'} = $this->psp_code;
                    }
                }

                if( trim($this->customer_identifier) != '' ) {
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                }elseif ($this->testNumber = '5.9') {
                    $myObj->{'customer_identifier'} = $this->customer_identifier ;
                }

                if( trim($this->fiscal_number) != '' ) {
                    $myObj->{'fiscal_number'} = $this->fiscal_number;
                }elseif ($this->testNumber = '5.12') {
                    $myObj->{'fiscal_number'} = $this->fiscal_number ;
                }

                if( trim($this->iban) != '' ) {
                    $myObj->{'iban'} = $this->iban;
                } elseif ($this->testNumber = '5.11') {
                    $myObj->{'iban'} = $this->iban ;
                }
             
                if ($this->testNumber != '5.13' ) {
                    if ($this->testNumber == '5.14') {
                        $myObj->{'timestamp'} = $this->setAdvancedTimestamp();
                    } elseif ($this->testNumber == '5.15') {
                        $myObj->{'timestamp'} = $this->setPreviousTimestamp();
                    }else { 
                        if( trim($this->timestamp) != '' ) {
                            $myObj->{'timestamp'} = $this->timestamp;
                        } 
                    }
                }
                

                $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $myJSON = json_encode( $myObj,true);

                // //cria dados para envio
                // $data = [
                //     'psp_code' => $this->psp_code, 
                //     'customer_identifier' => $this->customer_identifier,
                //     'customer_identifier_type' => $this->customer_identifier_type,
                //     'fiscal_number' => $this->fiscal_number,
                //     'customer_type' => $this->customer_type,
                //     'iban' => $this->iban,
                //     'correlation_id_origin' => $this->correlation_id_origin,
                //     'timestamp' => $this->timestamp
                // ];
                // //encode data to json 
                // $jsonPayloadTotal = json_encode($data,JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                // //cria dados para envio
                // $data = [ $straux  ];

                // //encode data to json 
                // $jsonPayload = json_encode($data);

                //set variables
                //$apiProxyLookup->setData($data);
                $apiProxyLookup->setPayload($myJSON);

                //call action 
                $result = $apiProxyLookup->executaTestesPL();

                //array de resultados do teste
                $teste = [
                    'endpoint' => $this->endpoint,
                    'test_id' => $this->testNumber,
                    'test_description' => $this->testDescription,
                    // 'payload_enviado' =>  print_r($jsonPayload, true),
                    // 'payload_total' =>  print_r($jsonPayloadTotal, true),
                    'payload_obj' =>  $myJSON,
                    'resultado' => $result,
                    'payload_recebido' => [],
                    'http_status_recebido' => 0,
                    'correlation_id' => 0,
                    'message' => '',
                    'error_code' => '',
                    'error_message' => ''
                ];

                array_push($this->testes, $teste); 
               
            }
    
            fclose($content);

           // $lines = File::get(storage_path(''));

            //le ficheiro de texto com dados de teste
            //$lines = file('bp/proxylookup-dummy-data.txt');
            // $count = 0;

            // foreach($lines as $line) {
               
              

            // }

            //\Log::channel($this->logchannel)->info( print_r($this->testes, true) );

            return $this->testes;
            

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
    }

    private function executaTesteBancoPortugal_8()
    {
        try{

          
            //initialize service
            $apiProxyLookup = new ApiProxyLookup( $this->logchannel, $this->isConnProduction );
   
            $content = fopen(Storage::path('public\bp\cops-dummy-data.txt'),'r');

            $count = 0;

            while(!feof($content)){
    
                $line = fgets($content);
    
                $count += 1;
                \Log::channel($this->logchannel)->info( str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line );
                
                $arrfields = explode('|', $line);

                //\Log::channel($this->logchannel)->info( ' Nº de colunas : ' .   count( $arrfields));

                if ( count( $arrfields) != 6 ) {
                    \Log::channel($this->logchannel)->info( 'Line NOT OK . Nº de colunas : ' .   count( $arrfields) );
                    continue;
                }

                $this->endpoint = 'https://wwwcert.bportugal.net/apigw/conp/cops';
                $this->testNumber = $arrfields[0];
                $this->testDescription = utf8_decode($arrfields[1]);
                $this->psp_code = $arrfields[2];
                $this->psp_destination_code = $arrfields[3];
                $this->iban = $arrfields[4];
                $this->timestamp = $this->setTimestamp();

                 //create JSON object
                 $myObj  = new \stdClass();
       
                if( trim($this->psp_code) != '' ) {
                    $myObj->{'psp_code'} = $this->psp_code;
                }else {
                    if( $this->testNumber == '8.8' ) {
                        $myObj->{'psp_code'} = '';
                    }
                }
               
                if( trim($this->psp_destination_code) != '' ) {
                    $myObj->{'psp_destination_code'} = $this->psp_destination_code ;
                }else {
                    if( $this->testNumber == '8.7' ) {
                        $myObj->{'psp_destination_code'} = '';
                    }
                }

                if( trim($this->iban) != '' ) {
                    $myObj->{'iban'} = $this->iban;
                }else {
                    if( $this->testNumber == '8.9' ) {
                        $myObj->{'iban'} = '';
                    }
                }

                if( $this->testNumber != '8.10' ) {
                    if( trim($this->timestamp) != '' ) {
                        $myObj->{'timestamp'} = $this->timestamp;
                    } 
                }
                
                $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $myJSON = json_encode( $myObj,true);

                //set variables
                //$apiProxyLookup->setData($data);
                $apiProxyLookup->setPayload($myJSON);

                //call action 
                $result = $apiProxyLookup->executaTestesPL();

                //array de resultados do teste
                $teste = [
                    'endpoint' => $this->endpoint,
                    'test_id' => $this->testNumber,
                    'test_description' => $this->testDescription,
                    // 'payload_enviado' =>  print_r($jsonPayload, true),
                    // 'payload_total' =>  print_r($jsonPayloadTotal, true),
                    'payload_obj' =>  $myJSON,
                    'resultado' => $result,
                    'payload_recebido' => [],
                    'http_status_recebido' => 0,
                    'correlation_id' => 0,
                    'message' => '',
                    'error_code' => '',
                    'error_message' => ''
                ];

                array_push($this->testes, $teste); 
               
            }
    
            fclose($content);

            return $this->testes;
            

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            throw $e;
        }
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

        return $this->timestamp;

    }

    public function setAdvancedTimestamp()
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
   
        return $nowFormatted ;

    }

    public function setPreviousTimestamp()
    {
        /*
        * The `DateTime` constructor doesn't create objects with fractional seconds.
        * However, the static method `DateTime::createFromFormat()` does include the
        * fractional seconds in the object.  Finally, since ISO 8601 specifies only
        * millisecond precision, remove the last three decimal places from the timestamp.
        */
        // DateTime object with microseconds
        $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', '')); 

        $datetime =  $now->modify('-1 year');

        // Truncate to milliseconds
        $nowFormatted = substr($datetime->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'; 
   
        return $nowFormatted ;

    }
    
    public function setPreviousTimestampCorrect()
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

    /*
    |--------------------------------------------------------------------------
    | SERVICE IBAN
    |--------------------------------------------------------------------------
    */ 

    private function executeTestIBAN()
    {
        \Log::channel($this->logchannel)->info('TestService::executeTestIBAN...com parametro : ' . $this->parametro);
        \Log::channel($this->logchannel)->info('TestService::executeTestIBAN...execucao dos testes : ' . print_r( $this->executaTesteIBAN,true));
        try{

            $executaTeste1 = filter_var( $this->executaTesteIBAN[0] , FILTER_VALIDATE_BOOLEAN );
            //$executaTeste2 = filter_var( $this->executaTesteIBAN[1] , FILTER_VALIDATE_BOOLEAN );
            $executaTeste1 = true;
            $executaTeste2 = false;
            if ( trim($this->parametro) == 'all' ) {
                $executaTeste1 = false;
                $executaTeste2 = true;
            }
            \Log::channel($this->logchannel )->info( 'TESTE Nº1 : IBAN... ' . $this->parametro);
            \Log::channel($this->logchannel )->info( 'EXECUTA TESTE Nº1...' . ($executaTeste1 ? 'SIM': 'NÃO'));

            \Log::channel($this->logchannel )->info( 'TESTE Nº2 : IBAN... ' . $this->parametro);
            \Log::channel($this->logchannel )->info( 'EXECUTA TESTE Nº2...' . ($executaTeste2 ? 'SIM': 'NÃO'));


            if ( $executaTeste1 )  $executaTeste1 = $this->getNomePrimeiroTitular();
            if ( $executaTeste2 )  $executaTeste2 = $this->getNomePrimeiroTitularFromPetc();

            if ( trim($this->parametro) == 'all' ) {
                return $executaTeste2;
            }
            
            return $executaTeste1;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return false;
        }
    }
    
    private function getNomePrimeiroTitular()
    {
        \Log::channel( $this->logchannel )->info( '::::::::::::getNomePrimeiroTitular:::::::::::::::::::');
        \Log::channel( $this->logchannel )->info( 'EXECUTA TESTE Nº1...');
        try{

         //initialize service
         $apiCop = new ApiCop( $this->logchannel, $this->isConnProduction );
            
         //set variables
         $this->iban = trim( $this->parametro );

         $apiCop->setIban( $this->iban );

         //call action 
         return $apiCop->getNomeFromIBAN();

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return 'erro';
        }
    }

    private function getNomePrimeiroTitularFromPetc()
    {
        \Log::channel( $this->logchannel )->info( '::::::::::::getNomePrimeiroTitularFromPetc:::::::::::::::::::');
        \Log::channel( $this->logchannel )->info( 'EXECUTA TESTE Nº2...');
        try{

         //initialize service
         $apiCop = new ApiCop( $this->logchannel, $this->isConnProduction );
         //call action 
         return $apiCop->getNomesFromIBANs();

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' '  . __LINE__ . ' ' . $e->getMessage());
            return 'erro';
        }
    }


    
    private function makeCurlCall2( $url , $curl_data, $headers, $method , $payload)
    {
        \Log::channel($this->logchannel)->info('----makeCurlCall2----');
        //make a curl call to homebanking
        try {

            // ini_set('max_execution_time',0);
            // ini_set('memory_limit', '-1');
    
            $proxyip= env('PROXY_IP');
            $proxyport = env('PROXY_PORT');
            $proxydata = $proxyip . ':' . $proxyport;

            //$start = microtime(true);
          
            //\Log::channel($this->logChannel)->info('----init----');
            //chamada ao homebanking
            $ch = curl_init();

            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,         // return web page
                CURLOPT_HEADER         => $headers,        // don't return headers
                CURLOPT_FOLLOWLOCATION => true,         // follow redirects
                CURLOPT_ENCODING       => "",           // handle all encodings
                CURLOPT_USERAGENT      => "pmvs",       // who am i
                CURLOPT_AUTOREFERER    => true,         // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
                CURLOPT_TIMEOUT        => 120,          // timeout on response
                CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
                //CURLOPT_POST            => 0 ,            // i am sending post data
               // CURLOPT_POSTFIELDS     => $curl_data,    // this are my post vars
                CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
                CURLOPT_SSL_VERIFYPEER => false,        //
                CURLOPT_VERBOSE        => 1 ,               //~
                CURLOPT_CUSTOMREQUEST => $method, 
              //  CURLOPT_PORT        => 8080,       
            );

            
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
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [
                'httpinfo_header' => [] ,
                'content' => $e->getMessage(),
                'error' => true,
                'httpcode' => 500,
            ];;
        }
    }


}