<?php 

namespace App\Services\Testes;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;

class PL extends Controller
{
    private $logchannel = 'testes';
    private $testNumber = 0;
    private $tabPosition =0;
    private $tipoPL = ['insert', 'delete', 'default'];
    private $fields = [];
    private $plInterface = null;

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
        \Log::channel($this->logchannel)->info('__construct PL');
    }

    public function __construct_3($logchannel, $tabPosition, $testNumber) 
    {
        $this->logchannel = $logchannel;
        $this->tabPosition =$tabPosition;
        $this->testNumber = $testNumber;

        \Log::channel($this->logchannel)->info('__construct_3 PL');
    }

  
 

    public function setFields( $data )
    {
        //\Log::channel($this->logchannel)->info('setFields data: ' . print_r($data, true));
        $this->fields = $data;
        // \Log::channel($this->logchannel)->info('setFields $this->fields: ' . print_r($this->fields, true));
    }

    public function setInterface()
    {
        try {

            \Log::channel($this->logchannel)->info('Set INTERFACES...');

            if ( $this->testNumber == 11 ) { //consulta contatos 
                app()->singleton(PLInterface::class, function ($app) {
                    return new PLConsulta($this->logchannel, $this->tabPosition, $this->testNumber);
                });
                $this->plInterface = app()->make(PLInterface::class);
                \Log::channel($this->logchannel)->info('SET PLInterface to PLConsulta');
                return '';
            }


            if ( $this->testNumber == 12) { //consulta ibans 
                app()->singleton(PLInterface::class, function ($app) {
                    return new PLConsulta($this->logchannel, $this->tabPosition, $this->testNumber);
                });
                $this->plInterface = app()->make(PLInterface::class);
                \Log::channel($this->logchannel)->info('SET PLInterface to PLConsulta');
                return '';
            }

            
            if ( $this->testNumber > 12 && $this->testNumber < 15 )  { //dissociacao 
                app()->singleton(PLInterface::class, function ($app) {
                    return new PLDissociacao($this->logchannel, $this->tabPosition, $this->testNumber);
                });
                $this->plInterface = app()->make(PLInterface::class);
                \Log::channel($this->logchannel)->info('SET PLInterface to PLDissociacao');
                return '';
            }

            // if ( $this->testNumber == 15 || $this->testNumber == 16 ||  $this->testNumber == 17  || $this->testNumber == 18  ) { //notificacoes
            //     app()->singleton(PLInterface::class, function ($app) {
            //         return new PLAssociacao($this->logchannel, $this->tabPosition, $this->testNumber);
            //     });
            //     $this->plInterface = app()->make(PLInterface::class);
            //     \Log::channel($this->logchannel)->info('SET PLInterface to PLAssociacao');
            //     return '';
            // }

            

            //split into  insert, 
            $split = ($this->testNumber < 10 || $this->testNumber > 14 ) ? 'insert' : 'confirmation';

            switch ( $split ){

                case 'insert': 
                    app()->singleton(PLInterface::class, function ($app) {
                        return new PLAssociacao($this->logchannel, $this->tabPosition, $this->testNumber);
                    });
                    $this->plInterface = app()->make(PLInterface::class);
                    \Log::channel($this->logchannel)->info('SET PLInterface to PLAssociacao');
                    break;

                case 'confirmation': 
                    app()->singleton(PLInterface::class, function ($app) {
                        return new PLConfirmacao($this->logchannel, $this->tabPosition, $this->testNumber);
                    });
                    $this->plInterface = app()->make(PLInterface::class);
                    \Log::channel($this->logchannel)->info('SET PLInterface to PLConfirmacao');
                    break;

                 default:
                     return 'setInterface inválido';
             }

             return '';

          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: setInterface inválido -> ' . $e->getMessage();
        }
    
    }

    public function efetuaPedido()
    {
        try {

            \Log::channel($this->logchannel)->info('Efetua pedido interface com ' . print_r($this->fields, true));

            $result = $this->plInterface->efetuaPedido( $this->fields );

            \Log::channel($this->logchannel)->info('Resultado interface com ' . print_r($result, true));

            return $result;

          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: efetuaPedido inválido -> ' . $e->getMessage();
        }
    
    }


}