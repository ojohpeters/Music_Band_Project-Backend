<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and has an 'admin' role
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // If the user doesn't have the 'admin' role, return an unauthorized response
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
