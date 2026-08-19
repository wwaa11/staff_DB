<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('token') !== env('API_TOKEN')) {
            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }

        return $next($request);
    }
}
