<?php

namespace App\Models\BancoPortugal\ProxyLookup;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;

class PlAssoc extends Model
{
    protected $connection = 'odbc-gba';
    protected $table = 'pl_assoc';
    public $timestamps = false;
    private $logchannel = 'bancoportugal';
    private $isConnProduction = false;
    private $mensagemErro = '';
    private $dt_inicio_gba = '';
    private $dt_resposta_gba = '';
    private $dt_inicio_operacao = '';
    private $dt_resposta_operacao = '';
    private $sigla = '';

    private $info;
    private $infoSet = false;
    private $n_contrato ;
    private $iban  ;
    private $identificador ;
    private $tp_identificador ;
    private $dt_subscricao ;
    private $dt_envio;
    private $nif ;
    private $tp_psu ;
    private $id_pedido_bp_orig ;
    private $id_pedido_bp  ;
    private $sit_pedido_bp ;
    private $dt_pendente ;
    private $dt_normal ;
    private $dt_expirado ;
    private $dt_remocao ;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUTORES
    |--------------------------------------------------------------------------
    */  
    public function __construct() 
    {
        $this->logchannel = 'bancoportugal';
        $this->isConnProduction = config('app.connection_prod');
        $this->setIsConnProduction();
        $this->mensagemErro = '';
        $this->infoSet = false;
        $this->sigla =  config('app.sigla');
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct PlAssoc');
        \Log::channel($this->logchannel)->info('Connection de Produção ? ' . ( $this->isConnProduction ? 'SIM' : 'NÃO' ) );
        \Log::channel($this->logchannel)->info('Connection  ' .  $this->connection );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 PlAssoc');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        $this->setIsConnProduction();
        \Log::channel($this->logchannel)->info('__construct_2 PlAssoc');
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

    public function setIsConnProduction( )
    {

        if ( $this->isConnProduction ) {
            $this->connection = 'odbc-gba-prod';
        }else {
            $this->connection = 'odbc-gba';
        }
        \Log::channel($this->logchannel)->info('setIsConnProduction : ' . $this->connection);
    }

    public function setInfo( $info )
    {   
        try{

            \Log::channel($this->logchannel)->info( 'set INFO...'  );

            $this->infoSet = false;

            if ( $info ) {
                if ( count($info) > 0 )  {
                    $this->info = $info;
                    if (array_key_exists('n_contrato', $info)){
                        $this->n_contrato = $info['n_contrato'];
                    }
                    if (array_key_exists('iban', $info)){
                        $this->iban = $info['iban'];
                    }
                    if (array_key_exists('identificador', $info)){
                         $this->identificador = $info['identificador'];
                    }
                    if (array_key_exists('tp_identificador', $info)){
                        $this->tp_identificador = $info['tp_identificador'];
                    }
                    if (array_key_exists('dt_subscricao', $info)){
                        $this->dt_subscricao = $info['dt_subscricao'];
                    }
                    if (array_key_exists('dt_envio', $info)){
                        $this->dt_envio = $info['dt_envio'];
                    }
                    if (array_key_exists('nif', $info)){
                        $this->nif = $info['nif'];
                    }
                    if (array_key_exists('tp_psu', $info)){
                        $this->tp_psu = $info['tp_psu'];
                    }
                    if (array_key_exists('id_pedido_bp_orig', $info)){
                        $this->id_pedido_bp_orig = $info['id_pedido_bp_orig'];
                    }
                    if (array_key_exists('id_pedido_bp', $info)){
                        $this->id_pedido_bp = $info['id_pedido_bp'];
                    }
                    if (array_key_exists('sit_pedido_bp', $info)){
                        $this->sit_pedido_bp = $info['sit_pedido_bp'];
                    }
                    if (array_key_exists('dt_pendente', $info)){
                        $this->dt_pendente = $info['dt_pendente'];
                    }
                    if (array_key_exists('dt_normal', $info)){
                        $this->dt_normal = $info['dt_normal'];
                    }
                    if (array_key_exists('dt_expirado', $info)){
                        $this->dt_expirado = $info['dt_expirado'];
                    }
                    if (array_key_exists('dt_remocao', $info)){
                        $this->dt_remocao = $info['dt_remocao'];
                    }
                    $this->infoSet = true;
                    return true;
                }
            }

        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | GETTERS
    |--------------------------------------------------------------------------
    */  
    public function isInfoSet( )
    {
        return $this->infoSet;
    }



    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */  
    public function existeAssociacaoAtiva()
    {
        try{

            if ( ! $this->infoSet ) {
                \Log::channel($this->logchannel)->error( 'INFO not set' );
                return false ;
            }

            $sql="select count(*) as nregs
            from pl_assoc        
            where n_contrato = '".$this->n_contrato."'
            and nif = '".$this->nif."'
            and tp_identificador = '".$this->tp_identificador."'
            and tp_psu = '".$this->tp_psu."'
            and sit_pedido_bp = 'N'" ;

            \Log::channel($this->logchannel)->info( $sql );

            $devolve = DB::connection($this->connection)->select($sql);

            \Log::channel($this->logchannel)->info(  print_r( $devolve, true) );

            if (count($devolve) != 1 ) {
                return false ;
            }

            if ($devolve[0]['nregs'] < 1) {
                return false;
            }else {
                return true;
            }
            

        } catch (\Illuminate\Database\QueryException  $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );

        } catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
    }

    public function getAssociacaoAtiva()
    {
        try{

            if ( ! $this->infoSet ) {
                \Log::channel($this->logchannel)->error( 'INFO not set' );
                return false ;
            }

            $sql="select *
            from pl_assoc        
            where n_contrato = '".$this->n_contrato."'
            and nif = '".$this->nif."'
            and tp_identificador = '".$this->tp_identificador."'
            and tp_psu = '".$this->tp_psu."'
            and sit_pedido_bp = 'N'" ;

            \Log::channel($this->logchannel)->info( $sql );

            $devolve = DB::connection($this->connection)->select($sql);

            \Log::channel($this->logchannel)->info(  print_r( $devolve, true) );

            return $devolve;

        } catch (\Illuminate\Database\QueryException  $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];

        } catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }
    }

