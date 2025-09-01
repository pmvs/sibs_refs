<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use App\Services\BancoPortugal\BancoPortugalService;

class ChargeRequestPlAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $logchannel = 'carga';
    private $customer_identifier = [];
    private $contrato = 0;
    private $counter = 0;
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
    public function __construct( $customer_identifier, $i )
    {
        $this->logchannel = 'carga';
        $this->customer_identifier = $customer_identifier;
        $this->contrato = 0;
        $this->counter = $i;
        //\Log::channel($this->logchannel)->info('JOB COPS CREATED');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::channel($this->logchannel)->info('JOB ChargeRequestPlAccount HANDLE ' . $this->counter .  ' ' . $this->customer_identifier);

        //\Log::channel($this->logchannel)->info( 'Vai invocar o servico do BP para obter nome COp do ...' .  $this->iban);
        $start_main = microtime(true);

        $content = ( new BancoPortugalService( $this->logchannel ) )->obtemIban($this->contrato,  $this->customer_identifier);
        if ( $content ) {
            \Log::channel($this->logchannel)->info( 'Serviço finalizado com SUCESSO' );
        }else {
            \Log::channel($this->logchannel)->info( 'Serviço com ERROS' );
        }
        \Log::channel($this->logchannel)->info(print_r($content, true));

        $time_elapsed_secs = microtime(true) - $start_main;
        \Log::channel($this->logchannel)->info($this->counter . '::time_elapsed_msecs: ' . ($time_elapsed_secs * 1000));

        //\Log::channel($this->logchannel)->info('JOB ChargeRequestCOPS HANDLE END ' . $this->counter . ' ' . $this->iban);

    }

    public function failed()
    {

        $datainfo = 'ChargeRequestPlAccount JOB failed';
        \Log::channel($this->logchannel)->info($datainfo);

    
    }
}
