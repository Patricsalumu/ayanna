{{-- resources/views/components/app-layout.blade.php --}}
@props(['header' => null])

@extends('layouts.app')

@section('header')
    {{ $header ?? '' }}
@endsection

@section('content')
    {{ $slot }}
@endsection
