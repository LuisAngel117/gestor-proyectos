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
<body class="font-sans antialiased"
      x-data="{
          theme: localStorage.getItem('theme') || 'light',
          sidebarOpen: localStorage.getItem('sidebar') !== 'collapsed'
      }"
      x-init="
          document.documentElement.dataset.theme = theme;
          $watch('theme', value => { document.documentElement.dataset.theme = value; localStorage.setItem('theme', value); });
          $watch('sidebarOpen', value => { localStorage.setItem('sidebar', value ? 'expanded' : 'collapsed'); });
      "
      x-on:toggle-theme.window="theme = theme === 'light' ? 'dark' : 'light'"
      x-on:toggle-sidebar.window="sidebarOpen = !sidebarOpen">
    <div class="app-shell" :class="{ 'sidebar-collapsed': !sidebarOpen }">
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
    <script>
        document.addEventListener('input', (event) => {
            const target = event.target;
            if (!target || !target.matches('[data-hour-input]')) {
                return;
            }

            const original = target.value;
            let sanitized = original.replace(/[^0-9.]/g, '');
            const parts = sanitized.split('.');
            if (parts.length > 2) {
                sanitized = parts[0] + '.' + parts.slice(1).join('');
            }
            const finalParts = sanitized.split('.');
            if (finalParts.length === 2) {
                finalParts[1] = finalParts[1].slice(0, 2);
                sanitized = finalParts[0] + '.' + finalParts[1];
            }

            if (sanitized !== original) {
                target.value = sanitized;
                const message = target.parentElement?.querySelector('[data-hour-message]');
                if (message) {
                    message.classList.remove('hidden');
                }
            }
        });

        document.addEventListener('blur', (event) => {
            const target = event.target;
            if (!target || !target.matches('[data-hour-input]')) {
                return;
            }
            const message = target.parentElement?.querySelector('[data-hour-message]');
            if (message) {
                message.classList.add('hidden');
            }
        }, true);
    </script>
</body>
</html>
