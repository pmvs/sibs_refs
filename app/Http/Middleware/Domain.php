<?php 

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Domain
{
    public function handle(Request $request, Closure $next, $domain)
    {

        
        \Log::channel('domain')->info('Domain in parameters : ' . $domain);
        $domainRequest3 = $request->headers->all()['host'][0];
        \Log::channel('domain')->info('Domain in request : ' . $domainRequest3);

        if  ( $domainRequest3 !== $domain ) {
            \Log::channel('domain')->warning('Domain not allowed for this route');
            return response()->json(['message' => 'Domain not allowed for this route'], 401);
        }

        return $next($request);
    }
}