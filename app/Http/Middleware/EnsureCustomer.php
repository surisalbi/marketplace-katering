<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()?->role !== 'customer') {
            abort(403, 'Hanya untuk customer.');
        }

        return $next($request);
    }
}