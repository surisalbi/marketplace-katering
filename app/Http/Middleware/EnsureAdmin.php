<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Hanya untuk admin.');
        }

        return $next($request);
    }
}