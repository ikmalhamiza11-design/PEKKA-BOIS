<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#48550f">
    <title>@yield('judul', 'Masuk') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-5 px-4 py-5">
        @hasSection('appbar')
            @yield('appbar')
        @else
            <x-brand-bar />
        @endif

        <main class="flex flex-1 flex-col gap-5">
            @yield('isi')
        </main>

        <footer class="pb-2 text-center text-xs text-olive/70">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </footer>
    </div>
</body>
</html>
