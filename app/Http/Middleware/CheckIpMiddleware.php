<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class CheckIpMiddleware 
{

    private $logchannel = 'jwt';
    private $whiteIps = [                     
    ];

    public function __construct() 
    {
        $this->whiteIps = config('enums.whiteIps');
    }
                        
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        \Log::channel($this->logchannel)->info( '-----------------BEGIN CHECK IP------------------------');
        \Log::channel($this->logchannel)->info( 'CheckIpMiddleware IP    : ' . $request->ip());
        \Log::channel($this->logchannel)->info( 'Allowed IPs : ' .print_r($this->whiteIps, true));
        if (!in_array($request->ip(), $this->whiteIps) ) {
            \Log::channel($this->logchannel)->error('ERROR: IP not allowed. Return 401');
            $json = ['message' => 'Unauthorized'];
            return response()->json($json, 401);
        }
        \Log::channel($this->logchannel)->info('IP allowed.');
     
        return $next($request);
    }

    public function terminate($request, $response)
	{
		//Log::info( 'Middleware CheckIpMiddleware terminate' );     
        //Log::info( '------------------------------' );
	}

}
