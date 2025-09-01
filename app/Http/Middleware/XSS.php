<?php

namespace App\Http\Middleware;

use Log;
use Closure;

class XSS
{

    public function __construct( ) 
	{
        // Log::info( '------------------------------' );
		// Log::info( 'Middleware XSS __construct' );
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
        try {
            Log::info('Middleware XSS handle...');
            $userInput = $request->all();
            Log::info('UserInput Before : ' . print_r( $userInput , true) );
            array_walk_recursive($userInput, function (&$userInput) {
                $userInput = strip_tags($userInput);
            });
            Log::info('UserInput After : ' . print_r( $userInput , true) );
            $request->merge($userInput);
            Log::info('Middleware XSS next...');
            return $next($request);
        }catch( \Exception $e ) {
            \Log::channel('daily')->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
        }
     
    }

    public function terminate($request, $response)
	{
		//Log::info('Middleware XSS terminate...');
        // Log::info( '------------------------------' );
	}
}
