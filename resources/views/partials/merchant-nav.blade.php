@php $link = fn ($on) => $on ? 'text-orange-600 font-semibold' : 'hover:text-orange-600'; @endphp
<nav class="bg-white border-b sticky top-0 z-30">
    <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('merchant.dashboard') }}" class="font-bold text-orange-600">Merchant</a>
        <div class="flex items-center gap-5 text-sm text-gray-600">
            <a href="{{ route('merchant.pesanan.index') }}" class="relative {{ $link(request()->routeIs('merchant.pesanan.*')) }}">
                Pesanan
                @if (($baru + $bayar) > 0)
                    <span class="absolute -top-2 -right-4 bg-orange-500 text-white text-[10px] font-semibold rounded-full min-w-4 h-4 px-1 flex items-center justify-center">{{ $baru + $bayar }}</span>
                @endif
            </a>
            <a href="{{ route('merchant.menu.index') }}" class="{{ $link(request()->routeIs('merchant.menu.*')) }}">Menu</a>
            <a href="{{ route('merchant.profile.edit') }}" class="{{ $link(request()->routeIs('merchant.profile.*')) }}">Profil</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="hover:text-red-600">Logout</button>
            </form>
        </div>
    </div>
</nav>