<nav class="bg-white border-b">
    <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('customer.dashboard') }}" class="font-bold text-orange-600">Katering</a>
        <div class="flex items-center gap-5 text-sm text-gray-600">
            <a href="{{ route('customer.alamat.index') }}" class="hover:text-orange-600">Alamat</a>
            <a href="{{ route('customer.profile.edit') }}" class="hover:text-orange-600">Profil</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="hover:text-red-600">Logout</button>
            </form>
        </div>
    </div>
</nav>