<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistema de Calificaciones') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .bg-dots-darker {
                background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
            }

            .animate-pulse-slow {
                animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.7;
                }
            }

            @keyframes fadeIn {
                0% {
                    opacity: 0;
                    transform: translateY(10px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .fade-in {
                animation: fadeIn 0.8s ease-out forwards;
            }

            .animate-float {
                animation: float 6s ease-in-out infinite;
            }

            @keyframes float {
                0% {
                    transform: translateY(0px);
                }
                50% {
                    transform: translateY(-20px);
                }
                100% {
                    transform: translateY(0px);
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-blue-100 bg-dots-darker">
            <div class="relative p-6 lg:p-8">
                <nav class="flex justify-between items-center">
                    <div class="flex items-center transition duration-300 transform hover:scale-105">
                        <span class="text-4xl text-indigo-600 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 animate-pulse-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                            </svg>
                        </span>
                        <h1 class="text-2xl font-bold text-gray-800">Sistema de Calificaciones</h1>
                    </div>

                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 rounded-md border border-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 text-center">Iniciar sesión</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 text-center">Registrarse</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </nav>

                <div class="mt-16 mx-auto max-w-7xl">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div class="fade-in" style="animation-delay: 0.2s;">
                            <h2 class="text-4xl font-extrabold text-gray-900 leading-tight lg:text-5xl">
                                Sistema de Gestión de <span class="text-indigo-600">Calificaciones</span>
                            </h2>
                            <p class="mt-6 text-xl text-gray-600">
                                Plataforma educativa para la gestión eficiente de campos formativos, evaluaciones y seguimiento del rendimiento de los alumnos.
                            </p>
                            <div class="mt-8 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="px-8 py-3 text-base font-medium text-white bg-indigo-600 rounded-md shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 transform hover:-translate-y-0.5 text-center">
                                        Ir al Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="px-8 py-3 text-base font-medium text-white bg-indigo-600 rounded-md shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 transform hover:-translate-y-0.5 text-center">
                                        Comenzar ahora
                                    </a>
                                    <a href="#caracteristicas" class="px-8 py-3 text-base font-medium text-indigo-600 bg-white rounded-md shadow-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 transform hover:-translate-y-0.5 text-center">
                                        Conocer más
                                    </a>
                                @endauth
                            </div>
                        </div>
                        <div class="flex justify-center fade-in animate-float" style="animation-delay: 0.5s;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-64 w-64 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>

                    <div id="caracteristicas" class="mt-24 fade-in" style="animation-delay: 0.8s;">
                        <h3 class="text-2xl font-bold text-center text-gray-900 mb-12">Características principales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="bg-white p-8 rounded-lg shadow-lg transform transition duration-300 hover:scale-105">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <h4 class="text-xl font-semibold text-gray-900 mb-2">Gestión de Evaluaciones</h4>
                                <p class="text-gray-600">
                                    Crea, edita y administra evaluaciones con múltiples criterios y ponderaciones ajustables.
                                </p>
                            </div>

                            <div class="bg-white p-8 rounded-lg shadow-lg transform transition duration-300 hover:scale-105">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h4 class="text-xl font-semibold text-gray-900 mb-2">Seguimiento de Alumnos</h4>
                                <p class="text-gray-600">
                                    Realiza un seguimiento detallado del rendimiento de cada alumno en diferentes campos formativos.
                                </p>
                            </div>

                            <div class="bg-white p-8 rounded-lg shadow-lg transform transition duration-300 hover:scale-105">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h4 class="text-xl font-semibold text-gray-900 mb-2">Reportes y Estadísticas</h4>
                                <p class="text-gray-600">
                                    Accede a estadísticas en tiempo real y genera reportes personalizados para análisis y toma de decisiones.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="mt-24 py-8 border-t border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-600">
                        <p>&copy; {{ date('Y') }} Sistema de Calificaciones. Todos los derechos reservados.</p>
                        <p class="mt-2">Desarrollado con <span class="text-red-500">♥</span> utilizando Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>
