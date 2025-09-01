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

class SaveRequestCOPS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $logchannel = 'cops';
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
        $this->logchannel = 'cops';
        $this->info = $info;
        //\Log::channel($this->logchannel)->info('JOB COPS CREATED');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //\Log::channel($this->logchannel)->info('JOB COPS HANDLE');
        //\Log::channel($this->logchannel)->info(print_r($this->info, true));  
        try {

            $request_id = $this->saveRequestToDB();
            if ( $request_id > 0 ) {
                $payload_id = $this->savePayloadToDB($request_id);
            }
            \Log::channel($this->logchannel)->info('JOB COPS FINNISH ' . $request_id);

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ .  ' ' . $e->getMessage()); 
        }
    }

    public function failed()
    {
        $datainfo = 'SaveRequestCOPS JOB failed : ' . print_r($this->info, true);
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

    private function savePayloadToDB($request_id)
    {
        //grava payload na BD
        //\Log::channel($this->logchannel)->info('SAVE Payload to DB'); 
        try {
           // $this->payloadJson = json_decode($this->payloadJson);
            $banco = $this->getCodigoBanco();

            $payload = [
                'dt_pedido' => date('Y-m-d'),
                'request_id' => $request_id ,
                'n_netcaixa' => $this->info['contrato'],
                'user_id' => $this->info['user_id'],
                'psp_code' => $banco, 
                'psp_code_destination' => $this->info['psporigincode'],
                'iban' => $this->info['iban'],
                'account_holder' => $this->info['response']['account_holder'], 
                'commercial_name' => $this->info['response']['commercial_name'], 
                'correlation_id_origin' => $this->info['correlation_id'],
                'timestamp' => $this->info['timestamp'],
                'success' => 1,
                'correlation_id' => $this->info['correlation_id'],
                'message' => $this->info['response']['message'], 
                'errors_codes' => '',
                'errors_values' => '',
                'http_code' => $this->info['httpcode'] 
            ];

            $payloadBD = CopPayload::insertGetId( $payload );
            $payload_id =  $payloadBD;
         
            //\Log::channel($this->logchannel)->info('Payload saved to db with id ' . $payload_id );

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error('Payload NOT saved to db'); 
            \Log::channel($this->logchannel)->error($e->getMessage()); 
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


}
