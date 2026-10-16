<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function show($invoice)
    {
        $customerId = DB::table('customer')->where('user_id', Auth::id())->value('id');

        $invoice = DB::table('invoice')
            ->where('id', $invoice)
            ->where('customer_id', $customerId)
            ->first();

        abort_if(! $invoice, 404);

        $pesanan = DB::table('pesanan')->where('id', $invoice->pesanan_id)->first();
        $items   = DB::table('item_pesanan')->where('pesanan_id', $invoice->pesanan_id)->orderBy('id')->get();

        $pembayaran = DB::table('pembayaran')->where('invoice_id', $invoice->id)->orderByDesc('id')->get();

        $lewatTempo = $invoice->status === 'overdue'
            || ($invoice->status === 'unpaid' && Carbon::parse($invoice->due_date)->endOfDay()->isPast());

        $bolehBayar = $invoice->status === 'unpaid'
            && ! $lewatTempo
            && in_array($pesanan->status, ['pending', 'confirmed', 'preparing', 'delivering', 'completed'])
            && ! $pembayaran->contains('status', 'pending');

        return view('customer.invoice.show', compact('invoice', 'pesanan', 'items', 'pembayaran', 'bolehBayar', 'lewatTempo'));
    }
}