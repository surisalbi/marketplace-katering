<li>
    <a href="{{ route('admin.dashboard') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('admin.dashboard') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-home text-xl"></i>
        Dashboard
    </a>
</li>
<li>
    <a href="{{ route('admin.merchants.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('admin.merchants.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-store text-xl"></i>
        Manajemen Merchant
    </a>
</li>
<li>
    <a href="{{ route('admin.users.index') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm
        {{ request()->routeIs('admin.users.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
        <i class="las la-users text-xl"></i>
        Manajemen User
    </a>
</li>
