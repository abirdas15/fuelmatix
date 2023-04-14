<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $path = \Illuminate\Support\Facades\Request::route()->getName();

        if (Auth::guard()->check()) {
            if($path == 'Spa.Auth') {
                return redirect()->route('Spa.Dashboard', '/dashboard');
            } else {
                return $next($request);
            }
        } else {
            if($path == 'Spa.Auth') {
                return $next($request);
            } else {
                return redirect()->route('Spa.Auth', 'login');
            }
        }
    }
}
