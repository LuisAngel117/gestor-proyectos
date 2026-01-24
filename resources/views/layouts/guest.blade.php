<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gestor de Proyectos') }}</title>

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
          x-init="document.documentElement.dataset.theme = theme; $watch('theme', value => { document.documentElement.dataset.theme = value; localStorage.setItem('theme', value); })">
        <div class="min-h-screen flex items-center justify-center px-4 py-10" style="background-color: var(--app-bg);">
            <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-6">
                <div class="hidden lg:flex flex-col justify-between rounded-xl p-8 text-white shadow-lg bg-gradient-to-br from-[#1de9b6] to-[#1dc4e9]">
                    <div class="flex items-center gap-3">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span class="text-2xl font-semibold">{{ config('app.name', 'Gestor') }}</span>
                    </div>
                    <div class="space-y-4">
                        <h1 class="text-3xl font-semibold leading-tight">Gestiona proyectos y equipos sin complicaciones.</h1>
                        <p class="text-base text-white/80">Accede al panel local y mantén el control de tareas, sprints y métricas.</p>
                        <ul class="space-y-2 text-sm text-white/85">
                            <li class="flex items-center gap-2">
                                <span class="inline-flex h-2 w-2 rounded-full bg-white/80"></span>
                                Flujo guiado por equipos y proyectos
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-flex h-2 w-2 rounded-full bg-white/80"></span>
                                Tablero, calendario y reportes listos
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-flex h-2 w-2 rounded-full bg-white/80"></span>
                                Todo en local, sin dependencias extra
                            </li>
                        </ul>
                    </div>
                    <p class="text-xs text-white/70">Propietario · Jose Revelo · {{ now()->format('Y') }}</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center gap-3 mb-6 lg:hidden">
                            <svg class="w-8 h-8 text-app-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">{{ config('app.name', 'Gestor') }}</span>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
