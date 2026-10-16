<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Ubah password user yang sedang login (dipanggil via AJAX dari popup).
     * Jika validasi gagal, Laravel otomatis membalas JSON 422 karena request memakai header Accept: application/json.
     */
    public function update(Request $request)
    {
        $user = DB::table('users')->where('id', $request->user()->id)->first();

        $request->validate([
            'password_lama' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (! Hash::check($value, $user->password)) {
                    $fail('Password lama salah.');
                }
            }],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ], [
            'required'  => ':attribute wajib diisi.',
            'min'       => ':attribute minimal :min karakter.',
            'confirmed' => 'Konfirmasi password baru tidak cocok.',
            'different' => 'Password baru harus berbeda dari password lama.',
        ], [
            'password_lama' => 'Password lama',
            'password_baru' => 'Password baru',
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($request->password_baru),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}