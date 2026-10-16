<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit()
    {
        $merchant = DB::table('merchant')->where('user_id', Auth::id())->first();

        return view('merchant.profile', compact('merchant'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_usaha' => 'required|string|max:100',
            'no_telepon' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'email'      => 'required|email|max:50',
            'provinsi'   => 'required|string|max:30',
            'kab_kota'   => 'required|string|max:30',
            'alamat'     => 'required|string|max:255',
            'deskripsi'  => 'nullable|string|max:2000',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'bank_nama'        => 'nullable|string|max:50|required_with:bank_no_rekening,bank_atas_nama',
            'bank_no_rekening' => ['nullable', 'regex:/^[0-9\s\-]{5,30}$/', 'required_with:bank_nama,bank_atas_nama'],
            'bank_atas_nama'   => 'nullable|string|max:100|required_with:bank_nama,bank_no_rekening',
        ]);

        $merchant = DB::table('merchant')->where('user_id', Auth::id())->first();

        unset($data['foto']);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            // extension() dari mime asli file, bukan dari nama file buatan user
            $filename = Str::uuid() . '.' . $file->extension();
            $file->move(public_path('uploads/profile'), $filename);

            // hapus foto lama
            if ($merchant?->foto && File::exists(public_path($merchant->foto))) {
                File::delete(public_path($merchant->foto));
            }

            // simpan path relatif dari folder public
            $data['foto'] = 'uploads/profile/' . $filename;
        }

        DB::table('merchant')->where('user_id', Auth::id())->update($data);

        return redirect()->route('merchant.dashboard')
            ->with('status', 'Data merchant berhasil disimpan.');
    }
}