<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $rid = $request->headers->get('X-Request-Id') ?: Str::upper(Str::random(16));
        // anexa ao request para outros componentes
        $request->headers->set('X-Request-Id', $rid);

        $response = $next($request);
        // ecoa no response
        return $response->header('X-Request-Id', $rid);
    }
}
