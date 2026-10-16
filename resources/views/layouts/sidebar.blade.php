<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/50 z-40 hidden md:hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>
<aside id="sidebar" class="w-64 bg-white border-r border-gray-100 flex flex-col justify-between absolute md:relative z-50 h-full transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
    <div>
        <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100">
            <div class="flex items-center gap-2">
                Marketplace Katering
            </div>
            <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-gray-600 p-1">
                <i class="las la-times text-xl"></i>
            </button>
        </div>

        <nav class="p-4 space-y-6 overflow-y-auto max-h-[calc(100vh-140px)]">

            {{-- ================= UTAMA ================= --}}
            <div>
                <ul class="space-y-1">
                    
                    @if(auth()->user()->role === 'merchant')
                        @include('layouts.navbar.merchant')
                    @elseif(auth()->user()->role === 'admin')
                        @include('layouts.navbar.admin')
                    @elseif(auth()->user()->role === 'customer')
                        @include('layouts.navbar.customer')
                    @endif

                </ul>
            </div>
        </nav>
    </div>

    <div class="p-4 border-t border-gray-100 bg-white">
        <div class="flex items-center gap-3 bg-gray-50 p-2 rounded-lg">
            <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=0c5fa8&color=fff" alt="User" class="w-7 h-7 rounded-full shadow-sm">
            <div class="overflow-hidden">
                <p class="text-sm font-semibold text-gray-700 truncate"> {{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->role }}</p>
            </div>
        </div>
    </div>
</aside>