<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log the incoming request
        Log::info('Incoming Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'headers' => $request->header(),
            'input' => $request->all()
        ]);

        $response = $next($request);

        // Log the outgoing response
        Log::info('Outgoing Response', [
            'status' => $response->status(),
            'headers' => $response->headers->all(),
            'content' => $response->getContent()
        ]);

        return $response;
    }
}