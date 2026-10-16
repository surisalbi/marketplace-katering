<a href="{{ route('customer.katering.show', $m->id) }}"
   class="group bg-white rounded-2xl shadow-sm hover:shadow-lg transition overflow-hidden flex flex-col">
    <div class="relative aspect-[4/3] bg-orange-50 overflow-hidden">
        @if ($m->foto)
            <img src="{{ asset($m->foto) }}" alt="{{ $m->nama }}" loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-5xl">🍱</div>
        @endif
        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-medium px-2.5 py-1 rounded-full">
            {{ $m->kategori_nama }}
        </span>
    </div>
    <div class="p-4 flex flex-col flex-1">
        <h3 class="font-semibold line-clamp-1">{{ $m->nama }}</h3>
        <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $m->nama_usaha }} &middot; 📍 {{ $m->kab_kota ?: '-' }}</p>
        @if ($m->deskripsi)
            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $m->deskripsi }}</p>
        @endif
        <div class="mt-auto pt-3">
            <span class="text-lg font-bold text-orange-600">Rp {{ number_format($m->harga, 0, ',', '.') }}</span>
        </div>
    </div>
</a>