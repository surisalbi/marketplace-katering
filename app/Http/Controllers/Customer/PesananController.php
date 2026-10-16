<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\KeranjangService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'semua');

        $statuses = match ($tab) {
            'berjalan' => ['pending', 'confirmed', 'preparing', 'delivering'],
            'selesai'  => ['completed'],
            'batal'    => ['cancelled', 'rejected'],
            default    => null,
        };

        $pesanan = DB::table('pesanan')
            ->join('merchant', 'merchant.id', '=', 'pesanan.merchant_id')
            ->where('pesanan.customer_id', $this->customerId())
            ->when($statuses, fn ($q) => $q->whereIn('pesanan.status', $statuses))
            ->select('pesanan.*', 'merchant.nama_usaha')
            ->selectSub(
                fn ($q) => $q->from('item_pesanan')->selectRaw('count(*)')->whereColumn('item_pesanan.pesanan_id', 'pesanan.id'),
                'jumlah_item'
            )
            ->orderByDesc('pesanan.id')
            ->paginate(10)
            ->withQueryString();

        return view('customer.pesanan.index', compact('pesanan', 'tab'));
    }

    public function store(Request $request, KeranjangService $keranjang)
    {
        $customerId = $this->customerId();
        $customer   = DB::table('customer')->where('id', $customerId)->first();
        $minHari    = (int) config('pesanan.min_hari_pesan');
        $minTanggal = now()->addDays($minHari)->toDateString();

        $data = $request->validate([
            'alamat_id'      => 'required|integer',
            'tgl_pengiriman' => "required|date|after_or_equal:$minTanggal",
            'jam_pengiriman' => 'nullable|date_format:H:i',
            'catatan'        => 'nullable|string|max:500',
        ], [
            'alamat_id.required'            => 'Pilih alamat pengiriman.',
            'tgl_pengiriman.after_or_equal' => 'Tanggal pengiriman minimal ' . now()->addDays($minHari)->format('d-m-Y') . '.',
        ]);

        $alamat = DB::table('alamat_customer')
            ->where('id', $data['alamat_id'])
            ->where('customer_id', $customerId)
            ->first();

        if (! $alamat) {
            return back()->withInput()->withErrors(['alamat_id' => 'Alamat tidak valid.']);
        }

        $cart = $keranjang->resolve();

        if ($cart['items']->isEmpty()) {
            return redirect()->route('customer.keranjang.index')->withErrors(['keranjang' => 'Keranjang Anda kosong.']);
        }

        if ($cart['dropped'] > 0) {
            return redirect()->route('customer.keranjang.index')
                ->withErrors(['keranjang' => 'Sebagian menu sudah tidak tersedia. Periksa kembali pesanan Anda.']);
        }

        $merchant = $cart['merchant'];

        [$pesananId, $invoiceId] = DB::transaction(function () use ($data, $alamat, $cart, $customer, $merchant) {
            // 1. Pesanan
            $id = DB::table('pesanan')->insertGetId([
                'no_pesanan'       => $this->generateNomor('pesanan', 'no_pesanan', 'ORD'),
                'customer_id'      => $customer->id,
                'merchant_id'      => $merchant->id,
                'status'           => 'pending',
                'tgl_pengiriman'   => $data['tgl_pengiriman'],
                'jam_pengiriman'   => $data['jam_pengiriman'] ?? null,
                'nama_penerima'    => $alamat->nama_penerima,
                'telp_penerima'    => $alamat->telp,
                'kota_penerima'    => $alamat->kota,
                'alamat_penerima'  => $alamat->alamat,
                'catatan'          => $data['catatan'] ?? null,
                'subtotal'         => $cart['subtotal'],
                'biaya_pengiriman' => $cart['ongkir'],
                'nominal_pajak'    => $cart['pajak'],
                'grand_total'      => $cart['grand'],
            ]);

            // 2. Item pesanan
            DB::table('item_pesanan')->insert(
                $cart['items']->map(fn ($i) => [
                    'pesanan_id' => $id,
                    'menu_id'    => $i->menu_id,
                    'nama_menu'  => $i->nama,
                    'harga_menu' => $i->harga,
                    'qty'        => $i->qty,
                    'total'      => $i->total,
                    'catatan'    => $i->catatan,
                ])->all()
            );

            // 3. Invoice (snapshot data merchant & customer saat ini)
            $issue = now();
            $due   = $issue->copy()->addDays((int) config('pesanan.invoice_jatuh_tempo_hari'));
            $kirim = Carbon::parse($data['tgl_pengiriman']);
            if ($due->gt($kirim)) {
                $due = $kirim;   // jatuh tempo tidak boleh melewati tanggal pengiriman
            }
        
            $invoiceId = DB::table('invoice')->insertGetId([
                'no_invoice'       => $this->generateNomor('invoice', 'no_invoice', 'INV'),
                'pesanan_id'       => $id,
                'merchant_id'      => $merchant->id,
                'customer_id'      => $customer->id,
                'nama_merchant'    => $merchant->nama_usaha ?: '-',
                'alamat_merchant'  => collect([$merchant->alamat, $merchant->kab_kota, $merchant->provinsi])->filter()->implode(', ') ?: '-',
                'nama_customer'    => $customer->nama_kantor,
                'alamat_customer'  => Str::limit($alamat->alamat . ', ' . $alamat->kota, 500, ''),
                'issue_date'       => $issue->toDateString(),
                'due_date'         => $due->toDateString(),
                'subtotal'         => $cart['subtotal'],
                'biaya_pengiriman' => $cart['ongkir'],
                'nominal_pajak'    => $cart['pajak'],
                'grand_total'      => $cart['grand'],
                'status'           => 'unpaid',
            ]);

            return [$id, $invoiceId];
        });

        session()->forget('keranjang');

        return redirect()->route('customer.invoice.show', $invoiceId)
            ->with('status', 'Pesanan berhasil dibuat. Menunggu konfirmasi dari merchant.');
    }

    public function show($id)
    {
        $pesanan = DB::table('pesanan')
            ->join('merchant', 'merchant.id', '=', 'pesanan.merchant_id')
            ->where('pesanan.id', $id)
            ->where('pesanan.customer_id', $this->customerId())
            ->select('pesanan.*', 'merchant.nama_usaha', 'merchant.kab_kota as merchant_kota')
            ->first();

        abort_if(! $pesanan, 404);

        $items   = DB::table('item_pesanan')->where('pesanan_id', $pesanan->id)->orderBy('id')->get();
        $invoice = DB::table('invoice')->where('pesanan_id', $pesanan->id)->first();

        return view('customer.pesanan.show', compact('pesanan', 'items', 'invoice'));
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['alasan_batal' => 'required|string|max:255']);

        $customerId = $this->customerId();

        $affected = DB::transaction(function () use ($request, $id, $customerId) {
            // atomik: hanya berhasil jika masih pending & milik customer ini
            $n = DB::table('pesanan')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled', 'alasan_batal' => $request->alasan_batal]);

            if ($n) {
                DB::table('invoice')->where('pesanan_id', $id)->where('status', 'unpaid')->update(['status' => 'cancelled']);
            }

            return $n;
        });

        if (! $affected) {
            return back()->withErrors(['alasan_batal' => 'Pesanan sudah diproses katering dan tidak bisa dibatalkan.']);
        }

        return back()->with('status', 'Pesanan berhasil dibatalkan.');
    }

    /* ---------- helpers ---------- */

    private function customerId(): int
    {
        $id = DB::table('customer')->where('user_id', Auth::id())->value('id');
        abort_if(! $id, 403);

        return (int) $id;
    }

    private function generateNomor(string $table, string $column, string $prefix): string
    {
        $prefix = $prefix . '-' . now()->format('Ymd') . '-';

        $last = DB::table($table)
            ->where($column, 'like', $prefix . '%')
            ->orderByDesc($column)
            ->lockForUpdate()
            ->value($column);

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}