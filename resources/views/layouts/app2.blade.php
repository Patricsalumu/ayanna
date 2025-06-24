<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device.width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ayanna') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('storage/logos/favicon.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    <!-- Scripts Laravel/Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CDN (optionnel si déjà compilé via Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Interact.js (optionnel) -->
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
</head>
<body class="font-sans antialiased text-[#3e2f24] bg-[#f9f6f3]">

    <div class="min-h-screen flex flex-col">
        <!-- Navigation uniquement si demandé -->
        @isset($withNavigation)
            @include('layouts.navigation2')
        @endisset

        <!-- En-tête de page éventuel -->
        @isset($header)
            <header class="bg-[#d8c1a8] shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-lg font-bold text-[#3e2f24]">
                        {{ $header }}
                    </h1>
                </div>
            </header>
        @endisset

        <!-- Contenu principal -->
        <main>
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>
    </div>
    
</div>

<!-- Scripts Lucide (à placer avant </body>)-->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>lucide.createIcons();</script>
<!-- Alpine.js pour les modales et l'interactivité -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
