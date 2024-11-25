<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
    public function handle($request, \Closure $next, ...$guards)
    {
        // Fetch the user using your custom method
        $user = User::findUserByToken();

        // Check if the user is valid and an instance of User (or Authenticatable)
        if ($user instanceof User) {
            // Set the authenticated user
            Auth::setUser($user);
            $request->request->add(['session_user' => $user]);

            // Proceed with the request
            return $next($request);
        }

        // If the user is not found or invalid, return an unauthorized response
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized Access Request',
        ], 401);
    }

}
