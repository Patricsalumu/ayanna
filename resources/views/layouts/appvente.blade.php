<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ayanna') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('storage/logos/favicon.png') }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

         <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Interact.js -->
      <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    </head>
    <body class="font-sans antialiased" x-data="{ showNavMenu: false }">
        @php
            // Contexte global pour la navigation
            $pointDeVenteId = request('point_de_vente_id') ?? session('point_de_vente_id');
            $pointDeVente = isset($pointDeVente) ? $pointDeVente : ($pointDeVenteId ? \App\Models\PointDeVente::find($pointDeVenteId) : null);
            $salle = isset($salle) ? $salle : (request()->route('salle') ? \App\Models\Salle::find(request()->route('salle')) : null);
        @endphp
        <div class="min-h-screen bg-gray-100 ">
            @include('layouts.navvente')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="bg-gray-100 min-h-screen">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
    </body>
</html>
