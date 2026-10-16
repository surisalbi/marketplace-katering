<li>
    <a href="{{ route('merchant.dashboard') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('merchant.dashboard') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-home text-xl"></i>
        Dashboard
    </a>
</li>

<li>
    <a href="{{ route('merchant.menu.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('merchant.menu.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-utensils text-xl"></i>
        Menu
    </a>
</li>

<li>
    <a href="{{ route('merchant.pesanan.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('merchant.pesanan.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-shopping-cart text-xl"></i>
        Pesanan
        @if (($baru + $bayar) > 0)
            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">
                {{ $baru + $bayar }}
            </span>
        @endif
    </a>
</li>