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

class GbaController extends Controller
{
    private $logchannel = 'gba';
    private $isConnProduction = false;
    private $connection = 'odbc-gba';
    private $emensagemErro = '';
    /*
    |--------------------------------------------------------------------------
    | METHODS CONSTRUCT
    |--------------------------------------------------------------------------
    */   
    public function __construct() 
    {
        $this->logchannel = 'gba';
        $this->connection = 'odbc-gba';
        $this->mensagemErro = '';
        $this->isConnProduction = config('app.connection_prod');
        $this->setConnection();
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct GbaController');
        \Log::channel($this->logchannel)->info('Connection de Produção ? ' . ( $this->isConnProduction ? 'SIM' : 'NÃO' ) );
        \Log::channel($this->logchannel)->info('Connection : ' . $this->connection );
    }

    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 GbaController');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        $this->setConnection();
        \Log::channel($this->logchannel)->info('__construct_2 GbaController');
    }

    
    /*
    |--------------------------------------------------------------------------
    | GETTERS
    |--------------------------------------------------------------------------
    */  
    public function getConnection()
    {
        return $this->connection ;
    }

    public function getMensagemErro()
    {
        return $this->mensagemErro ;
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
        //$this->timestamp = $nowFormatted; 
       // \Log::channel($this->logchannel)->info('Timestamp : ' .  $this->timestamp );

        return $nowFormatted;

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

    public function setIsConnProduction( $isConnProduction )
    {
        $this->isConnProduction = $isConnProduction;
        $this-> setConnection();
    }

    public function setConnection( )
    {
        if ( $this->isConnProduction ) {
            $this->connection = 'odbc-gba-prod';
        }else {
            $this->connection = 'odbc-gba';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PUBLIC 
    |--------------------------------------------------------------------------
    */  

    public function contaEncerrada( $identificador, $nif, $iban )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'GbaController.contaEncerrada :' . $identificador); 
            \Log::channel($this->logchannel)->info( 'GbaController.contaEncerrada :' . $nif); 
            \Log::channel($this->logchannel)->info( 'GbaController.contaEncerrada :' . $ban); 


            //connection de producao pq nao existe tabela de dev
            $this->isConnProduction = true;

            //faz dissociação 
            return true;


            // $pl = new PlAssoc( $this->logchannel , $this->isConnProduction );
            
            // //set informação
            // $info = [];
            // $info['nif'] = $nif;
            // $info['n_contrato'] = $contrato;
            // if ( $tpidentificador == config('enums.ProxyLookupTipoIdentificador.Telemovel')) {
            //     $info['identificador'] = $telemovel;
            // }else {
            //     $info['identificador'] = $nif;
            // }
            // $info['tp_identificador'] = $tpidentificador;
            // $info['tp_psu'] = $tpcustomer;

            // $pl->setInfo( $info );
            
            // return $pl->existeAssociacaoAtiva();

        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            return false;
        }
    }




}
