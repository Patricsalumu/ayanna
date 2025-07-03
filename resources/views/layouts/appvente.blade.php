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
          <link rel="stylesheet" href="/build/assets/app-Dz7X2YIF.css">
         <script type="module" src="/build/assets/app-DLcFWqMV.js"></script>

         <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Alpine.js CDN (fallback si Vite ne charge pas correctement) -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Interact.js -->
      <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    </head>
    <body class="font-sans antialiased" x-data="{ showNavMenu: false, showNotification: false, notificationMessage: '', notificationType: 'info', showConfirm: false, confirmMessage: '', confirmCallback: null }">
        @php
            // Contexte global pour la navigation
            $pointDeVenteId = request('point_de_vente_id') ?? session('point_de_vente_id');
            $pointDeVente = isset($pointDeVente) ? $pointDeVente : ($pointDeVenteId ? \App\Models\PointDeVente::find($pointDeVenteId) : null);
            $salle = isset($salle) ? $salle : (request()->route('salle') ? \App\Models\Salle::find(request()->route('salle')) : null);
        @endphp
        
        <!-- Système de notifications Alpine.js -->
        <div x-show="showNotification" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" 
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed top-4 right-4 z-50 max-w-sm w-full">
            <div :class="{
                'bg-green-500': notificationType === 'success',
                'bg-red-500': notificationType === 'error', 
                'bg-blue-500': notificationType === 'info',
                'bg-yellow-500': notificationType === 'warning'
            }" class="text-white px-6 py-4 rounded-lg shadow-lg">
                <div class="flex justify-between items-center">
                    <span x-text="notificationMessage"></span>
                    <button @click="showNotification = false" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Modale de confirmation Alpine.js -->
        <div x-show="showConfirm" 
             style="background:rgba(0,0,0,0.5)" 
             class="fixed inset-0 z-50 flex items-center justify-center" 
             x-transition>
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirmation</h3>
                    <p class="text-gray-600" x-text="confirmMessage"></p>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="showConfirm = false" 
                            class="px-4 py-2 text-gray-600 bg-gray-200 rounded hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button @click="if(confirmCallback) confirmCallback(); showConfirm = false" 
                            class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 transition">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
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
