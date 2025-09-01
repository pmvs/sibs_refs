<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        header_remove("X-Powered-By");
       // header_remove("Connection");
        header_remove("X-RateLimit-Limit");
        header_remove("X-RateLimit-Remaining");
       // header_remove("Cache-Control");

        return $next($request)
        ->header('Access-Control-Allow-Origin','*')
        ->header('Access-Control-Allow-Methods','GET,POST,DELETE')
        ->header('Access-Control-Allow-Headers','Content-Type, Authorization');
    }
}
