<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.navbar.merchant', function ($view) {
            $baru = $bayar = 0;

            if (Auth::check()) {
                $mid = DB::table('merchant')->where('user_id', Auth::id())->value('id');

                $baru = DB::table('pesanan')->where('merchant_id', $mid)->where('status', 'pending')->count();
                $bayar = DB::table('pembayaran')
                    ->join('invoice', 'invoice.id', '=', 'pembayaran.invoice_id')
                    ->where('invoice.merchant_id', $mid)
                    ->where('pembayaran.status', 'pending')
                    ->count();
            }

            $view->with(compact('baru', 'bayar'));
        });
    }
}
