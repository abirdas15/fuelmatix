<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
        $path = $request->path();
        // Check if the request path is for the admin authentication
        if (strpos($path, 'admin/auth/login') === 0) {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard', 'dashboard');
            } else {
                return $next($request);
            }
        }

        // Check if the request path is for the general authentication
        if (strpos($path, 'auth/login') === 0) {
            if (Auth::check()) {
                return redirect()->route('spa.dashboard', 'dashboard');
            } else {
                return $next($request);
            }
        }

        // Check if the path is an admin route
        if (strpos($path, 'admin') === 0) {
            if (!Auth::guard('admin')->check()) {
                return redirect('/admin/auth/login');
            } else {
                return $next($request);
            }
        }

        // Check if the path is an auth route
        if (strpos($path, 'auth') === 0) {
            if (!Auth::check()) {
                return redirect('/auth/login');
            } else {
                return $next($request);
            }
        }

        // For all other routes, redirect to general login if not authenticated
        if (!Auth::check()) {
            return redirect('/auth/login');
        }

        return $next($request);
    }
}
