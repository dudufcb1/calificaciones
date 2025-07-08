<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Estilos para ocultar barras de desplazamiento -->
        <style>
            /* Ocultar barra de desplazamiento pero mantener funcionalidad de scroll */
            .hide-scrollbar {
                -ms-overflow-style: none;  /* Para Internet Explorer y Edge */
                scrollbar-width: none;     /* Para Firefox */
            }
            .hide-scrollbar::-webkit-scrollbar {
                display: none;  /* Para Chrome, Safari y Opera */
            }

            /* Ocultar elementos con Alpine.js antes de que se inicialice */
            [x-cloak] {
                display: none !important;
            }

            /* Estilos adicionales para modales */
            .modal-backdrop {
                background-color: rgba(0, 0, 0, 0.5);
                position: fixed;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: 40;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
        <!-- Notificaciones -->
        <div x-data="{ notification: false, message: '', type: 'success' }"
             x-on:notify.window="notification = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { notification = false }, 3000)"
             class="fixed top-4 right-4 z-50">
            <div x-show="notification"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-90"
                 :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error', 'bg-blue-500': type === 'info' }"
                 class="rounded-md px-4 py-3 text-white shadow-md">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <template x-if="type === 'success'">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="type === 'error'">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </template>
                        <template x-if="type === 'info'">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white" x-text="message"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen flex">
            <!-- Mobile sidebar backdrop -->
            <div
                x-cloak
                x-show="sidebarOpen"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
            ></div>

            <!-- Sidebar -->
            <div
                x-cloak
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 w-64 bg-indigo-900 text-white transition duration-200 transform z-50 lg:translate-x-0 lg:static lg:inset-0 flex flex-col"
            >
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 px-4 bg-indigo-950">
                    <div class="text-xl font-bold">
                        {{ config('app.name', 'Laravel') }}
                    </div>
                </div>

                <!-- Close button (mobile only) -->
                <button
                    @click="sidebarOpen = false"
                    class="absolute p-1 top-3 right-3 text-white lg:hidden hover:text-gray-200 focus:outline-none"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- User profile -->
                <div class="flex flex-col items-center mt-6 pb-5 border-b border-indigo-800">
                    <div class="p-4 w-14 h-14 flex items-center justify-center rounded-full bg-indigo-800 text-white text-xl uppercase font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <h4 class="mt-2 font-medium text-gray-200 p-4">{{ auth()->user()->name }}</h4>
                </div>

                <!-- Navigation -->
                <nav class="px-4 mt-5 flex-1 overflow-y-auto hide-scrollbar">
                    <h3 class="px-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">Menu</h3>

                    <a href="{{ route('dashboard') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('dashboard') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Panel
                    </a>

                    <a href="{{ route('grupos.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('grupos.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Grupos
                    </a>

                    <a href="{{ route('alumnos.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('alumnos.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Alumnos
                    </a>

                    <a href="{{ route('campos-formativos.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('campos-formativos.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        Campos Formativos
                    </a>

                    <a href="{{ route('ciclos.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('ciclos.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Ciclos Escolares
                    </a>

                    <a href="{{ route('momentos.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('momentos.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Momentos Educativos
                    </a>

                    <a href="{{ route('evaluaciones.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('evaluaciones.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Evaluaciones
                    </a>

                    <a href="{{ route('asistencia.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('asistencia.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Asistencia
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('usuarios.index') }}" class="flex items-center px-2 py-3 mt-2 text-sm {{ request()->routeIs('usuarios.*') ? 'bg-indigo-800 text-white rounded-lg' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-lg transition-colors duration-150' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Gestión de Usuarios
                        </a>
                    @endif
                </nav>

                <!-- Logout -->
                <div class="px-4 py-4 border-t border-indigo-800 mt-auto">
                    <livewire:layout.sidebar-logout />
                </div>
            </div>

            <!-- Content area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Mobile header -->
                <header class="bg-white py-4 px-4 shadow-md lg:hidden">
                    <div class="flex items-center justify-between">
                        <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="text-lg font-semibold text-indigo-900">
                            {{ config('app.name', 'Laravel') }}
                        </div>
                        <div class="w-8"></div> <!-- Spacer for balance -->
                    </div>
                </header>

                <!-- Page content -->
                <main class="flex-1 overflow-auto">
                    <!-- Page Heading -->
                    @if (isset($header))
                        <header class="bg-white shadow-sm">
                            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif

                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        @if (session()->has('message'))
                            <div x-data="{ show: true }"
                                 x-show="show"
                                 x-init="setTimeout(() => show = false, 3000)"
                                 class="mb-4 p-4 bg-green-500 text-white rounded-lg shadow-lg">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div x-data="{ show: true }"
                                 x-show="show"
                                 x-init="setTimeout(() => show = false, 5000)"
                                 class="mb-4 p-4 bg-red-500 text-white rounded-lg shadow-lg">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @livewireScripts

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </body>
</html>
