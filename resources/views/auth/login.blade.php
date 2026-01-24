<x-guest-layout>
    <div class="space-y-6">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Acceso</p>
            <h1 class="text-2xl font-semibold text-gray-800">Iniciar sesion</h1>
            <p class="text-sm text-gray-500 mt-1">Usa tus credenciales para entrar al panel local.</p>
        </div>

        <x-auth-session-status class="text-sm" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Correo')" />
                <x-text-input id="email" class="mt-2 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="tucorreo@ejemplo.com" />
                <x-input-error :messages="$errors->get('email')" class="form-error" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Contrasena')" />
                <x-text-input id="password" class="mt-2 w-full" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="form-error" />
            </div>

            <div class="flex items-center justify-between text-sm text-gray-500">
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[var(--app-primary)] focus:ring-0" name="remember">
                    <span>{{ __('Recordarme') }}</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm text-app-primary hover:underline" href="{{ route('password.request') }}">
                        {{ __('Olvidaste tu contrasena?') }}
                    </a>
                @endif
            </div>

            <div class="space-y-3 pt-2">
                <x-primary-button class="w-full justify-center">
                    {{ __('Entrar') }}
                </x-primary-button>
                @if (Route::has('register'))
                    <div class="text-sm text-center text-gray-500">
                        <span>Nuevo en el sistema?</span>
                        <a class="text-app-primary hover:underline" href="{{ route('register') }}">
                            {{ __('Crear cuenta') }}
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </div>
</x-guest-layout>
