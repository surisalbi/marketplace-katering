<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title') - Marketplace</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
        <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
        <link rel="icon" href="{{ asset('assets/img/favicon.png') }}">
        <script src="{{ asset('assets/js/main.js') }}"></script>
        @stack('style')
    </head>
    <body class="bg-background text-gray-800 font-sans h-screen flex overflow-hidden relative">
        @include('layouts.sidebar')

        <main class="flex-1 flex flex-col overflow-hidden w-full bg-background relative">
            @include('layouts.header')

            <div class="flex-1 overflow-y-auto space-y-6 p-6">
                @yield('content')
            </div>
        </main>

        @include('partials.modal-ubah-password')
        
        <script src="{{ asset('assets/js/script.js') }}"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @stack('script')
    </body>
</html>