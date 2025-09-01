<?php

namespace App\Http\Middleware;

use Closure;

class DomainMiddleware
{
    private $logchannel = 'domain';
    private $domain = 'plqual';

    public function handle($request, Closure $next)
    {

      

        $host = $request->getHost();
        \Log::channel($this->logchannel)->info( ' HOST : ' . $host);

        if (strpos($host, 'plqual.ccammafra.pt') !== false) {
            // Set the domain in the request
            $this->domain = config('app.domain_plcp');
            \Log::channel($this->logchannel)->info( ' SET DOMAIN : ' . $this->domain);
            $request->merge(['domain' => $this->domain]);
        } elseif (strpos($host, 'vop-cert.ccammafra.pt') !== false) {
            // Set the domain in the request
            $this->domain = config('app.domain_vop');
            \Log::channel($this->logchannel)->info( ' SET DOMAIN : ' . $this->domain);
            $request->merge(['domain' => $this->domain]);
        } else {
            \Log::channel($this->logchannel)->warning( ' ABORT DOMAIN MIDDLEWARE - return 503 - Service unavailable' );
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    'code' => 'E001',
                    'value' => 'Service unavailable'
                ],
            ], 503);
        }
        \Log::channel($this->logchannel)->info( ' DOMAIN MIDDLEWARE next' );
        return $next($request);

    }
}