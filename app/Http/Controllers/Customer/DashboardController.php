<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = DB::table('customer')->where('user_id', Auth::id())->first();
        abort_if(! $customer, 403);

        $alamat = DB::table('alamat_customer')->where('customer_id', $customer->id);

        return view('customer.dashboard', [
            'customer'      => $customer,
            'alamatCount'   => (clone $alamat)->count(),
            'alamatDefault' => (clone $alamat)->where('is_default', 1)->first(),
        ]);
    }
}