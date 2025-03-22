<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('campos-formativos.index')" :active="request()->routeIs('campos-formativos.*')">
                        {{ __('Campos Formativos') }}
                    </x-nav-link>

                    <x-nav-link :href="route('alumnos.index')" :active="request()->routeIs('alumnos.*')">
                        {{ __('Alumnos') }}
                    </x-nav-link>

                    <x-nav-link :href="route('evaluaciones.index')" :active="request()->routeIs('evaluaciones.*')">
                        {{ __('Evaluaciones') }}
                    </x-nav-link>

                    <x-nav-link :href="route('grupos.index')" :active="request()->routeIs('grupos.*')">
                        {{ __('Grupos') }}
                    </x-nav-link>

                    <!-- Dropdown para Asistencia -->
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('asistencia.*') ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : '' }}">
                                    <div>{{ __('Asistencia') }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('asistencia.index')">
                                    {{ __('Resumen') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('asistencia.pasar-lista')">
                                    {{ __('Pasar Lista') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('asistencia.mensual')">
                                    {{ __('Vista Mensual') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('asistencia.reporte')">
                                    {{ __('Reportes') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('asistencia.configuracion')">
                                    {{ __('Configuración') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Dropdown para Ciclos/Momentos -->
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('ciclos.*') || request()->routeIs('momentos.*') ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : '' }}">
                                    <div>{{ __('Ciclos/Momentos') }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('ciclos.index')">
                                    {{ __('Ciclos Escolares') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('momentos.index')">
                                    {{ __('Momentos Educativos') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
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

            <x-responsive-nav-link :href="route('campos-formativos.index')" :active="request()->routeIs('campos-formativos.*')">
                {{ __('Campos Formativos') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('alumnos.index')" :active="request()->routeIs('alumnos.*')">
                {{ __('Alumnos') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('evaluaciones.index')" :active="request()->routeIs('evaluaciones.*')">
                {{ __('Evaluaciones') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('grupos.index')" :active="request()->routeIs('grupos.*')">
                {{ __('Grupos') }}
            </x-responsive-nav-link>

            <!-- Sección de Asistencia en modo responsive -->
            <div class="pt-2 pb-3 space-y-1">
                <div class="font-medium pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600">
                    {{ __('Asistencia') }}
                </div>
                <x-responsive-nav-link :href="route('asistencia.index')" :active="request()->routeIs('asistencia.index')">
                    {{ __('Resumen') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asistencia.pasar-lista')" :active="request()->routeIs('asistencia.pasar-lista')">
                    {{ __('Pasar Lista') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asistencia.mensual')" :active="request()->routeIs('asistencia.mensual')">
                    {{ __('Vista Mensual') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asistencia.reporte')" :active="request()->routeIs('asistencia.reporte')">
                    {{ __('Reportes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asistencia.configuracion')" :active="request()->routeIs('asistencia.configuracion')">
                    {{ __('Configuración') }}
                </x-responsive-nav-link>
            </div>

            <!-- Sección de Ciclos/Momentos en modo responsive -->
            <div class="pt-2 pb-3 space-y-1">
                <div class="font-medium pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600">
                    {{ __('Ciclos/Momentos') }}
                </div>
                <x-responsive-nav-link :href="route('ciclos.index')" :active="request()->routeIs('ciclos.index')">
                    {{ __('Ciclos Escolares') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('momentos.index')" :active="request()->routeIs('momentos.index')">
                    {{ __('Momentos Educativos') }}
                </x-responsive-nav-link>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
