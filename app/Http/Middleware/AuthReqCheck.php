<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthReqCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $auth = auth()->user();
        $header = $request->header();
        if (isset($header['x-api-key']) && $header['x-api-key'][0] == '_@@jbbrd2023fuelmatix@@_' && $auth != null) {
            $request->request->add(['session_user' => $auth]);
            return $next($request);
        }
        return response()->json(['status' => 401, 'error' => ['error' => ['Unauthorized Request']]]);
    }
}
