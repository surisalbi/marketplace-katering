<header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-4 md:px-8 z-10 w-full shrink-0">
    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="md:hidden p-2 -ml-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="las la-bars"></i>
        </button>
        <h2 class="text-lg font-semibold text-gray-800 hidden sm:block"></h2>
    </div>

    <div class="flex items-center gap-2 md:gap-6">

        <div class="relative group cursor-pointer">
            <div class="flex items-center gap-2 border border-transparent md:border-gray-200 py-1.5 md:px-3 rounded-lg hover:bg-gray-50 transition">
                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=0c5fa8&color=fff" alt="User" class="w-7 h-7 rounded-full shadow-sm">
                <i class="las la-angle-down text-xs"></i>
            </div>

            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 transform origin-top-right group-hover:translate-y-0 translate-y-2">
                <div class="py-2">
                    <div class="px-4 py-2 border-b border-gray-50 lg:hidden block mb-1 bg-gray-50/50">
                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->role }}</p>
                    </div>
                    <a href="#" data-modal-open="modal-password" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        Ubah Password
                    </a>
                    <hr class="my-1 border-gray-100" />
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button class="flex items-center px-4 py-2 text-sm text-red-600 w-full hover:bg-red-50 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
