<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center py-10 bg-[#F8FAFC]">
        <div class="flex flex-col items-center text-center">
            <a href="/">
                <img src="{{ asset('images/logo-simewo.png') }}" alt="Logo SIMEWO" class="w-16 h-16 object-contain">
            </a>
            <p class="mt-3 text-sm text-[#64748B] max-w-xs">Sistem Monitoring dan Evaluasi Website OPD</p>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-xl shadow-slate-900/10 overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>

        <p class="mt-8 text-xs text-[#94A3B8]">Diskominfo Kabupaten Tapin</p>
    </div>
</body>

</html>