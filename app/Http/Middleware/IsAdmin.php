<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
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
       
        // Check if the user is authenticated and has the 'admin' role
        if ($request->user() && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
       
        return $next($request);
    }
}

