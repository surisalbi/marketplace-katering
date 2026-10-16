@extends('layouts.app')
@section('title', 'Kelola Menu')
@section('content')
<nav class="bg-white border-b">
    <div class="max-w-full mx-auto p-4">
        <h1 class="text-lg font-semibold">Kelola Menu</h1>
    </div>
</nav>

<main class="max-w-full mx-auto space-y-4">
    @include('partials.alerts')

    <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama menu..."
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
            <select name="kategori" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Semua kategori</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
            <button class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        </form>
        <a href="{{ route('merchant.menu.create') }}"
           class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg text-center">+ Tambah Menu</a>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left">
                <tr>
                    <th class="px-4 py-3">Menu</th>
                    <th class="px-4 py-3 hidden sm:table-cell">Kategori</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($menus as $m)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($m->foto)
                                    <img src="{{ asset($m->foto) }}" class="w-12 h-12 rounded-lg object-cover" alt="">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-100"></div>
                                @endif
                                <span class="font-medium">{{ $m->nama }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-600">{{ $m->kategori_nama ?? '-' }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($m->harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('merchant.menu.toggle', $m->id) }}">
                                @csrf @method('PATCH')
                                @if ($m->is_available)
                                    <button class="text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">Tersedia</button>
                                @else
                                    <button class="text-xs font-medium bg-gray-200 text-gray-600 px-2.5 py-1 rounded-full">Habis</button>
                                @endif
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('merchant.menu.edit', $m->id) }}" class="text-orange-600 mr-3">Edit</a>
                            <form method="POST" action="{{ route('merchant.menu.destroy', $m->id) }}" class="inline"
                                  onsubmit="return confirm('Hapus menu ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada menu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $menus->links() }}
</main>
@endsection