<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use App\Services\BancoPortugal\BancoPortugalService;

class ChargeRequestCOPB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $logchannel = 'carga';
    private $copb = [];
    private $contrato = 0;
    private $counter = 0;
    private $psp_destination = '0000';
    private $lista = [];
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct( $pspdest, $copb, $lista, $i )
    {
        $this->logchannel = 'carga';
        $this->copb = $copb;
        $this->contrato = 0;
        $this->counter = $i;
        $this->psp_destination = $pspdest;
        $this->lista = $lista;
        //\Log::channel($this->logchannel)->info('JOB COPS CREATED');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::channel($this->logchannel)->info('JOB ChargeRequestCOPB HANDLE ' . $this->counter . ' ' .  $this->psp_destination);

        $start_main = microtime(true);
        $content = ( new BancoPortugalService( $this->logchannel ) )->invocaCopBTeste($this->contrato, 2000, $this->lista, $this->psp_destination);
        $time_elapsed_secs = microtime(true) - $start_main;
        \Log::channel($this->logchannel)->info($this->counter . '::time_elapsed_msecs: ' . ($time_elapsed_secs * 1000));
        //\Log::channel($this->logchannel)->info(print_r($content, true));

        //\Log::channel($this->logchannel)->info('JOB ChargeRequestCOPB HANDLE END ' . $this->counter . ' ' .  $this->psp_destination);

    }

    
    public function failed()
    {

        $datainfo = 'ChargeRequestCOPB JOB failed'. $this->counter . ' ' .  $this->psp_destination;
        \Log::channel($this->logchannel)->info($datainfo);

    
    }

}
