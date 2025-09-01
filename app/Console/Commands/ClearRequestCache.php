<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClearRequestCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-request-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the request cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

           // "C:\Program Files\PHP\v8.2.11\php.exe" "artisan" app:clear-request-cache > "NUL" 2>&1

            // Clear the cache
            \Log::channel('commands')->info($this->description);

            //Cache::flush();
            Cache::forget('request_count');
            Cache::forget('request_monitoring_count');

            \Log::channel('commands')->info('Request cache cleared successfully.');
            
        }catch(\Exception $e){
            \Log::channel('commands')->error('Request cache NOT cleared.');
            \Log::channel('commands')->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
      
    }
}
