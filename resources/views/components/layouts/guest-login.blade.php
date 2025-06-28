<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ayanna') }}</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('storage/logos/favicon.png') }}" type="image/png">
    <!-- Polices -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <!-- Scripts & Styles Laravel/Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm mx-auto">
        <div class="flex justify-center">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-[#3e2f24]" />
            </a>
        </div>
        <div class="mt-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
