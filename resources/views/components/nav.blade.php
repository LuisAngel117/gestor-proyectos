<nav x-data="{ open: false }" class="app-topbar">
    <!-- Primary Navigation Menu -->
    <div class="px-4 sm:px-6 lg:px-8 h-full">
        <div class="flex items-center h-full">
            <div class="flex items-center gap-3 lg:hidden">
                <a href="{{ route('dashboard') }}" class="flex items-center">
                    <svg class="w-8 h-8 text-app-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span class="ml-2 text-lg font-semibold text-gray-800">{{ config('app.name', 'Gestor') }}</span>
                </a>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-auto space-x-4">
                @php
                    $navUser = Auth::user();
                    $unreadCount = $navUser?->unreadNotifications()->count() ?? 0;
                    $messageCount = 0;
                    if ($navUser) {
                        $teamIds = $navUser->teams()->pluck('teams.id')->all();
                        $projectIds = $navUser->projects()->pluck('projects.id')->all();

                        $messageCount = \App\Models\Message::query()
                            ->where(function ($query) use ($teamIds, $projectIds) {
                                $query->whereIn('team_id', $teamIds)
                                    ->orWhereIn('project_id', $projectIds);
                            })
                            ->where(function ($query) use ($navUser) {
                                $query->whereNull('recipient_id')
                                    ->orWhere('recipient_id', $navUser->id)
                                    ->orWhere('sender_id', $navUser->id);
                            })
                            ->count();
                    }
                @endphp
                <button type="button" class="hidden lg:inline-flex app-topbar-link"
                        @click="$dispatch('toggle-sidebar')" aria-label="Mostrar u ocultar menu">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <button type="button" class="app-topbar-link"
                        @click="$dispatch('toggle-theme')" aria-label="Cambiar tema">
                    <svg class="theme-icon-light h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414m0-11.314L7.05 7.05m10.9 10.9l1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                    <svg class="theme-icon-dark h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" />
                    </svg>
                </button>
                @php
                    $recentNotifications = $navUser
                        ? $navUser->notifications()->latest()->take(3)->get()
                        : collect();
                @endphp
                <div class="relative" x-data="{ openNotifications: false, unreadCount: {{ $unreadCount }} }" @click.outside="openNotifications = false">
                    <button type="button" class="app-topbar-link"
                            @click="openNotifications = !openNotifications" aria-label="Ver notificaciones">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.17V11a6 6 0 10-12 0v3.17a2 2 0 01-.6 1.43L4 17h5m6 0a3 3 0 11-6 0h6z" />
                        </svg>
                        <template x-if="unreadCount > 0">
                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center h-4 min-w-[1rem] px-1 text-[10px] font-semibold text-white bg-red-600 rounded-full">
                            <span x-text="unreadCount"></span>
                            </span>
                        </template>
                    </button>
                    <div x-show="openNotifications" x-cloak x-transition
                         class="absolute right-0 mt-3 w-[22rem] max-w-[90vw] rounded-2xl border border-gray-200 bg-white shadow-lg z-50">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-900">Notificaciones</h4>
                                <span class="text-xs text-gray-500"><span x-text="unreadCount"></span> sin leer</span>
                            </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            @if($recentNotifications->isEmpty())
                                <div class="px-4 py-6 text-sm text-gray-500 text-center">
                                    No tienes notificaciones recientes.
                                </div>
                            @else
                                @foreach($recentNotifications as $notification)
                                    @php
                                        $data = $notification->data ?? [];
                                        $projectId = $data['project_id'] ?? null;
                                        $taskId = $data['task_id'] ?? null;
                                        $notificationUrl = ($projectId && $taskId)
                                            ? route('tasks.show', [$projectId, $taskId])
                                            : route('notifications.index');
                                        $title = $data['title'] ?? $data['message'] ?? $data['event'] ?? 'Nueva notificacion';
                                        $body = $data['body'] ?? $data['description'] ?? null;
                                    @endphp
                                    <div x-data="{ read: {{ $notification->read_at ? 'true' : 'false' }} }">
                                        <button type="button"
                                                class="w-full text-left px-4 py-3 hover:bg-gray-50 transition"
                                                @click="
                                                    if (!read) {
                                                        fetch('{{ route('notifications.read', $notification->id) }}', {
                                                            method: 'PATCH',
                                                            headers: {
                                                                'Accept': 'application/json',
                                                                'Content-Type': 'application/json',
                                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                                                            },
                                                            body: JSON.stringify({})
                                                        }).then(() => {
                                                            read = true;
                                                            unreadCount = Math.max(0, unreadCount - 1);
                                                            window.location = '{{ $notificationUrl }}';
                                                        });
                                                    } else {
                                                        window.location = '{{ $notificationUrl }}';
                                                    }
                                                ">
                                            <div class="flex items-start gap-3">
                                                <span class="mt-1 h-2 w-2 rounded-full" :class="read ? 'bg-gray-300' : 'bg-primary-500'"></span>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($title, 60) }}</p>
                                                    @if($body)
                                                        <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($body, 70) }}</p>
                                                    @endif
                                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="p-3 border-t border-gray-100">
                            <a href="{{ route('notifications.index') }}" class="btn-secondary text-xs w-full justify-center">Ver todo</a>
                        </div>
                    </div>
                </div>
                <a href="{{ route('messages.index') }}" class="app-topbar-link">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-9 8l3.5-3H20a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v9a2 2 0 002 2h1v3z" />
                    </svg>
                    @if($messageCount > 0)
                        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center h-4 min-w-[1rem] px-1 text-[10px] font-semibold text-white bg-blue-600 rounded-full">
                            {{ $messageCount }}
                        </span>
                    @endif
                </a>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="app-topbar-user text-sm font-medium focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.show')">
                            {{ __('Mi perfil') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Editar perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden ml-auto">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('teams.index')" :active="request()->routeIs('teams.*')">
                {{ __('Equipos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                {{ __('Proyectos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                {{ __('Notificaciones') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                {{ __('Mensajes') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.show')">
                    {{ __('Mi perfil') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Editar perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar Sesion') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
