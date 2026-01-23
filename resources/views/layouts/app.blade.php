<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gestor de Proyectos') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('theme') || 'light';
    </script>
</head>
<body class="font-sans antialiased" x-data="{ theme: localStorage.getItem('theme') || 'light' }"
      x-init="document.documentElement.dataset.theme = theme; $watch('theme', value => { document.documentElement.dataset.theme = value; localStorage.setItem('theme', value); })"
      x-on:toggle-theme.window="theme = theme === 'light' ? 'dark' : 'light'">
    <div class="app-shell">
        <aside class="hidden lg:block app-sidebar">
            @hasSection('sidebar')
                @yield('sidebar')
            @else
                @include('components.sidebar')
            @endif
        </aside>

        <div class="app-content flex flex-col min-w-0">
            <!-- Navegacion -->
            @include('components.nav')

            <!-- Page Content -->
            <main class="app-main flex-1">
                <!-- Page Heading -->
                @hasSection('header')
                    <div class="app-page-header">
                        @yield('header')
                    </div>
                @elseif (isset($header))
                    <div class="app-page-header">
                        {{ $header }}
                    </div>
                @endif

                <!-- Alertas Flash -->
                @include('components.alert')

                @yield('content')
            </main>

            <!-- Footer -->
            @include('components.footer')
        </div>
    </div>
</body>
</html>
