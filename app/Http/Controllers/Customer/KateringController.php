<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KateringController extends Controller
{
    private const CARD_COLUMNS = [
        'menu.id', 'menu.nama', 'menu.deskripsi', 'menu.harga', 'menu.foto',
        'kategori_menu.nama as kategori_nama',
        'merchant.nama_usaha', 'merchant.kab_kota', 'merchant.provinsi',
    ];

    public function index(Request $request)
    {
        $f = $request->validate([
            'q'        => 'nullable|string|max:100',
            'kategori' => 'nullable|integer',
            'kota'     => 'nullable|string|max:30',
            'min'      => 'nullable|numeric|min:0',
            'max'      => 'nullable|numeric|min:0',
            'sort'     => 'nullable|in:terbaru,termurah,termahal',
            'merchant' => 'nullable|integer',
        ]);

        [$col, $dir] = match ($f['sort'] ?? 'terbaru') {
            'termurah' => ['menu.harga', 'asc'],
            'termahal' => ['menu.harga', 'desc'],
            default    => ['menu.id', 'desc'],
        };

        $menus = $this->baseQuery()
            ->select(self::CARD_COLUMNS)
            ->when(filled($f['q'] ?? null), function ($q) use ($f) {
                $like = '%' . $f['q'] . '%';
                $q->where(fn ($w) => $w
                    ->where('menu.nama', 'like', $like)
                    ->orWhere('merchant.nama_usaha', 'like', $like)
                    ->orWhere('menu.deskripsi', 'like', $like));
            })
            ->when(filled($f['kategori'] ?? null), fn ($q) => $q->where('menu.kategori_menu_id', $f['kategori']))
            ->when(filled($f['merchant'] ?? null), fn ($q) => $q->where('menu.merchant_id', $f['merchant']))
            ->when(filled($f['kota'] ?? null), fn ($q) => $q->where('merchant.kab_kota', $f['kota']))
            ->when(filled($f['min'] ?? null), fn ($q) => $q->where('menu.harga', '>=', $f['min']))
            ->when(filled($f['max'] ?? null), fn ($q) => $q->where('menu.harga', '<=', $f['max']))
            ->orderBy($col, $dir)
            ->orderByDesc('menu.id')
            ->paginate(12)
            ->withQueryString();

        $kategori = DB::table('kategori_menu')->orderBy('nama')->get();

        $kotaList = DB::table('merchant')
            ->where('is_active', 1)
            ->whereNotNull('kab_kota')
            ->where('kab_kota', '!=', '')
            ->distinct()
            ->orderBy('kab_kota')
            ->pluck('kab_kota');

        $keys = ['q', 'kategori', 'kota', 'min', 'max', 'merchant'];

        $hasFilter = collect($request->only($keys))->filter(fn ($v) => filled($v))->isNotEmpty();

        $merchantFilter = filled($f['merchant'] ?? null)
            ? DB::table('merchant')->where('id', $f['merchant'])->value('nama_usaha')
            : null;

        return view('customer.katering.index', compact('menus', 'kategori', 'kotaList', 'hasFilter', 'merchantFilter'));
    }

    public function show($id)
    {
        $menu = $this->baseQuery()
            ->select('menu.*',
                'kategori_menu.nama as kategori_nama',
                'merchant.nama_usaha', 'merchant.kab_kota', 'merchant.provinsi',
                'merchant.deskripsi as merchant_deskripsi', 'merchant.foto as merchant_foto')
            ->where('menu.id', $id)
            ->first();

        abort_if(! $menu, 404);

        $lainnya = $this->baseQuery()
            ->select(self::CARD_COLUMNS)
            ->where('menu.merchant_id', $menu->merchant_id)
            ->where('menu.id', '!=', $menu->id)
            ->orderByDesc('menu.id')
            ->limit(4)
            ->get();

        return view('customer.katering.show', compact('menu', 'lainnya'));
    }

    // Hanya menu aktif dari merchant terverifikasi
    private function baseQuery()
    {
        return DB::table('menu')
            ->join('merchant', 'merchant.id', '=', 'menu.merchant_id')
            ->join('kategori_menu', 'kategori_menu.id', '=', 'menu.kategori_menu_id')
            ->whereNull('menu.deleted_at')
            ->where('menu.is_available', 1)
            ->where('merchant.is_active', 1);
    }
}