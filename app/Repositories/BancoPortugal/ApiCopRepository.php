<?php 

namespace App\Repositories\BancoPortugal;

use DB;
use Illuminate\Support\Facades\Log;

use App\Models\Pais;
use App\Models\BBalcao;
use App\Models\Profissao;
use App\Models\Sutilizadores;
use App\Models\SImpressora;
use App\Models\BParametros;
use App\Models\Conta;
use App\Models\Contas\BCnt;
use App\Models\Contas\BtpAcont;

use App\Models\Sistema\SysTables;

class ApiCopRepository 
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
        \Log::channel($this->logchannel)->info('__construct ApiCopRepository');
        \Log::channel($this->logchannel)->info('Connection de Produção ? ' . ( $this->isConnProduction ? 'SIM' : 'NÃO' ) );
        \Log::channel($this->logchannel)->info('Connection : ' . $this->connection );
    }

    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 ApiCopRepository');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        $this->setConnection();
        \Log::channel($this->logchannel)->info('__construct_2 ApiCopRepository');
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

    
    /*
    |--------------------------------------------------------------------------
    | IMPRESSORAS
    |--------------------------------------------------------------------------
    */

    public function getImpressoras()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getSysTables'  );

            $impressoras = new SImpressora( $this->logchannel , $this->isConnProduction );

            return $impressoras->getTabelas();
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            return [];
        }
    }

    public function getLocaisImpressoras()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getSysTables'  );

            $impressoras = new SImpressora( $this->logchannel , $this->isConnProduction );

            return $impressoras->getLocaisImpressoras();
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            return [];
        }
    }

    
    /*
    |--------------------------------------------------------------------------
    | SYS TABLES
    |--------------------------------------------------------------------------
    */

    public function getSysTables()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getSysTables'  );

            $tabelas = new SysTables( $this->logchannel , $this->isConnProduction );

            return $tabelas->getTabelas();
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            return [];
        }
    }

    public function getSysTablesInfoByName( $nmtabela )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getSysTablesInfoByName: ' . $nmtabela );

            $infoTabela = new SysTables( $this->logchannel , $this->isConnProduction );

            return $infoTabela->getTabelaInfoByName( $nmtabela );
          
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage()  );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            return [];
        }
    }

    public function getSysTablesInfoComplete( $tabid )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getSysTablesInfoComplete: ' . $tabid );

            $infoTabelacomplete = new SysTables( $this->logchannel , $this->isConnProduction );

            return $infoTabelacomplete->getTabelaInfoComplete( $tabid );
          
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage()  );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() ) ;
            return [];
        }
    }

    
   
    /*
    |--------------------------------------------------------------------------
    | PAISES
    |--------------------------------------------------------------------------
    */
    public function getPaises( )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getPaises'  );

            return Pais::getPaises($this->logchannel);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getPaises ERROR QueryException : ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getPaises Exception : ' . $e->getMessage()) ;
            return [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROFISSOES
    |--------------------------------------------------------------------------
    */
    public function getProfissoes( )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getProfissoes'  );

            return Profissao::getProfissoes($this->logchannel);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getProfissoes ERROR QueryException : ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getProfissoes Exception : ' . $e->getMessage()) ;
            return [];
        }
    }

    public function getProfissao( $cdprofissao )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getProfissao'  . $cdprofissao);

            return Profissao::getProfissao($this->logchannel, $cdprofissao);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getProfissao ERROR QueryException : ' . $e->getMessage() );
            return "";
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getProfissao Exception : ' . $e->getMessage()) ;
            return "";
        }
    }

        
    /*
    |--------------------------------------------------------------------------
    | UTILIZADORES
    |--------------------------------------------------------------------------
    */
    public function getUtilizadores()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getUtilizadores');

            $utilizadores = new Sutilizadores( $this->logchannel , $this->isConnProduction );

            return $utilizadores->getAll();
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }
    }

    public function getUtilizador( $id )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getUtilizador ' . $id);

            

            $utilizadores = new Sutilizadores( $this->logchannel , $this->isConnProduction );
          
            return $utilizadores->getOne( $id );

        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return null;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return null;
        }
    }

    public function existeNUtilizador( $id )
    {
        $this->mensagemErro = '';
        try{

            $utilizadores = new Sutilizadores( $this->logchannel , $this->isConnProduction );

            //verifica se o numero  existe
            return $utilizadores->existeUtilizador( $id );
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $this->mensagemErro = 'Utilizador não obtido. Base de dados indisponível.';
            return true;

        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $this->mensagemErro = 'Utilizador não obtido. Erro interno.';
            return true;
        }
    }

    public function existeNmUtilizador( $nm )
    {
        $this->mensagemErro = '';
        try{

            $utilizadores = new Sutilizadores( $this->logchannel , $this->isConnProduction );

            //verifica se o numero  existe
            return $utilizadores->existeNmUtilizador( $nm );
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $this->mensagemErro = 'Nome de Utilizador não obtido. Base de dados indisponível.';
            return true;

        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $this->mensagemErro = ' Nome de Utilizador não obtido. Erro interno.';
            return true;
        }
    }

    public function updateUtilizador(  $fieldsForUpdate , $utilizador )
    {
        \Log::channel($this->logchannel)->info( 'ApiRepository.updateUtilizador...');
        \Log::channel($this->logchannel)->info( 'Inicio de transação');

        $conn = $this->getConnection();
        \Log::channel($this->logchannel)->info( 'Connection : ' . $conn);
      
        DB::connection($conn)->beginTransaction();

        try{

            //\Log::channel($this->logchannel)->info( 'Dados para UPDATE: ');
            //\Log::channel($this->logchannel)->info( print_r($fieldsForUpdate,true) );
            //\Log::channel($this->logchannel)->info( print_r($utilizador,true) );

            $utilizadores = new Sutilizadores( $this->logchannel , $this->isConnProduction );

            $utilizadores->setUtilizador( $utilizador );

            $updated = $utilizadores->updateUtilizador( $fieldsForUpdate );
            if ( ! $updated ) {
                DB::connection($conn)->rollback();
                \Log::channel($this->logchannel)->info( 'Rollback...' );
                \Log::channel($this->logchannel)->info( 'Utilizador não atualizado.' );
                return false;
            }

            DB::connection($conn)->commit();
            \Log::channel($this->logchannel)->info( 'Commit...' );
            \Log::channel($this->logchannel)->info( 'Utilizador atualizado com sucesso.' );

            return true;
           
        }catch (\Illuminate\Database\QueryException $e){
            DB::connection($conn)->rollback();
            \Log::channel($this->logchannel)->info( 'Rollback...' );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false;

        }catch (\Exception $e){
            DB::connection($conn)->rollback();
            \Log::channel($this->logchannel)->info( 'Rollback...' );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false;
        }
    }

    public function criaUtilizador(  $fieldsForCreation  )
    {
        \Log::channel($this->logchannel)->info( 'ApiRepository.criaUtilizador...');
        \Log::channel($this->logchannel)->info( 'Inicio de transação');

        $conn = $this->getConnection();
        \Log::channel($this->logchannel)->info( 'Connection : ' . $conn);
      
        DB::connection($conn)->beginTransaction();

        try{

            $utilizadores = new Sutilizadores( $this->logchannel , $this->isConnProduction );

            //verifica se o numero a introduzir não existe
            $existeUtilizador = $utilizadores->existeUtilizador( $fieldsForCreation['n_utilizador'] );
            if ( $existeUtilizador ) {
                DB::connection($conn)->rollback();
                \Log::channel($this->logchannel)->info( 'Rollback...' );
                \Log::channel($this->logchannel)->info( 'Utilizador não criado. Numero já existe' );
                $this->mensagemErro = 'Utilizador não criado. Número de utilizador atribuído já existe';
                return false;
            }

            //verifica se o nome a introduzir não existe
            $existeNmUtilizador = $utilizadores->existeNmUtilizador( trim($fieldsForCreation['utilizador']) );
            if ( $existeNmUtilizador ) {
                DB::connection($conn)->rollback();
                \Log::channel($this->logchannel)->info( 'Rollback...' );
                \Log::channel($this->logchannel)->info( 'Utilizador não criado. Nome de utilizador para login já existe.' );
                $this->mensagemErro = 'Utilizador não criado. Nome para login já existe.';
                return false;
            }
        
            //insere registo
            $created = $utilizadores->insereUtilizador( $fieldsForCreation );
            if ( ! $created ) {
                DB::connection($conn)->rollback();
                \Log::channel($this->logchannel)->info( 'Rollback...' );
                \Log::channel($this->logchannel)->info( 'Utilizador não criado.' );
                $this->mensagemErro = 'Ocorreram erros. Utilizador não criado.';
                return false;
            }

            // DB::connection($conn)->rollback();
            // \Log::channel($this->logchannel)->info( 'Rollback...FORÇADO' );
            // \Log::channel($this->logchannel)->info( 'Utilizador não criado.' );
            // $this->mensagemErro = 'Utilizador não criado.';
            // return false;

            DB::connection($conn)->commit();
            \Log::channel($this->logchannel)->info( 'Commit...' );
            \Log::channel($this->logchannel)->info( 'Utilizador criado com sucesso.' );

            return true;
           
        }catch (\Illuminate\Database\QueryException $e){
            DB::connection($conn)->rollback();
            \Log::channel($this->logchannel)->info( 'Rollback...' );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false;

        }catch (\Exception $e){
            DB::connection($conn)->rollback();
            \Log::channel($this->logchannel)->info( 'Rollback...' );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false;
        }
    }

    public function eliminaUtilizador ( $id )
    {
        \Log::channel($this->logchannel)->info( 'ApiRepository.eliminaUtilizador...' . $id);
        \Log::channel($this->logchannel)->info( 'Inicio de transação');

        $conn = $this->getConnection();
        \Log::channel($this->logchannel)->info( 'Connection : ' . $conn);
      
        DB::connection($conn)->beginTransaction();

        try{

            $utilizadores = new Sutilizadores( $this->logchannel , $this->isConnProduction );

            $deleted = $utilizadores->eliminaUtilizador( $id );
            if ( ! $deleted ) {
                DB::connection($conn)->rollback();
                \Log::channel($this->logchannel)->info( 'Rollback...' );
                \Log::channel($this->logchannel)->info( 'Utilizador não eliminado.' );
                return false;
            }

            DB::connection($conn)->commit();
            \Log::channel($this->logchannel)->info( 'Commit...' );
            \Log::channel($this->logchannel)->info( 'Utilizador eliminado com sucesso.' );

            return true;
           
        }catch (\Illuminate\Database\QueryException $e){
            DB::connection($conn)->rollback();
            \Log::channel($this->logchannel)->info( 'Rollback...' );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false;

        }catch (\Exception $e){
            DB::connection($conn)->rollback();
            \Log::channel($this->logchannel)->info( 'Rollback...' );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BALCOES
    |--------------------------------------------------------------------------
    */
    public function getBalcoes()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getBalcoes');

            return BBalcao::getAll( $this->logchannel );
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }
    }

    public function getBalcao( $cdbalcao )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getBalcao ' . $cdbalcao);

            return BBalcao::getOne( $cdbalcao, $this->logchannel );
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return null;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOCALIZACOES
    |--------------------------------------------------------------------------
    */
    public function getLocalizacoes()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getLocalizacoes');

            return Sutilizadores::getLocalizacoes( $this->logchannel );
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NIVEIS
    |--------------------------------------------------------------------------
    */
    public function getNiveis()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getNiveis');

            return Sutilizadores::getNiveis( $this->logchannel );
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CAIXAS
    |--------------------------------------------------------------------------
    */
    public function getCaixas()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getCaixas');

            return Sutilizadores::getCaixas( $this->logchannel );
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CONTAS 
    |--------------------------------------------------------------------------
    */
    public function getMotivosEncerramentoContas( )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getMotivosEncerramentoContas'  );

            return BtpAcont::getMotivosEncerramentoContas($this->logchannel);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getMotivosEncerramentoContas ERROR QueryException : ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getMotivosEncerramentoContas Exception : ' . $e->getMessage()) ;
            return [];
        }
    }

    public function existeConta( $cdentidade )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.existeConta :' . $cdentidade  );

            return Bcnt::existeConta( $cdentidade, $this->logchannel);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.existeConta ERROR QueryException : ' . $e->getMessage() );
            return [];
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.existeConta Exception : ' . $e->getMessage()) ;
            return [];
        }
    }

    public function getSaldosConta( $cdentidade )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getSaldosConta :' . $cdentidade  );

            return Bcnt::getSaldosEntidade( $cdentidade, $this->logchannel);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getSaldosConta ERROR QueryException : ' . $e->getMessage() );
            return -1;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getSaldosConta Exception : ' . $e->getMessage()) ;
            return -1;
        }
    }

    public function getSituacaoConta( $cdentidade )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getSituacaoConta :' . $cdentidade  );

            return Bcnt::getSituacaoEntidade( $cdentidade, $this->logchannel);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getSituacaoConta ERROR QueryException : ' . $e->getMessage() );
            return -1;
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getSituacaoConta Exception : ' . $e->getMessage()) ;
            return -1;
        }
    }

    public function getNmSituacaoConta( $situacao )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getNmSituacaoConta :' . $situacao  );

            return BtpAcont::getNomeSituacao( $situacao, $this->logchannel);
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getNmSituacaoConta ERROR QueryException : ' . $e->getMessage() );
            return "";
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( 'ApiRepository.getNmSituacaoConta Exception : ' . $e->getMessage()) ;
            return "";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MOVIMENTOS 
    |--------------------------------------------------------------------------
    */
    public function getMovimentos( $parametro )
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getMovimentos');

            $movimentos = Conta::getAllMovimentosConta( $parametro, 1);

            return $movimentos;
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return $e->getMessage();
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return $e->getMessage();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BPARAMETROS 
    |--------------------------------------------------------------------------
    */
    public function getBIC()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getBIC');

            $bparametros = new BParametros( $this->logchannel , $this->isConnProduction );

            return $bparametros->getBIC();
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return $e->getMessage();
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return $e->getMessage();
        }
    }
    
    public function getParametros()
    {
        try{
            
            \Log::channel($this->logchannel)->info( 'ApiRepository.getParametros');

            $bparametros = new BParametros( $this->logchannel , $this->isConnProduction );

            return $bparametros->getParametros();
           
        }catch (\Illuminate\Database\QueryException $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return $e->getMessage();
        }catch (\Exception $e){
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return $e->getMessage();
        }
    }

}