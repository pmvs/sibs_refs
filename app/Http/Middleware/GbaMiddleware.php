<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class GbaMiddleware
{

    private $logchannel = 'jwt';

    
    public function __construct()
    {
        \Log::channel($this->logchannel)->info( '__construct GbaMiddleware');  
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

        $ip = $request->ip();

        \Log::channel($this->logchannel)->info( 'GbaMiddleware IP : ' . $ip); 

        $listaips = config('app.white_ip_oba');

        $myArr = explode(',', $listaips);

        \Log::channel($this->logchannel)->info( 'White: ' . print_r( $myArr, true)); 

        if ( ! in_array($ip, $myArr)) {
            \Log::channel($this->logchannel)->info( 'IP not authorized');
            $json = ['message' => 'Unauthorized'];
            return response()->json($json, 401);
        }

        // if ( $ip != '148.69.165.144' )  
        // {
        //     if ($ip!= '200.52.0.225'  )  
        //     {
        //         if ($ip!= '62.48.152.10'  )  
        //         {
        //             Log::info( 'IP not authorized');
        //             return redirect('/');
        //         }
        //     }
        // }

        return $next($request);

    }

	public function terminate($request, $response)
	{
	
	}


}
