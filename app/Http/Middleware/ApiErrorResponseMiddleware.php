<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiErrorResponseMiddleware
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
        $response = $next($request);

        // If it's already a JSON response, return as is
        if ($response instanceof JsonResponse) {
            return $response;
        }

        // For error responses, ensure consistent format
        if ($response->status() >= 400) {
            return response()->json([
                'error' => $response->statusText(),
                'message' => $response->getContent()
            ], $response->status());
        }

        return $response;
    }
}