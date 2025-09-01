<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BancoPortugal\ProxyLookup\Cop;
use App\Models\PSPCOPRequest;
use App\Models\CopPayload;

use App\Jobs\SaveRequestCOPS;
use App\Jobs\SaveRequestCOPB;

use App\Services\BancoPortugal\BancoPortugalService;

class VopApi extends Controller
{

    private $logchannel = 'vop';
    private $withJobs = false;
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    
    /**
     * Operation getHealth
     *
     * /health - GET.
     *
     *
     * @return Http response
     */
    public function getHealth(Request $request)
    {
        $this->logchannel = 'health_vop';
        try{
            \Log::channel($this->logchannel)->info( '___________Health '.strtoupper(config('app.sigla_psp')).' VOP API______________' );
            return response('Health '.strtoupper(config('app.sigla_psp')).' VOP API', 200);
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage() );
            return response('', 400);
        }
    }


    private function getCodigoBanco()
    {
        $cdbanco = '0000';
        //caso nao consiga ir ao bparametros
        switch( config('app.sigla') ) {
            case 'TVD':
                $cdbanco = '5340';
                break;
            case 'MAF':
                $cdbanco = '5200';
                break;
            case 'BOM':
                $cdbanco = '0098';
                break;
            case 'CHM':
                $cdbanco = '0097';
                break;
            default:
                $cdbanco = '0000';
                break;
        }
        return $cdbanco;
    }



    /*
    |--------------------------------------------------------------------------
    | COUNTERS  
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

        \Log::channel($this->logchannel)->info($datainfo .  ' ' . $dt);
 

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

            $datainfo ='Inicio Pedido: ' .  $this->dt_inicio_gba . PHP_EOL . 
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

            $datainfo = 'Inicio Pedido: ' .  $this->dt_inicio_operacao . PHP_EOL . 
                        'Resposta ao pedido : ' . $this->dt_resposta_operacao . PHP_EOL . 
                        'Tempos Operação : ' . $differencegba ;
 
            \Log::channel($this->logchannel)->info($datainfo);
           
        } catch(Exception $e){
            \Log::channel($this->logchannel)->error($e->getMessage());
        }
    }


}
