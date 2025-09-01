<?php 

namespace App\Repositories\BancoPortugal;

use DB;
use DateTime;
use Illuminate\Support\Facades\Log;

use App\Models\BancoPortugal\ProxyLookup\PlAssoc;


class ProxyLookupRepository 
{
    private $logchannel = 'bancoportugal';
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
        $this->logchannel = 'bancoportugal';
        $this->connection = 'odbc-gba';
        $this->mensagemErro = '';
        $this->isConnProduction = config('app.connection_prod');
        $this->setConnection();
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct ProxyLookupRepository');
        \Log::channel($this->logchannel)->info('Connection de Produção ? ' . ( $this->isConnProduction ? 'SIM' : 'NÃO' ) );
        \Log::channel($this->logchannel)->info('Connection : ' . $this->connection );
    }

    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 ProxyLookupRepository');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        $this->setConnection();
        \Log::channel($this->logchannel)->info('__construct_2 ProxyLookupRepository');
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
    | PUBLIC METHODS 
    |--------------------------------------------------------------------------
    */  

    public function existeAssociacaoAtiva( $contrato, $telemovel, $nif, $tpidentificador, $tpcustomer)
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ProxyLookupRepository.existeAssociacaoAtiva'  );

            //connection de producao pq nao existe tabela de dev
            $this->isConnProduction = false;

            $pl = new PlAssoc( $this->logchannel , $this->isConnProduction );
            
            //set informação
            $info = [];
            $info['nif'] = $nif;
            $info['n_contrato'] = $contrato;
            if ( $tpidentificador == config('enums.ProxyLookupTipoIdentificador.Telemovel')) {
                $info['identificador'] = $telemovel;
            }else {
                $info['identificador'] = $nif;
            }
            $info['tp_identificador'] = $tpidentificador;
            $info['tp_psu'] = $tpcustomer;

            $pl->setInfo( $info );
            
            return $pl->existeAssociacaoAtiva();

        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            throw $e;
        }
    }

    public function insereAssociacaoAtiva($correlation_id, $contrato, $telemovel, $nif, $tpidentificador, $tpcustomer, $iban)
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ProxyLookupRepository.insereAssociacao'  );

            //connection de producao pq nao existe tabela de dev
            $this->isConnProduction = false;

            $pl = new PlAssoc( $this->logchannel , $this->isConnProduction );
            
            //set informação
            $info = [];
            $info['nif'] = $nif;
            $info['n_contrato'] = $contrato;
            if ( $tpidentificador == config('enums.ProxyLookupTipoIdentificador.Telemovel')) {
                $info['identificador'] = $telemovel;
            }else {
                $info['identificador'] = $nif;
            }
            $info['tp_identificador'] = $tpidentificador;
            $info['tp_psu'] = $tpcustomer;
            $info['iban'] = $iban;
            $info['dt_envio'] = date('Y-m-d');
            $info['dt_subscricao'] = date('Y-m-d');
            $info['id_pedido_bp_orig'] = $correlation_id;
            $info['id_pedido_bp'] = $correlation_id;
            $info['sit_pedido_bp'] = 'N';
            $info['dt_pendente'] = '';
            $info['dt_normal'] =  date('Y-m-d');
            $info['dt_expirado'] = '';
            $info['dt_remocao'] = '';
            $info['msg_erro'] = '';
            $info['msg_erro_cod'] = '';
            $info['msg_erro_val'] = '';

            $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', '')); 
            // Truncate to milliseconds
            $nowFormatted = substr($now->format('Y-m-d H:i:s.u'), 0, -3) ; 
            $nowFormatted = $now->format('Y-m-d H:i:s.u') ; 
            $info['dt_criacao'] =  $this->getTimeStamp();


            $pl->setInfo( $info );
            
            return $pl->insere();

        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            throw $e;
        }
    }

    public function getAssociacaoAtiva( $contrato, $telemovel, $nif, $tpidentificador, $tpcustomer)
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ProxyLookupRepository.getAssociacaoAtiva'  );

            //connection de producao pq nao existe tabela de dev
            $this->isConnProduction = false;

            $pl = new PlAssoc( $this->logchannel , $this->isConnProduction );
            
            //set informação
            $info = [];
            $info['nif'] = $nif;
            $info['n_contrato'] = $contrato;
            if ( $tpidentificador == config('enums.ProxyLookupTipoIdentificador.Telemovel')) {
                $info['identificador'] = $telemovel;
            }else {
                $info['identificador'] = $nif;
            }
            $info['tp_identificador'] = $tpidentificador;
            $info['tp_psu'] = $tpcustomer;

            $pl->setInfo( $info );
            
            return $pl->getAssociacaoAtiva();

        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            throw $e;
        }
    }

    public function removeAssociacao( $correlation_id,$contrato, $telemovel, $nif, $tpidentificador, $tpcustomer, $iban)
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ProxyLookupRepository.removeAssociacao'  );

            //connection de producao pq nao existe tabela de dev
            $this->isConnProduction = false;

            $pl = new PlAssoc( $this->logchannel , $this->isConnProduction );
            
            //set informação
            $info = [];
            $info['nif'] = $nif;
            $info['n_contrato'] = $contrato;
            if ( $tpidentificador == config('enums.ProxyLookupTipoIdentificador.Telemovel')) {
                $info['identificador'] = $telemovel;
            }else {
                $info['identificador'] = $nif;
            }
            $info['tp_identificador'] = $tpidentificador;
            $info['tp_psu'] = $tpcustomer;
            $info['iban'] = $iban;
            $info['dt_remocao'] = date('Y-m-d');
            $info['id_pedido_bp'] = $correlation_id;

            $pl->setInfo( $info );
            
            return $pl->removeAssociacaoAtiva();

        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            throw $e;
        }
    }

    public function removeAssociacoes($correlation_id, $contrato, $telemovel, $nif, $tpidentificador, $tpcustomer, $iban)
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ProxyLookupRepository.removeAssociacoes'  );

            //connection de producao pq nao existe tabela de dev
            $this->isConnProduction = false;

            $pl = new PlAssoc( $this->logchannel , $this->isConnProduction );
            
            //set informação
            $info = [];
            $info['nif'] = $nif;
            $info['n_contrato'] = $contrato;
            if ( $tpidentificador == config('enums.ProxyLookupTipoIdentificador.Telemovel')) {
                $info['identificador'] = $telemovel;
            }else {
                $info['identificador'] = $nif;
            }
            $info['tp_identificador'] = $tpidentificador;
            $info['tp_psu'] = $tpcustomer;
            $info['dt_remocao'] = date('Y-m-d');
            $info['id_pedido_bp'] = $correlation_id;

            $pl->setInfo( $info );
            
            return $pl->removeAssociacoes();

        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            throw $e;
        }
    }

}