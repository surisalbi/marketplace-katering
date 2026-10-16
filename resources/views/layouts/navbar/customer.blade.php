@php
    $cartCount = collect(session('keranjang.items', []))->sum('qty');
@endphp

{{-- <li>
    <a href="{{ route('customer.dashboard') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('customer.dashboard') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-home text-xl"></i>
        Home
    </a>
</li> --}}

<li>
    <a href="{{ route('customer.katering.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('customer.katering.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-hamburger text-xl"></i>
        Katering
    </a>
</li>

<li>
    <a href="{{ route('customer.keranjang.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('customer.keranjang.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-shopping-cart text-xl"></i>
        Keranjang
        @if ($cartCount)
            <span class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-orange-600 rounded-full">
                {{ $cartCount }}
            </span>
        @endif
    </a>
</li>

<li>
    <a href="{{ route('customer.pesanan.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('customer.pesanan.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-file-alt text-xl"></i>
        Pesanan
    </a>
</li>

@php 
    $masterRoutes = [ 
        'customer.profile.*', 
        'customer.alamat.*',
    ]; 
    $masterActive = request()->routeIs(...$masterRoutes); 
@endphp
<li
    x-data="{ open: {{
        $masterActive ? 'true' : 'false'
    }} }"
>
    <button
        @click="open = !open"
        type="button"
        class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg font-medium text-sm
            {{
            $masterActive
                ? 'bg-orange-50 text-orange-600'
                : 'text-gray-600 hover:bg-gray-50'
        }}"
    >
        <span class="flex items-center gap-3">
            <i class="las la-user text-xl"></i>
            Akun Saya
        </span>
        <i
            class="las la-angle-down text-sm transition-transform duration-200"
            :class="open ? 'rotate-180' : ''"
        ></i>
    </button>
    <ul
        x-show="open"
        x-collapse
        class="mt-1 ml-9 space-y-1 border-l border-gray-100 pl-3"
    >
        <li>
            <a
                href="{{ route('customer.profile.edit') }}"
                class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('customer.profile.*') ? 'text-orange-600 font-medium' : 'text-gray-500 hover:text-gray-700' }}"
            >
                Profil
            </a>
        </li>
        <li>
            <a
                href="{{ route('customer.alamat.index') }}"
                class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('customer.alamat.*') ? 'text-orange-600 font-medium' : 'text-gray-500 hover:text-gray-700' }}"
            >
                Alamat
            </a>
        </li>
    </ul>
</li>