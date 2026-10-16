@php
    $map = [
        'pending'    => ['Menunggu Konfirmasi', 'bg-yellow-100 text-yellow-700'],
        'confirmed'  => ['Dikonfirmasi',        'bg-blue-100 text-blue-700'],
        'preparing'  => ['Sedang Disiapkan',    'bg-indigo-100 text-indigo-700'],
        'delivering' => ['Dalam Pengiriman',    'bg-purple-100 text-purple-700'],
        'completed'  => ['Selesai',             'bg-green-100 text-green-700'],
        'cancelled'  => ['Dibatalkan',          'bg-gray-200 text-gray-600'],
        'rejected'   => ['Ditolak',             'bg-red-100 text-red-700'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-gray-100 text-gray-600'];
@endphp
<span class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap {{ $cls }}">{{ $label }}</span>