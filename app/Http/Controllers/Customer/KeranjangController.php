<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\KeranjangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KeranjangController extends Controller
{
    public function index(KeranjangService $keranjang)
    {
        $cart = $keranjang->resolve();

        $customerId = DB::table('customer')->where('user_id', Auth::id())->value('id');
        $alamat = DB::table('alamat_customer')
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $minTanggal = now()->addDays((int) config('pesanan.min_hari_pesan'))->toDateString();

        if ($cart['dropped'] > 0) {
            session()->flash('status', 'Sebagian menu sudah tidak tersedia dan dihapus dari keranjang.');
        }

        return view('customer.keranjang.index', compact('cart', 'alamat', 'minTanggal'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'menu_id' => 'required|integer',
            'qty'     => 'required|integer|min:1|max:1000',
        ]);

        $menu = DB::table('menu')
            ->join('merchant', 'merchant.id', '=', 'menu.merchant_id')
            ->where('menu.id', $data['menu_id'])
            ->whereNull('menu.deleted_at')
            ->where('menu.is_available', 1)
            ->where('merchant.is_active', 1)
            ->select('menu.id', 'menu.merchant_id')
            ->first();

        abort_if(! $menu, 404);

        $cart = session('keranjang', ['merchant_id' => null, 'items' => []]);

        if (! empty($cart['items']) && $cart['merchant_id'] != $menu->merchant_id) {
            return back()->withErrors([
                'keranjang' => 'Keranjang Anda berisi menu dari katering lain. Selesaikan atau kosongkan keranjang terlebih dahulu untuk memesan dari katering ini.',
            ]);
        }

        $cart['merchant_id'] = $menu->merchant_id;
        $current = $cart['items'][$menu->id]['qty'] ?? 0;
        $cart['items'][$menu->id] = [
            'qty'     => min(1000, $current + (int) $data['qty']),
            'catatan' => $cart['items'][$menu->id]['catatan'] ?? null,
        ];

        session(['keranjang' => $cart]);

        return redirect()->route('customer.keranjang.index')->with('status', 'Menu ditambahkan ke pesanan Anda.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'qty'       => 'required|array',
            'qty.*'     => 'required|integer|min:1|max:1000',
            'catatan.*' => 'nullable|string|max:255',
        ]);

        $cart = session('keranjang');
        if (! $cart) {
            return redirect()->route('customer.keranjang.index');
        }

        foreach ($request->input('qty') as $id => $qty) {
            if (isset($cart['items'][$id])) {
                $cart['items'][$id]['qty']     = (int) $qty;
                $cart['items'][$id]['catatan'] = $request->input("catatan.$id") ?: null;
            }
        }

        session(['keranjang' => $cart]);

        return redirect()->route('customer.keranjang.index');
    }

    public function remove($menu)
    {
        $cart = session('keranjang');

        if ($cart) {
            unset($cart['items'][$menu]);
            $cart['items'] ? session(['keranjang' => $cart]) : session()->forget('keranjang');
        }

        return redirect()->route('customer.keranjang.index');
    }

    public function clear()
    {
        session()->forget('keranjang');

        return redirect()->route('customer.keranjang.index')->with('status', 'Keranjang dikosongkan.');
    }
}