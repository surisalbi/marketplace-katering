@php
    $map = [
        'unpaid'    => ['Belum Dibayar', 'bg-yellow-100 text-yellow-700'],
        'paid'      => ['Lunas',         'bg-green-100 text-green-700'],
        'overdue'   => ['Jatuh Tempo',   'bg-red-100 text-red-700'],
        'cancelled' => ['Dibatalkan',    'bg-gray-200 text-gray-600'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-gray-100 text-gray-600'];
@endphp
<span class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap {{ $cls }}">{{ $label }}</span>