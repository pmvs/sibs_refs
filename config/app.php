<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),
    'sigla' => env('APP_SIGLA', 'MAF'),



    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),
    'domain_plcp' => env('APP_DOMAIN_PLCP', 'plqual'),
    'domain_vop' => env('APP_DOMAIN_VOP', 'vop-cert'),
    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application PROXY LOOKUP VARIABLES
    |--------------------------------------------------------------------------
    |
    |
    */

    'white_ip_oba' => env('WHITEIPOBA','127.0.0.1'),

    'sigla_psp' => env('SIGLA_PSP', 'cctve'),
    'codigo_psp' => env('CODIGO_PSP', '0000'),
    'path_to_certificate_pfx' => env('PATH_TO_CERTIFICATE_PFX', 'c:\tmp'),
    'psw_certificate_pfx' => env('PSW_CERTIFICATE_PFX', '123'),

    'path_private_key' => env('PATH_PRIVATE_KEY', 'C:\private.pem'),
    'path_public_key' => env('PATH_PUBLIC_KEY', 'C:\public.pem'),

    'oauth2bpuriaudience' => env('OAUTH2_BP_URI_AUDIENCE', 'https://wwwcert.bportugal.net/adfs/oauth2/token'),
    'oauth2bpuriauthorize' => env('OAUTH2_BP_URI_AUTHORIZE', 'https://wwwcert.bportugal.net/adfs/oauth2/authorize'),
    'oauth2_client_id' => env('OAUTH2_CLIENT_ID', 'https://bpnetsvc-ccmaf-cert.bportugal.pt'),

    'oauth2_issuer' => env('OAUTH2_ISSUER', 'http://wwwcert.bportugal.net/adfs/services/trust'),

    'oauth2_keys' => env('OAUTH2_KEYS', 'https://wwwcert.bportugal.net/adfs/discovery/keys'),

    'usernamebpnet' => env('USERNAME_BPNET', 'cqi040'),
    'passwordbpnet' => env('PASSWORD_BPNET', 'sonos'),

    'login_page' => env('LOGIN_PAGE', 1),

    'url_homebanking_oficial' => env('HOMEBANKING_OFICIAL', 'http://localhost'),

    //PROXY LOOKUP
    'copendpoint' => env('COP_ENDPOINT', 'https://plqual.ccammafra.pt/api/conp'),
    'plendpoint' => env('PL_ENDPOINT', 'https://plqual.ccammafra.pt/api/pl'),

    'oauth2appid'=> env('OAUTH2_APPID', 'https://bpnetsvc-bdp-cert.bportugal.pt/'),

    'pspcode' => env('PSP_CODE', '0000'),
    'pspcodeinvalido' => env('PSP_CODE_INVALIDO', '000'),
    'pspcodediferenteiban' => env('PSP_CODE_DIFERENTE_IBAN', '0000'),
    'pspcodeother' => env('PSP_CODE_OTHER', '0000'),
    'ibanparticular' => env('IBAN_PARTICULAR', 'PT50520052000001426000179'),
    'ibanparticular2' => env('IBAN_PARTICULAR2', 'PT50520052000001426000179'),
    'ibanparticular3' => env('IBAN_PARTICULAR3', 'PT50520052000001426000179'),
    'ibanempresa' => env('IBAN_EMPRESA', 'PT50001800000346382600173'),
    'ibanempresa2' => env('IBAN_EMPRESA2', 'PT50001800000346382600173'),
    'ibanempresa3' => env('IBAN_EMPRESA3', 'PT50001800000346382600173'),
    'ibanparticularvarios' => env('IBAN_PARTICULAR_VARIOS', ''),
    'ibanparticularinvalido' => env('IBAN_PARTICULAR_INVALIDO', 'PT50520052000001426000178'),
    'ibanother' => env('IBAN_OTHER', 'PT50520052000001426000179'),
    'fiscalnumberparticular' => env('FISCAL_NUMBER_PARTICULAR', 'PT999999999'),
    'fiscalnumberparticularother' => env('FISCAL_NUMBER_PARTICULAR_OTHER', 'PT999999999'),
    'fiscalnumberparticularother2' => env('FISCAL_NUMBER_PARTICULAR_OTHER_2', 'PT999999999'),
    'fiscalnumberparticularother3' => env('FISCAL_NUMBER_PARTICULAR_OTHER_3', 'PT999999999'),
    'fiscalnumberparticularinvalido' => env('FISCAL_NUMBER_PARTICULAR_INVALIDO', 'PT999999999'),
    'fiscalnumberempresa' => env('FISCAL_NUMBER_EMPRESA', 'PT999999999'),
    'fiscalnumberempresanaoaderente' => env('FISCAL_NUMBER_EMPRESA_NAO_ADERENTE', 'PT999999999'),
    'fiscalnumberempresainvalido' => env('FISCAL_NUMBER_EMPRESA_INVALIDO', 'PT999999999'),
    'telemovelnacional' => env('TELEMOVEL_NACIONAL', '+351960000000'),
    'telemovelnacionalinvalido' => env('TELEMOVEL_NACIONAL_INVALIDO', '960000000'),
    'telemovelnacionalsemindicativo'  => env('TELEMOVEL_NACIONAL_SEM_INDICATIVO', '351960000000'),
    'telemovelnacionalparticular' => env('TELEMOVEL_NACIONAL_PARTICULAR', '+351910000000'),
    'telemovelnacionalempresa' => env('TELEMOVEL_NACIONAL_EMPRESA', '+351910000000'),
    'telemovelinternacional' => env('TELEMOVEL_INTERNACIONAL', '+33060000000'),
    'telemovelnacionalnaoassociado' => env('TELEMOVEL_NACIONAL_NAO_ASSOCIADO', '+351960000000'),
    'telemovelinternacionalnaoassociado' => env('TELEMOVEL_INTERNACIONAL_NAO_ASSOCIADO', '+331960000000'),
    'listacontatos' => env('LISTA_CONTATOS', ''),
    'listacontatosnenhumaderente' => env('LISTA_CONTATOS_NENHUM_ADERENTE', ''),
    'listacontatosexcede' => env('LISTA_CONTATOS_EXCEDE', ''),
    'listacontatosigual' => env('LISTA_CONTATOS_IGUAL', ''),

    'lista_ibans_10' => env('LISTA_IBANS_10', ''),
    'lista_ibans_11' => env('LISTA_IBANS_11', ''),

    //testes pl v2 e indisponibilidades
    'iban_v2' => env('IBAN_V2', 'PT50520052000000575100174'),
    'nif_v2' => env('NIF_V2', 'PT206105445'),
    'telemovel_v2' => env('TELEMOVEL_V2', '+3519642554708'),
    'iban_empresa_v2' => env('IBAN_EMPRESA_V2', 'PT50520052000000998900158'),
    'nipc_v2' => env('NIPC_V2', 'PT503622109'),
    'nif2_v2' => env('NIF2_V2', 'PT503622109'),
    'nif_nao_conforme_v2' => env('NIF_NAO_CONFORME_V2', 'PT20610544'),
    'nif_invalido_v2' => env('NIF_INVALIDO_V2', 'PT206105444'),
    'nif_tipo45_v2' => env('NIF_TIPO45_V2', 'PT459999999'),
    'lista_ibans_v2' => env('LISTA_IBANS_V2', ''),
    'iban_tipo45_v2' => env('IBAN_TIPO45_V2', ''),
    'phone_book_v2' => env('PHONEBOOK_V2', ''),
    'iban2_v2' => env('IBAN2_V2', ''),
    'iban_empresa2_v2' => env('IBAN_EMPRESA2_V2', ''),



    'access_token' =>  env('ACCESS_TOKEN', ''),
    'access_token_valido' =>  env('ACCESS_TOKEN_VALIDO', false),


    'otp_seconds_valid' => env('OTP_SECONDS_VALID', 120),
    'inactivity' => env('SESSION_INACTIVITY', 300),
    'session_timer' => env('SESSION_TIMER', 3600),
    'url_sms_express' => env('URL_SMSEXPRESS', 'http://127.0.0.1:8080/sendSMS/'),
    'sms_usa_timestamp' => env('SMS_USA_TIMESTAMP', false),


    'mail_testes' => env('MAIL_TESTES', 'pmvsant@gmail.com'),
    'mail_from_address' => env('MAIL_FROM_ADDRESS', 'net.ccammafra@ccammafra.pt'),
    'mail_from_name' => env('MAIL_FROM_NAME', 'CCAMMafra'),

    'new_oba' => env('NEW_OBA', false),
    'interrupt_process' => env('INTERRUPT_PROCESS', false),

    'usa_proxy' => env('USA_PROXY', 'N'),
    'proxy_server' => env('PROXY_IP', '192.9.210.80'),
    'proxy_port' => env('PROXY_PORT', '6666'),

    'path_documentacao_interna_conta' => env('PATH_DOCUMENTACAO_INTERNA_CONTA', '\\200.52.0.247\ass\ass'),
    'path_documentacao_interna_nif' => env('PATH_DOCUMENTACAO_INTERNA_NIF', '\\200.52.0.247\ass\nif'),
    'path_documentacao_interna_doc' => env('PATH_DOCUMENTACAO_INTERNA_DOC', '\\200.52.0.247\ass\cc'),

    'extensao_documentacao_interna_conta' => env('EXTENSAO_DOCUMENTACAO_INTERNA_CONTA', 'jpg'),
    'extensao_documentacao_interna_nif' => env('EXTENSAO_DOCUMENTACAO_INTERNA_NIF', 'pdf'),
    'extensao_documentacao_interna_doc' => env('EXTENSAO_DOCUMENTACAO_INTERNA_DOC', 'jpg'),

    'cc_search_lists' => env('CC_SEARCH_LISTS', true),

    'service' => env('SERVICE', 'Net.CC@MMafra'),
    'service_documentacao' => env('SERVICE_DOCUMENTACAO', 'Documentos Digitais - CCAM Mafra'),

    'path_envdoc' => env('PATH_ENVDOC', 'c:\\hbp\\envdoc\\'),
    'path_envextratos' => env('PATH_ENVEXTRATOS', 'c:\\hbp\\envextratos\\'),
    'encripta_pdf_doc' => env('ENCRIPTA_PDF_DOC', true),
    'encripta_pdf_extrato' => env('ENCRIPTA_PDF_EXTRATO', true),


    'mail_persi_1' => env('MAIL_PERSI_1', 'pmvsant@gmail.com'),
    'mail_persi_2' => env('MAIL_PERSI_2', 'pmvsant@gmail.com'),
    'mail_persi_cc_1' => env('MAIL_PERSI_CC_1', 'pmvsant@gmail.com'),
    'mail_persi_cc_2' => env('MAIL_PERSI_CC_2', 'pmvsant@gmail.com'),

    'c2b_file_location_backup' => env('C2B_FILE_LOCATION_BACKUP', 'c:\\hbp\c2b\\received\\'),
    
    'multiauth' => env('MULTIAUTH','N'),
    'userifx' => env('USER631','gba'),

    'empresas' => env('EMPRESAS', 'Multi-Autenticação'),

    'smsexpress_originadorName' => env('SMSEXPRESS_ORIGINADOR_NAME', ''),
    'smsexpress_originadorNumber' => env('SMSEXPRESS_ORIGINADOR_NUMBER', ''),
    'smsexpress_originadorUser' => env('SMSEXPRESS_ORIGINADOR_USER', ''),
    'smsexpress_originadorPass' => env('SMSEXPRESS_ORIGINADOR_PASS', ''),
    
    'connection_prod' => env('CONNECTION_PROD', false),

    'white_ips' => env('WHITEIPS', ''),

    'max_requests' => env('MAX_REQUESTS', '5'),
    'periodo_tempo' => env('PERIODO_TEMPO', '1'),
    'last_access' => env('LAST_ACCESS', '1'),
    'frequencia_chamadas' => env('FREQUENCIA_CHAMADAS', '5'),
    'limite_chamadas' => env('LIMITE_CHAMADAS', '10'),
    'warning_email_for_attacks' => env('WARNING_EMAIL_FOR_ATTACKS', 'pmvsant@gmail.com'),
    'url_to_attack' => env('URL_TO_ATTACK', 'https://hbp-testes.ccammafra.pt'),
    'max_tentativas_login_fail' => env('MAX_TENTATIVAS_LOGIN_FAIL', '3'),


    'x_ibm_client_id_testes'  => env('X_IBM_CLIENT_ID_TESTES', ''),
    'x_ibm_client_id'  => env('APP_CLIENT_ID', ''),
    'x_ibm_client_id_testes_3_1'  => env('APP_CLIENT_ID_3_1', ''),


    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    //'timezone' => 'UTC',
    'timezone' => 'Europe/Lisbon',


    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store'  => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */
        PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider::class,
        LaravelPdoOdbc\ODBCServiceProvider::class,
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        'JWTAuth' => PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::class, 
        'JWTFactory' => PHPOpenSourceSaver\JWTAuth\Facades\JWTFactory::class,
    ])->toArray(),

];
