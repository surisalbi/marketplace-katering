@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 p-6">

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">
                Halo, {{ explode(' ', auth()->user()->name)[0] }}
            </h1>
            <p class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>

</div>
@endsection