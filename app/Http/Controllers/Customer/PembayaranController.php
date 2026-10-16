<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PembayaranController extends Controller
{
    public function create($invoice)
    {
        $inv = $this->loadInvoice($invoice);

        if ($msg = $this->blocker($inv)) {
            return redirect()->route('customer.invoice.show', $inv->id)->withErrors(['pembayaran' => $msg]);
        }

        $merchant = DB::table('merchant')->where('id', $inv->merchant_id)->first();

        return view('customer.pembayaran.create', compact('inv', 'merchant'));
    }

    public function store(Request $request, $invoice)
    {
        $data = $request->validate([
            'metode'       => 'required|in:transfer,cash',
            'reference_no' => 'nullable|string|max:100',
            'paid_at'      => 'required_if:metode,transfer|nullable|date|before_or_equal:now',
            'bukti'        => 'required_if:metode,transfer|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'paid_at.required_if' => 'Isi waktu transfer.',
            'bukti.required_if'   => 'Unggah bukti transfer.',
        ]);

        $inv      = $this->loadInvoice($invoice);
        $merchant = DB::table('merchant')->where('id', $inv->merchant_id)->first();

        if ($data['metode'] === 'transfer' && ! $merchant->bank_no_rekening) {
            return back()->withInput()->withErrors(['metode' => 'Merchant ini belum menyediakan rekening. Silakan pilih bayar tunai.']);
        }

        $error = DB::transaction(function () use ($request, $data, $inv) {
            // kunci baris invoice agar tidak terjadi dua pembayaran pending bersamaan
            $locked = DB::table('invoice')->where('id', $inv->id)->lockForUpdate()->first();

            if ($msg = $this->blocker($locked)) {
                return $msg;
            }

            $isTransfer = $data['metode'] === 'transfer';
            $isCash     = $data['metode'] === 'cash';
            $bukti      = null;

            if ($isTransfer) {
                $file  = $request->file('bukti');
                $nama  = Str::uuid() . '.' . $file->extension();   // extension dari MIME asli, bukan nama buatan user
                $file->move(public_path('uploads/bukti'), $nama);
                $bukti = 'uploads/bukti/' . $nama;
                $status = 'pending';
            }

            if ($isCash) {
                $status = 'verified';
                // update status invoice menjadi paid karena pembayaran tunai langsung diverifikasi
                DB::table('invoice')->where('id', $locked->id)->update(['status' => 'paid']);
            }

            DB::table('pembayaran')->insert([
                'invoice_id'   => $locked->id,
                'nominal'      => $locked->grand_total,
                'metode'       => $data['metode'],
                'reference_no' => $isTransfer ? ($data['reference_no'] ?? null) : null,
                'bukti'        => $bukti,
                'status'       => $status,
                'paid_at'      => $isTransfer ? Carbon::parse($data['paid_at'])->toDateTimeString() : null,
            ]);

            return null;
        });

        if ($error) {
            return redirect()->route('customer.invoice.show', $inv->id)->withErrors(['pembayaran' => $error]);
        }

        return redirect()->route('customer.invoice.show', $inv->id)
            ->with('status', 'Pembayaran dikirim dan menunggu verifikasi dari merchant.');
    }

    /* ---------- helpers ---------- */

    private function customerId(): int
    {
        $id = DB::table('customer')->where('user_id', Auth::id())->value('id');
        abort_if(! $id, 403);

        return (int) $id;
    }

    private function loadInvoice($id): object
    {
        $inv = DB::table('invoice')->where('id', $id)->where('customer_id', $this->customerId())->first();
        abort_if(! $inv, 404);

        return $inv;
    }

    // Alasan invoice belum boleh dibayar (null = boleh)
    private function blocker(object $inv): ?string
    {
        $lewatTempo = $inv->status === 'overdue'
            || ($inv->status === 'unpaid' && Carbon::parse($inv->due_date)->endOfDay()->isPast());

        if ($lewatTempo) {
            return 'Invoice sudah melewati jatuh tempo dan tidak dapat dibayar. Silakan hubungi merchant.';
        }

        if ($inv->status !== 'unpaid') {
            return 'Invoice ini tidak dapat dibayar.';
        }

        $statusPesanan = DB::table('pesanan')->where('id', $inv->pesanan_id)->value('status');
        if (! in_array($statusPesanan, ['pending', 'confirmed', 'preparing', 'delivering', 'completed'])) {
            return 'Pembayaran dapat dilakukan setelah pesanan dikonfirmasi merchant.';
        }

        if (DB::table('pembayaran')->where('invoice_id', $inv->id)->where('status', 'pending')->exists()) {
            return 'Pembayaran Anda sedang menunggu verifikasi.';
        }

        return null;
    }
}