<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function edit()
    {
        $customer = DB::table('customer')->where('user_id', Auth::id())->first();

        return view('customer.profile', compact('customer'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_kantor' => 'required|string|max:150',
            'nama_pic'    => 'required|string|max:100',
            'telp'        => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
        ]);

        DB::transaction(function () use ($data) {
            DB::table('customer')->where('user_id', Auth::id())->update($data);
            DB::table('users')->where('id', Auth::id())->update([
                'name'       => $data['nama_pic'],
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('customer.dashboard')->with('status', 'Profil berhasil diperbarui.');
    }
}