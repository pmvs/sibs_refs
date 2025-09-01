<?php 

namespace App\Services\Testes;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;

class Indisponibilidade extends Controller
{
    private $logchannel = 'testes';
    private $testNumber = 0;
    private $tabPosition =0;
    private $tipoIndisponibilidade = ['add', 'update', 'list'];
    private $typeOfIndisponibilidade = '';
    private $fields = [];
    private $indisponibilidadeInterface = null;

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
        \Log::channel($this->logchannel)->info('__construct Indisponibilidade');
    }

    public function __construct_3($logchannel, $tabPosition, $testNumber) 
    {
        $this->logchannel = $logchannel;
        $this->tabPosition =$tabPosition;
        $this->testNumber = $testNumber;

        \Log::channel($this->logchannel)->info('__construct_3 Indisponibilidade');
    }

    public function setTypeOfIndisponibilidade( $i )
    {
        $this->typeOfIndisponibilidade = $this->tipoIndisponibilidade[$i];
    }

    public function getTypeOfIndisponibilidade()
    {
        return $this->typeOfIndisponibilidade;
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

            if ( $this->testNumber > 22 ) { //listas de indisponibilidades 
                app()->singleton(IndisponibilidadeInterface::class, function ($app) {
                    return new ListaIndisponibilidade($this->logchannel, $this->tabPosition, $this->testNumber);
                });
                $this->indisponibilidadeInterface = app()->make(IndisponibilidadeInterface::class);
                \Log::channel($this->logchannel)->info('SET IndisponibilidadeInterface to ListaIndisponibilidade');
                return '';
            }

            //split into programada or nao programada
            $split = ($this->testNumber < 15) ? 'programada' : 'nao_programada';

            switch ( $split ){

                case 'programada': 
                    app()->singleton(IndisponibilidadeInterface::class, function ($app) {
                        return new IndisponibilidadeProgramada($this->logchannel, $this->tabPosition, $this->testNumber);
                    });
                    $this->indisponibilidadeInterface = app()->make(IndisponibilidadeInterface::class);
                    \Log::channel($this->logchannel)->info('SET IndisponibilidadeInterface to IndisponibilidadeProgramada');
                    break;

                case 'nao_programada': 
                    app()->singleton(IndisponibilidadeInterface::class, function ($app) {
                        return new IndisponibilidadeNaoProgramada($this->logchannel, $this->tabPosition, $this->testNumber);
                    });
                    $this->indisponibilidadeInterface = app()->make(IndisponibilidadeInterface::class);
                    \Log::channel($this->logchannel)->info('SET IndisponibilidadeInterface to IndisponibilidadeNaoProgramada');
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

            $result = $this->indisponibilidadeInterface->efetuaPedido( $this->fields );

            \Log::channel($this->logchannel)->info('Resultado interface com ' . print_r($result, true));

            return $result;

          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: efetuaPedido inválido -> ' . $e->getMessage();
        }
    
    }


}