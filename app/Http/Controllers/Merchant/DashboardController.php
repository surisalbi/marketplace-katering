<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public const REQUIRED = ['nama_usaha', 'no_telepon', 'email', 'provinsi', 'kab_kota', 'alamat', 'deskripsi', 'foto'];

    public function index()
    {
        $merchant = DB::table('merchant')->where('user_id', Auth::id())->first();

        $filled = collect(self::REQUIRED)->filter(fn ($f) => filled($merchant->$f ?? null))->count();
        $percent = (int) round($filled / count(self::REQUIRED) * 100);

        return view('merchant.dashboard', compact('merchant', 'percent'));
    }
}