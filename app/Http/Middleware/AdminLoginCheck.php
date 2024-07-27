<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginCheck
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
        $path = \Illuminate\Support\Facades\Request::route()->getName();
        if (Auth::guard('admin')->check()) {
            if($path == 'admin.auth') {
                return redirect()->route('admin.dashboard', 'dashboard');
            } else {
                return $next($request);
            }
        } else {
            if($path == 'admin.auth') {
                return $next($request);
            } else {
                return redirect()->route('admin.auth', 'login');
            }
        }
    }
}
