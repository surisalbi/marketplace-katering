@extends('layouts.app')
@section('title', 'Manajemen User')
@section('content')
<div class="max-w-full mx-auto py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Status User</h1>
        <p class="text-sm text-gray-500">Aktifkan atau nonaktifkan akun pengguna.</p>
    </div>

    {{-- Alert --}}
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
    <form method="GET" action="{{ route('admin.users.index') }}"
          class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Cari nama / email..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select name="role"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Role</option>
            @foreach (['admin', 'merchant', 'customer'] as $r)
                <option value="{{ $r }}" {{ $role === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>

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
            <a href="{{ route('admin.users.index') }}"
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
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Role</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Terdaftar</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $index => $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $users->firstItem() + $index }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="ml-1 text-xs text-gray-400">(Anda)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @php
                                $roleColor = match ($user->role) {
                                    'admin'    => 'bg-purple-100 text-purple-700',
                                    'merchant' => 'bg-blue-100 text-blue-700',
                                    'customer' => 'bg-gray-100 text-gray-700',
                                    default    => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $roleColor }}">
                                {{ $user->role ? ucfirst($user->role) : '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($user->id === auth()->id())
                                <button type="button" disabled
                                        class="cursor-not-allowed rounded-lg bg-gray-200 px-3 py-1.5 text-xs font-medium text-gray-400">
                                    Tidak tersedia
                                </button>
                            @else
                                <form method="POST"
                                      action="{{ route('admin.users.toggle', $user->id) }}"
                                      onsubmit="return confirm('Yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} user {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('PATCH')
                                    @if ($user->is_active)
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
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data user.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection