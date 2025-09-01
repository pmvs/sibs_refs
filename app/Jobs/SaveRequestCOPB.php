<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\PSPCOPRequest;
use App\Models\CopPayload;

class SaveRequestCOPB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $logchannel = 'copb';
    private $info = [];
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
    public function __construct( $info )
    {
        $this->logchannel = 'copb';
        $this->info = $info;
        //\Log::channel($this->logchannel)->info('JOB COPS CREATED');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //\Log::channel($this->logchannel)->info('JOB COPB HANDLE');
        //\Log::channel($this->logchannel)->info(print_r($this->info, true));  
        try {
            $request_id = $this->saveRequestToDB();
            \Log::channel($this->logchannel)->info('JOB COPB FINNISH ' . $request_id );
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ .  ' ' . $e->getMessage()); 
        }
    }

    public function failed()
    {
        $datainfo = 'SaveRequestCOPB JOB failed : ' . print_r($this->info, true);
        \Log::channel($this->logchannel)->info($datainfo);
    }

    private function saveRequestToDB()
    {
        //\Log::channel($this->logchannel)->info('SAVE Request and response to DB PSPCOPRequest'); 
        try {
            $pedido = [
                'dt_pedido' => date('Y-m-d'),
                'token_used' => $this->info['token_used'] , 
                'payload' => json_encode($this->info['input'], JSON_UNESCAPED_SLASHES), 
                'response' => json_encode($this->info['response'], JSON_UNESCAPED_SLASHES),
                'audience' => $this->info['audience'],
                'correlation_id' => $this->info['correlation_id'],
                'n_netcaixa' => $this->info['contrato'],
                'user_id' => $this->info['user_id'], 
                'timeelapsed' => $this->info['time_elapsed_secs'],
                'http_code_response' => $this->info['httpcode'],
                'psp_origin_code' => $this->info['psporigincode'],
            ];
           
            $requestBP = PSPCOPRequest::insertGetId( $pedido);
            $request_id =  $requestBP;

            //\Log::channel($this->logchannel)->info('Request saved to db with id ' .  $request_id  ); 
            
            return $request_id;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Request NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
            return 0;
        }
      
            
    }

}
