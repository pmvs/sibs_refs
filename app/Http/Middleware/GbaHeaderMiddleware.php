<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class GbaHeaderMiddleware
{

    
    public function __construct()
    {
        //Log::info( '__construct GbaHeaderMiddleware');  
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

        $headers = getallheaders();

        Log::info('Search Request Headers : ' . print_r($headers, true) );

        if ( ! array_key_exists('X-Token', $headers) ) {
            Log::error('Token inexistente.');
            return response()->json(['error' => 'Not allowed'], 401);
        }

        $xtoken = $headers['X-Token'];
        Log::info( 'X-token : ' .   $xtoken );

        $hash = strtoupper(hash('sha256', '503622109'));
        Log::info( 'Hash : ' . $hash);

        if ( trim($hash ) != trim($headers['X-Token']) ) {
            Log::error('Token errado.');
            return response()->json(['error' => 'Not allowed'], 401);
        }

        return $next($request);

    }

	public function terminate($request, $response)
	{
	
	}


}
