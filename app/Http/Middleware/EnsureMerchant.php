<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMerchant
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()?->role !== 'merchant') {
            abort(403, 'Hanya untuk merchant.');
        }

        return $next($request);
    }
}