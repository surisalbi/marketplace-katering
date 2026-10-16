<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $merchantId = $this->merchantId();

        $menus = DB::table('menu')
            ->leftJoin('kategori_menu', 'kategori_menu.id', '=', 'menu.kategori_menu_id')
            ->select('menu.*', 'kategori_menu.nama as kategori_nama')
            ->where('menu.merchant_id', $merchantId)
            ->whereNull('menu.deleted_at')
            ->when($request->filled('q'), fn ($q) => $q->where('menu.nama', 'like', '%' . $request->q . '%'))
            ->when($request->filled('kategori'), fn ($q) => $q->where('menu.kategori_menu_id', $request->kategori))
            ->orderByDesc('menu.id')
            ->paginate(10)
            ->withQueryString();

        $kategori = DB::table('kategori_menu')->orderBy('nama')->get();

        return view('merchant.menu.index', compact('menus', 'kategori'));
    }

    public function create()
    {
        return view('merchant.menu.form', [
            'menu'     => null,
            'kategori' => DB::table('kategori_menu')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['merchant_id']   = $this->merchantId();
        $data['is_available']  = $request->boolean('is_available');

        unset($data['foto']);
        if ($request->hasFile('foto')) {
            $data['foto'] = $this->saveFoto($request->file('foto'));
        }

        DB::table('menu')->insert($data);

        return redirect()->route('merchant.menu.index')->with('status', 'Menu berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return view('merchant.menu.form', [
            'menu'     => $this->findOwned($id),
            'kategori' => DB::table('kategori_menu')->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $menu = $this->findOwned($id);
        $data = $this->validated($request);

        $data['is_available'] = $request->boolean('is_available');

        unset($data['foto']);
        if ($request->hasFile('foto')) {
            $data['foto'] = $this->saveFoto($request->file('foto'));

            if ($menu->foto && File::exists(public_path($menu->foto))) {
                File::delete(public_path($menu->foto));
            }
        }

        DB::table('menu')->where('id', $menu->id)->update($data);

        return redirect()->route('merchant.menu.index')->with('status', 'Menu berhasil diperbarui.');
    }

    // Soft delete: baris tetap ada agar riwayat order tidak rusak
    public function destroy($id)
    {
        $menu = $this->findOwned($id);

        DB::table('menu')->where('id', $menu->id)->update(['deleted_at' => now()]);

        return redirect()->route('merchant.menu.index')->with('status', 'Menu berhasil dihapus.');
    }

    public function toggle($id)
    {
        $menu = $this->findOwned($id);

        DB::table('menu')->where('id', $menu->id)->update(['is_available' => $menu->is_available ? 0 : 1]);

        return back()->with('status', 'Status ketersediaan menu diperbarui.');
    }

    /* ---------- helpers ---------- */

    private function merchantId(): int
    {
        return (int) DB::table('merchant')->where('user_id', Auth::id())->value('id');
    }

    // Ambil menu milik merchant ini yang belum dihapus, atau 404
    private function findOwned($id): object
    {
        $menu = DB::table('menu')
            ->where('id', $id)
            ->where('merchant_id', $this->merchantId())
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $menu, 404);

        return $menu;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'kategori_menu_id' => 'required|exists:kategori_menu,id',
            'nama'             => 'required|string|max:100',
            'deskripsi'        => 'nullable|string|max:2000',
            'harga'            => 'required|numeric|min:0|max:99999999',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    }

    private function saveFoto(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->extension();
        $file->move(public_path('uploads/menu'), $filename);

        return 'uploads/menu/' . $filename;
    }
}