    public function insere()
    {
        try{

            if( count($this->info) == 0) {
                return [ 'error' => true, 'message'=> 'Array de informação para insert na tabela pl_assoc não inicializado'];
            }

            $sql= "INSERT INTO " . $this->table . " VALUES ( 
                '". $this->info['n_contrato'] ."', 
                '". $this->info['iban'] ."', 
                '". $this->info['identificador']."', 
                '". $this->info['tp_identificador'] ."',   
                '". $this->info['dt_subscricao'] ."',  
                '". $this->info['dt_envio'] ."',  
                '". $this->info['nif'] ."',  
                '". $this->info['tp_psu'] ."',  
                '". $this->info['id_pedido_bp_orig']."',   
                '". $this->info['id_pedido_bp']."', 
                '". $this->info['sit_pedido_bp']."', 
                '". $this->info['dt_pendente']."', 
                '". $this->info['dt_normal']."', 
                '". $this->info['dt_expirado']."', 
                '". $this->info['dt_remocao']."',
                '". $this->info['msg_erro']."',
                '". $this->info['msg_erro_cod']."',
                '". $this->info['msg_erro_val']."',
                '". $this->info['dt_criacao']."'
                )";

            \Log::channel($this->logchannel)->info($sql);

            $this->connection = 'odbc-gba';
            if($this->isConnProduction) {
                $this->connection = 'odbc-gba-prod';
            }
            \Log::channel($this->logchannel)->info('Connection: ' . $this->connection);

            $result = \DB::connection( $this->connection )->insert($sql);

            \Log::channel($this->logchannel)->info('Return: ' . print_r([ 'error' => false, 'message'=> '', 'result' => $result], true));
            \Log::channel($this->logchannel)->info('*************************');

            return [ 'error' => false, 'message'=> '', 'result' => $result];

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return [ 'error' => true, 'message'=> $e->getMessage()];
        } catch (\Exception $e) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return [ 'error' => true, 'message'=> $e->getMessage()];
        }
    }

    public function getTabela()
    {
        try{

            $sql= "SELECT * 
                  FROM " . $this->table . " ORDER BY  dt_pedido DESC, n_contrato ASC ";
            \Log::channel($this->logchannel)->info($sql);

            $this->connection = 'odbc-gba';
            if($this->isConnProduction) {
                $this->connection = 'odbc-gba-prod';
            }
            \Log::channel($this->logchannel)->info('Connection: ' . $this->connection);

            $result = \DB::connection( $this->connection )->select($sql);

            return [ 'error' => false, 'message'=> '', 'result' => $result];

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return [ 'error' => true, 'message'=> $e->getMessage()];
        } catch (\Exception $e) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return [ 'error' => true, 'message'=> $e->getMessage()];
        }
    }

    public function getRegisto()
    {
        try{

            $sql= "SELECT * 
                  FROM " . $this->table . " WHERE n_contrato = '" . $this->n_contrato . "'
                  AND identificador = '" . $this->identificador . "' AND iban = '" . $this->iban . "' ";
            \Log::channel($this->logchannel)->info($sql);

            $this->connection = 'odbc-gba';
            if($this->isConnProduction) {
                $this->connection = 'odbc-gba-prod';
            }
            \Log::channel($this->logchannel)->info('Connection: ' . $this->connection );

            $result = \DB::connection( $this->connection )->select($sql);

            return [ 'error' => false, 'message'=> '', 'result' => $result];

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return [ 'error' => true, 'message'=> $e->getMessage()];
        } catch (\Exception $e) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            return [ 'error' => true, 'message'=> $e->getMessage()];
        }
    }

    public function removeAssociacaoAtiva()
    {
        try{

            if ( ! $this->infoSet ) {
                \Log::channel($this->logchannel)->error( 'INFO not set' );
                return false ;
            }

            $sql="UPDATE pl_assoc  
                SET dt_remocao =  '". $this->dt_remocao ."' ,
                sit_pedido_bp = 'R' ,
                id_pedido_bp = '".$this->id_pedido_bp."'
                where n_contrato = '".$this->n_contrato."'
                and iban = '".$this->iban."'
                and nif = '".$this->nif."'
                and tp_identificador = '".$this->tp_identificador."'
                and identificador = '".$this->identificador."'
                and tp_psu = '".$this->tp_psu."'
                and sit_pedido_bp = 'N'" ;

            \Log::channel($this->logchannel)->info( $sql );

            $devolve = DB::connection($this->connection)->update($sql);

            \Log::channel($this->logchannel)->info(  print_r( $devolve, true) );
            \Log::channel($this->logchannel)->info('*************************');

            return true;

        } catch (\Illuminate\Database\QueryException  $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false ;

        } catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false ;
        }
    }

    public function removeAssociacoes()
    {
        try{

            if ( ! $this->infoSet ) {
                \Log::channel($this->logchannel)->error( 'INFO not set' );
                return false ;
            }

            $sql="UPDATE pl_assoc  
                SET dt_remocao =  '". $this->dt_remocao ."' ,
                sit_pedido_bp = 'R' 
                where n_contrato = '".$this->n_contrato."'
                and nif = '".$this->nif."'
                and tp_identificador = '".$this->tp_identificador."'
                and identificador = '".$this->identificador."'
                and tp_psu = '".$this->tp_psu."'
                and sit_pedido_bp = 'N'" ;

            \Log::channel($this->logchannel)->info( $sql );

            $devolve = DB::connection($this->connection)->update($sql);

            \Log::channel($this->logchannel)->info(  print_r( $devolve, true) );
            \Log::channel($this->logchannel)->info('*************************');

            return true;

        } catch (\Illuminate\Database\QueryException  $e) {
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false ;

        } catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false ;
        }
    }

}