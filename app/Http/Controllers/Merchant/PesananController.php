<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    // status asal => status tujuan yang diizinkan
    private const NEXT = [
        'pending'    => ['confirmed', 'rejected'],
        'confirmed'  => ['preparing'],
        'preparing'  => ['delivering'],
        'delivering' => ['completed'],
    ];

    public function index(Request $request)
    {
        $mid = $this->merchantId();
        $tab = $request->query('tab', 'baru');

        $statuses = match ($tab) {
            'baru'     => ['pending'],
            'diproses' => ['confirmed', 'preparing', 'delivering'],
            'selesai'  => ['completed'],
            'batal'    => ['cancelled', 'rejected'],
            default    => null,
        };

        $pesanan = DB::table('pesanan')
            ->join('customer', 'customer.id', '=', 'pesanan.customer_id')
            ->leftJoin('invoice', 'invoice.pesanan_id', '=', 'pesanan.id')
            ->where('pesanan.merchant_id', $mid)
            ->when($statuses, fn ($q) => $q->whereIn('pesanan.status', $statuses))
            ->select('pesanan.*', 'customer.nama_kantor', 'invoice.status as invoice_status')
            ->selectSub(
                fn ($q) => $q->from('pembayaran')->selectRaw('count(*)')
                    ->whereColumn('pembayaran.invoice_id', 'invoice.id')
                    ->where('pembayaran.status', 'pending'),
                'bayar_pending'
            )
            ->orderByDesc('pesanan.id')
            ->paginate(10)
            ->withQueryString();

        $counts = DB::table('pesanan')->where('merchant_id', $mid)
            ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('merchant.pesanan.index', compact('pesanan', 'tab', 'counts'));
    }

    public function show($id)
    {
        $pesanan = DB::table('pesanan')
            ->join('customer', 'customer.id', '=', 'pesanan.customer_id')
            ->where('pesanan.id', $id)
            ->where('pesanan.merchant_id', $this->merchantId())
            ->select('pesanan.*', 'customer.nama_kantor', 'customer.nama_pic', 'customer.telp as customer_telp')
            ->first();

        abort_if(! $pesanan, 404);

        $items      = DB::table('item_pesanan')->where('pesanan_id', $pesanan->id)->orderBy('id')->get();
        $invoice    = DB::table('invoice')->where('pesanan_id', $pesanan->id)->first();
        $pembayaran = $invoice
            ? DB::table('pembayaran')->where('invoice_id', $invoice->id)->orderByDesc('id')->get()
            : collect();

        return view('merchant.pesanan.show', compact('pesanan', 'items', 'invoice', 'pembayaran'));
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status'       => 'required|in:confirmed,rejected,preparing,delivering,completed',
            'alasan_batal' => 'required_if:status,rejected|nullable|string|max:255',
        ], ['alasan_batal.required_if' => 'Isi alasan penolakan.']);

        $mid     = $this->merchantId();
        $pesanan = DB::table('pesanan')->where('id', $id)->where('merchant_id', $mid)->first();
        abort_if(! $pesanan, 404);

        if (! in_array($data['status'], self::NEXT[$pesanan->status] ?? [])) {
            return back()->withErrors(['status' => 'Perubahan status tidak diizinkan.']);
        }

        $affected = DB::transaction(function () use ($data, $pesanan, $mid) {
            // atomik: hanya berhasil jika status belum berubah oleh pihak lain
            $n = DB::table('pesanan')
                ->where('id', $pesanan->id)
                ->where('merchant_id', $mid)
                ->where('status', $pesanan->status)
                ->update(array_filter([
                    'status'       => $data['status'],
                    'alasan_batal' => $data['status'] === 'rejected' ? $data['alasan_batal'] : null,
                ], fn ($v) => $v !== null));

            if ($n && $data['status'] === 'rejected') {
                DB::table('invoice')->where('pesanan_id', $pesanan->id)->where('status', 'unpaid')->update(['status' => 'cancelled']);
            }

            return $n;
        });

        if (! $affected) {
            return back()->withErrors(['status' => 'Status pesanan sudah berubah. Muat ulang halaman.']);
        }

        return back()->with('status', 'Status pesanan diperbarui.');
    }

    private function merchantId(): int
    {
        $id = DB::table('merchant')->where('user_id', Auth::id())->value('id');
        abort_if(! $id, 403);

        return (int) $id;
    }
}