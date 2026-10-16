<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    /**
     * Daftar merchant + pencarian + filter status.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $merchants = DB::table('merchant as m')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->select(
                'm.id',
                'm.nama_usaha',
                'm.no_telepon',
                'm.email',
                'm.provinsi',
                'm.kab_kota',
                'm.foto',
                'm.is_active',
                'm.created_at',
                'u.name as pemilik'
            )
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('m.nama_usaha', 'like', "%{$search}%")
                      ->orWhere('m.email', 'like', "%{$search}%")
                      ->orWhere('m.no_telepon', 'like', "%{$search}%")
                      ->orWhere('u.name', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('m.is_active', (int) $status))
            ->orderByDesc('m.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.merchants.index', compact('merchants', 'search', 'status'));
    }

    /**
     * Detail merchant.
     */
    public function show(int $id)
    {
        $merchant = DB::table('merchant as m')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->select(
                'm.*',
                'u.name as pemilik',
                'u.email as email_akun',
                'u.is_active as akun_aktif'
            )
            ->where('m.id', $id)
            ->first();

        abort_if(!$merchant, 404, 'Merchant tidak ditemukan.');

        return view('admin.merchants.show', compact('merchant'));
    }

    /**
     * Toggle aktif/nonaktif merchant.
     */
    public function toggle(int $id)
    {
        $merchant = DB::table('merchant')->where('id', $id)->first();

        if (!$merchant) {
            return redirect()->back()->with('error', 'Merchant tidak ditemukan.');
        }

        $newStatus = $merchant->is_active ? 0 : 1;

        DB::table('merchant')
            ->where('id', $id)
            ->update([
                'is_active'  => $newStatus,
                'updated_at' => now(),
            ]);

        $nama = $merchant->nama_usaha ?? 'Merchant';

        $message = $newStatus
            ? "Merchant {$nama} berhasil diaktifkan."
            : "Merchant {$nama} berhasil dinonaktifkan.";

        return redirect()->back()->with('success', $message);
    }
}