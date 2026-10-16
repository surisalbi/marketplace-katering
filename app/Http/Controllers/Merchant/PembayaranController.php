<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function verify($id)
    {
        $mid = $this->merchantId();

        $error = DB::transaction(function () use ($id, $mid) {
            $p = $this->findOwned($id, $mid, lock: true);

            if ($p->status !== 'pending' || ! in_array($p->invoice_status, ['unpaid', 'overdue'])) {
                return 'Pembayaran ini sudah diproses.';
            }

            $paidAt = $p->paid_at ?? now();

            DB::table('pembayaran')->where('id', $p->id)->update([
                'status'      => 'verified',
                'paid_at'     => $paidAt,
                'verified_at' => now(),
                'verified_by' => Auth::id(),
            ]);

            DB::table('invoice')->where('id', $p->invoice_id)->update([
                'status'  => 'paid',
                'paid_at' => $paidAt,
            ]);

            // pembayaran pending lain untuk invoice yang sama (jika ada) digugurkan
            DB::table('pembayaran')
                ->where('invoice_id', $p->invoice_id)
                ->where('id', '!=', $p->id)
                ->where('status', 'pending')
                ->update(['status' => 'failed', 'alasan_tolak' => 'Invoice sudah dilunasi.']);

            return null;
        });

        return $error
            ? back()->withErrors(['pembayaran' => $error])
            : back()->with('status', 'Pembayaran diverifikasi. Invoice berstatus lunas.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['alasan_tolak' => 'required|string|max:255']);

        $mid = $this->merchantId();

        $error = DB::transaction(function () use ($request, $id, $mid) {
            $p = $this->findOwned($id, $mid, lock: true);

            if ($p->status !== 'pending') {
                return 'Pembayaran ini sudah diproses.';
            }

            DB::table('pembayaran')->where('id', $p->id)->update([
                'status'       => 'failed',
                'alasan_tolak' => $request->alasan_tolak,
                'verified_at'  => now(),
                'verified_by'  => Auth::id(),
            ]);

            return null;
        });

        return $error
            ? back()->withErrors(['pembayaran' => $error])
            : back()->with('status', 'Pembayaran ditolak. Customer dapat mengirim ulang.');
    }

    public function bukti($id)
    {
        $p = $this->findOwned($id, $this->merchantId());

        abort_if(! $p->bukti || ! Storage::disk('local')->exists($p->bukti), 404);

        return response()->file(Storage::disk('local')->path($p->bukti));
    }

    /* ---------- helpers ---------- */

    private function merchantId(): int
    {
        $id = DB::table('merchant')->where('user_id', Auth::id())->value('id');
        abort_if(! $id, 403);

        return (int) $id;
    }

    // Pembayaran hanya bisa diakses merchant pemilik invoice
    private function findOwned($id, int $mid, bool $lock = false): object
    {
        $q = DB::table('pembayaran')
            ->join('invoice', 'invoice.id', '=', 'pembayaran.invoice_id')
            ->where('pembayaran.id', $id)
            ->where('invoice.merchant_id', $mid)
            ->select('pembayaran.*', 'invoice.status as invoice_status');

        $p = $lock ? $q->lockForUpdate()->first() : $q->first();
        abort_if(! $p, 404);

        return $p;
    }
}