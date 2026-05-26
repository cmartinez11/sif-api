<x-guest-layout>
    <!-- 1. PANTALLA DE BIENVENIDA (SPLASH SCREEN) -->
    <div id="splash-screen" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: #28a745; /* Verde Fénix */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        color: white;
        transition: opacity 0.6s ease-out;
    ">
        <div style="text-align: center; font-family: sans-serif;">
            <h1 style="font-size: 6rem; font-weight: 800; margin: 0; line-height: 1;">SIF</h1>
            <div style="width: 60px; height: 4px; background: white; margin: 15px auto;"></div>
            <p style="font-size: 1.2rem; text-transform: uppercase; letter-spacing: 4px; font-weight: 300;">
                Sistema de Información Fénix
            </p>
        </div>
    </div>
    <div id="login-content" style="visibility: hidden; opacity: 0; transition: opacity 0.8s ease-in;">
        <x-auth-card>
            <x-slot name="logo">
                <a href="/">
                    <x-application-logo class="w-80 h-auto fill-current text-gray-500" />
                </a>
            </x-slot>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Validation Errors -->
            <x-auth-validation-errors class="mb-4" :errors="$errors" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-label for="email" :value="__('Correo Electrónico')" />

                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-label for="password" :value="__('Contraseña')" />

                    <x-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-fenix-green shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>
                </div>

                <div class="flex items-center justify-center mt-4">
                    <x-button class="ml-3">
                        Iniciar Sesión
                    </x-button>
                </div>
            </form>
        </x-auth-card>
    </div>
    <!-- 3. SCRIPT DE CONTROL -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const splash = document.getElementById('splash-screen');
            const login = document.getElementById('login-content');

            // Esperar 3 segundos exactos antes de empezar a desaparecer
            setTimeout(() => {
                // Desvanecer el verde
                splash.style.opacity = '0';
                
                // Mostrar el login suavemente
                login.style.visibility = 'visible';
                login.style.opacity = '1';

                // Eliminar el splash del DOM después de la animación para no interferir con clicks
                setTimeout(() => {
                    splash.remove();
                }, 600);
            }, 3500);
        });
    </script>
</x-guest-layout>

