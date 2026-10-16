<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KeranjangService
{
    public function get(): array
    {
        return session('keranjang', ['merchant_id' => null, 'items' => []]);
    }

    /**
     * Baca keranjang + validasi ulang ke database (harga, ketersediaan, merchant aktif).
     * Item yang sudah tidak valid otomatis dibuang dari session dan dihitung di 'dropped'.
     */
    public function resolve(): array
    {
        $cart     = $this->get();
        $items    = collect();
        $subtotal = 0.0;
        $dropped  = 0;
        $merchant = null;

        if (! empty($cart['items'])) {
            $menus = DB::table('menu')
                ->join('merchant', 'merchant.id', '=', 'menu.merchant_id')
                ->whereIn('menu.id', array_keys($cart['items']))
                ->where('menu.merchant_id', $cart['merchant_id'])
                ->whereNull('menu.deleted_at')
                ->where('menu.is_available', 1)
                ->where('merchant.is_active', 1)
                ->select('menu.id', 'menu.nama', 'menu.harga', 'menu.foto')
                ->get()
                ->keyBy('id');

            $valid = [];
            foreach ($cart['items'] as $id => $row) {
                $m = $menus->get($id);
                if (! $m) {
                    $dropped++;
                    continue;
                }

                $qty   = (int) $row['qty'];
                $total = round((float) $m->harga * $qty, 2);

                $items->push((object) [
                    'menu_id' => $m->id,
                    'nama'    => $m->nama,
                    'foto'    => $m->foto,
                    'harga'   => (float) $m->harga,
                    'qty'     => $qty,
                    'catatan' => $row['catatan'] ?? null,
                    'total'   => $total,
                ]);

                $subtotal += $total;
                $valid[$id] = $row;
            }

            if ($dropped) {
                $valid
                    ? session(['keranjang' => ['merchant_id' => $cart['merchant_id'], 'items' => $valid]])
                    : session()->forget('keranjang');
            }

            if ($items->isNotEmpty()) {
                $merchant = DB::table('merchant')->where('id', $cart['merchant_id'])->first();
            }
        }

        $ongkir = (float) config('pesanan.biaya_pengiriman');
        $pajak  = round($subtotal * (float) config('pesanan.pajak_persen') / 100, 2);

        return [
            'merchant' => $merchant,
            'items'    => $items,
            'subtotal' => round($subtotal, 2),
            'ongkir'   => $ongkir,
            'pajak'    => $pajak,
            'grand'    => round($subtotal + $ongkir + $pajak, 2),
            'dropped'  => $dropped,
        ];
    }
}