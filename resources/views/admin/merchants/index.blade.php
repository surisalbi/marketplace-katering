@extends('layouts.app')
@section('title', 'Data Merchant')
@section('content')
<div class="max-w-full mx-auto py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Data Merchant</h1>
        <p class="text-sm text-gray-500">Lihat detail dan kelola status merchant.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.merchants.index') }}"
          class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Cari usaha, pemilik, email, telepon..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select name="status"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="1" {{ (string) $status === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ (string) $status === '0' ? 'selected' : '' }}>Nonaktif</option>
        </select>

        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Filter
            </button>
            <a href="{{ route('admin.merchants.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Usaha</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Pemilik</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Kontak</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Lokasi</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($merchants as $index => $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $merchants->firstItem() + $index }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($m->foto)
                                    <img src="{{ asset($m->foto) }}" alt="{{ $m->nama_usaha }}"
                                         class="h-10 w-10 rounded-lg object-cover border border-gray-200">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-400">
                                        {{ strtoupper(substr($m->nama_usaha ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                <span class="font-medium text-gray-800">{{ $m->nama_usaha ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $m->pemilik }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            <div>{{ $m->email ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $m->no_telepon ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $m->kab_kota ?? '-' }}
                            @if ($m->provinsi)
                                <div class="text-xs text-gray-400">{{ $m->provinsi }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($m->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.merchants.show', $m->id) }}"
                                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    Detail
                                </a>

                                <form method="POST" action="{{ route('admin.merchants.toggle', $m->id) }}"
                                      onsubmit="return confirm('Yakin ingin {{ $m->is_active ? 'menonaktifkan' : 'mengaktifkan' }} merchant {{ addslashes($m->nama_usaha ?? '') }}?')">
                                    @csrf
                                    @method('PATCH')
                                    @if ($m->is_active)
                                        <button type="submit"
                                                class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">
                                            Aktifkan
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data merchant.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $merchants->links() }}
    </div>
</div>
@endsection