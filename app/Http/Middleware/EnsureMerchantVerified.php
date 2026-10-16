<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnsureMerchantVerified
{
    public function handle(Request $request, Closure $next)
    {
        $verified = DB::table('merchant')
            ->where('user_id', Auth::id())
            ->where('is_active', 1)
            ->exists();

        if (! $verified) {
            return redirect()->route('merchant.dashboard')
                ->withErrors(['merchant' => 'Usaha Anda belum terverifikasi. Menu baru bisa dikelola setelah verifikasi admin.']);
        }

        return $next($request);
    }
}