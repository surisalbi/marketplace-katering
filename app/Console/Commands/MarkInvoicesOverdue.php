<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkInvoicesOverdue extends Command
{
    protected $signature = 'invoice:mark-overdue';
    protected $description = 'Tandai invoice unpaid yang melewati jatuh tempo sebagai overdue';

    public function handle(): int
    {
        $ids = DB::table('invoice')
            ->where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            // jangan ditandai overdue jika ada bukti bayar yang masih menunggu verifikasi merchant
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('pembayaran')
                ->whereColumn('pembayaran.invoice_id', 'invoice.id')
                ->where('pembayaran.status', 'pending'))
            ->pluck('id');

        if ($ids->isEmpty()) {
            $this->info('Tidak ada invoice jatuh tempo.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($ids) {
            DB::table('invoice')->whereIn('id', $ids)->update(['status' => 'overdue']);

            // opsional: batalkan pesanan yang belum mulai disiapkan
            if (config('pesanan.batal_otomatis_overdue')) {
                $pesananIds = DB::table('invoice')->whereIn('id', $ids)->pluck('pesanan_id');

                DB::table('pesanan')
                    ->whereIn('id', $pesananIds)
                    ->where('status', 'confirmed')
                    ->update(['status' => 'cancelled', 'alasan_batal' => 'Pembayaran melewati jatuh tempo.']);
            }
        });

        $this->info($ids->count() . ' invoice ditandai overdue.');

        return self::SUCCESS;
    }
}