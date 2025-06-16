{{-- resources/views/components/app-layout.blade.php --}}
@props(['header' => null])
<x-layouts.app>
    @if (isset($header))
        <x-slot name="header">
            {{ $header }}
        </x-slot>
    @endif
    {{ $slot }}
</x-layouts.app>
