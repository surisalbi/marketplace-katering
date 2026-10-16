<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlamatController extends Controller
{
    public function index()
    {
        $alamat = DB::table('alamat_customer')
            ->where('customer_id', $this->customerId())
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('customer.alamat.index', compact('alamat'));
    }

    public function create()
    {
        return view('customer.alamat.form', ['alamat' => null]);
    }

    public function store(Request $request)
    {
        $data       = $this->validated($request);
        $customerId = $this->customerId();

        DB::transaction(function () use ($request, $data, $customerId) {
            $isFirst   = ! DB::table('alamat_customer')->where('customer_id', $customerId)->exists();
            $isDefault = $isFirst || $request->boolean('is_default');   // alamat pertama otomatis utama

            if ($isDefault) {
                DB::table('alamat_customer')->where('customer_id', $customerId)->update(['is_default' => 0]);
            }

            DB::table('alamat_customer')->insert($data + [
                'customer_id' => $customerId,
                'is_default'  => $isDefault ? 1 : 0,
            ]);
        });

        return redirect()->route('customer.alamat.index')->with('status', 'Alamat berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return view('customer.alamat.form', ['alamat' => $this->findOwned($id)]);
    }

    public function update(Request $request, $id)
    {
        $alamat = $this->findOwned($id);
        $data   = $this->validated($request);

        DB::transaction(function () use ($request, $alamat, $data) {
            // alamat utama tidak boleh "dilepas" begitu saja; harus dipindah ke alamat lain
            $isDefault = $alamat->is_default || $request->boolean('is_default');

            if ($isDefault && ! $alamat->is_default) {
                DB::table('alamat_customer')->where('customer_id', $alamat->customer_id)->update(['is_default' => 0]);
            }

            DB::table('alamat_customer')->where('id', $alamat->id)->update($data + [
                'is_default' => $isDefault ? 1 : 0,
            ]);
        });

        return redirect()->route('customer.alamat.index')->with('status', 'Alamat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $alamat = $this->findOwned($id);

        DB::transaction(function () use ($alamat) {
            DB::table('alamat_customer')->where('id', $alamat->id)->delete();

            // jika yang dihapus alamat utama, jadikan alamat terbaru yang tersisa sebagai utama
            if ($alamat->is_default) {
                $next = DB::table('alamat_customer')
                    ->where('customer_id', $alamat->customer_id)
                    ->orderByDesc('id')
                    ->first();

                if ($next) {
                    DB::table('alamat_customer')->where('id', $next->id)->update(['is_default' => 1]);
                }
            }
        });

        return redirect()->route('customer.alamat.index')->with('status', 'Alamat berhasil dihapus.');
    }

    public function setDefault($id)
    {
        $alamat = $this->findOwned($id);

        DB::transaction(function () use ($alamat) {
            DB::table('alamat_customer')->where('customer_id', $alamat->customer_id)->update(['is_default' => 0]);
            DB::table('alamat_customer')->where('id', $alamat->id)->update(['is_default' => 1]);
        });

        return back()->with('status', 'Alamat utama diperbarui.');
    }

    /* ---------- helpers ---------- */

    private function customerId(): int
    {
        $id = DB::table('customer')->where('user_id', Auth::id())->value('id');
        abort_if(! $id, 403);

        return (int) $id;
    }

    private function findOwned($id): object
    {
        $alamat = DB::table('alamat_customer')
            ->where('id', $id)
            ->where('customer_id', $this->customerId())
            ->first();

        abort_if(! $alamat, 404);

        return $alamat;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label'         => 'required|string|max:50',
            'nama_penerima' => 'required|string|max:100',
            'telp'          => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'kota'          => 'required|string|max:100',
            'alamat'        => 'required|string|max:500',
        ]);
    }
}