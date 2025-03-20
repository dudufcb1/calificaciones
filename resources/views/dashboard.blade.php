<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tablero') }}
        </h2>
    </x-slot>

    <!-- Tarjetas informativas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Tarjeta de bienvenida -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5 bg-indigo-600">
                <h3 class="text-lg font-semibold text-white">Bienvenido, {{ $user->name }}</h3>
            </div>
            <div class="p-5">
                <p class="text-gray-700">{{ now()->format('d/m/Y') }}</p>
                <p class="mt-3 text-gray-600 text-sm">
                    @if($isAdmin)
                        Tienes acceso a todas las funciones de administración del sistema.
                    @else
                        Bienvenido al sistema de calificaciones, gestiona tus evaluaciones desde este panel.
                    @endif
                </p>
            </div>
        </div>

        <!-- Tarjeta de estadísticas -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5 bg-emerald-600">
                <h3 class="text-lg font-semibold text-white">Mis Estadísticas</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-gray-600">Campos Formativos</div>
                    <div class="text-gray-900 font-medium">{{ $stats['camposFormativos'] }}</div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="text-gray-600">Evaluaciones</div>
                    <div class="text-gray-900 font-medium">{{ $stats['evaluaciones'] }}</div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="text-gray-600">Alumnos</div>
                    <div class="text-gray-900 font-medium">{{ $stats['alumnos'] }}</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Grupos</div>
                    <div class="text-gray-900 font-medium">{{ $stats['grupos'] }}</div>
                </div>
            </div>
        </div>

        <!-- Acceso rápido -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5 bg-amber-600">
                <h3 class="text-lg font-semibold text-white">Acceso Rápido</h3>
            </div>
            <div class="p-5">
                @foreach($quickLinks as $link)
                <a href="{{ route($link['route']) }}" class="block w-full py-2 px-3 mb-2 bg-{{ $link['color'] }}-50 text-{{ $link['color'] }}-600 rounded hover:bg-{{ $link['color'] }}-100 transition-colors">
                    <div class="flex items-center">
                        @if($link['icon'] === 'academics')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                        @elseif($link['icon'] === 'document')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        @elseif($link['icon'] === 'users')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        @elseif($link['icon'] === 'group')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        @elseif($link['icon'] === 'admin')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                        {{ $link['text'] }}
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Evaluaciones recientes -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5 bg-blue-600">
                <h3 class="text-lg font-semibold text-white">Evaluaciones Recientes</h3>
            </div>
            <div class="p-5">
                @if(count($evaluacionesRecientes) > 0)
                    <div class="space-y-3">
                        @foreach($evaluacionesRecientes as $evaluacion)
                            <div class="border-b border-gray-100 pb-2">
                                <p class="text-sm font-medium text-gray-900">{{ $evaluacion->titulo }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $evaluacion->campoFormativo->nombre }}
                                    <span class="ml-1">{{ $evaluacion->created_at->format('d/m/Y') }}</span>
                                </p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('evaluaciones.index') }}" class="text-sm text-blue-600 hover:text-blue-500">
                            Ver todas las evaluaciones
                        </a>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">
                        No tienes evaluaciones recientes
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Sección de administración (solo para administradores) -->
    @if($isAdmin)
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Administración del Sistema</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Estadísticas generales -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-5 bg-purple-600">
                        <h3 class="text-lg font-semibold text-white">Estadísticas Globales</h3>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-gray-600">Total Usuarios</div>
                            <div class="text-gray-900 font-medium">{{ $adminStats['totalUsuarios'] }}</div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-gray-600">Usuarios Activos</div>
                            <div class="text-green-600 font-medium">{{ $adminStats['usuariosActivos'] }}</div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-gray-600">Usuarios Pendientes</div>
                            <div class="text-yellow-600 font-medium">{{ $adminStats['usuariosPendientes'] }}</div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-gray-600">Usuarios Inactivos</div>
                            <div class="text-red-600 font-medium">{{ $adminStats['usuariosInactivos'] }}</div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-gray-600">Total Evaluaciones</div>
                            <div class="text-gray-900 font-medium">{{ $adminStats['totalEvaluaciones'] }}</div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-gray-600">Total Alumnos</div>
                            <div class="text-gray-900 font-medium">{{ $adminStats['totalAlumnos'] }}</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-gray-600">Total Grupos</div>
                            <div class="text-gray-900 font-medium">{{ $adminStats['totalGrupos'] }}</div>
                        </div>
                    </div>
                </div>

                <!-- Usuarios pendientes de confirmación -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-5 bg-yellow-600">
                        <h3 class="text-lg font-semibold text-white">Usuarios Pendientes</h3>
                    </div>
                    <div class="p-5">
                        @if(count($usuariosPendientes) > 0)
                            <div class="space-y-3">
                                @foreach($usuariosPendientes as $usuario)
                                    <div class="border-b border-gray-100 pb-2">
                                        <p class="text-sm font-medium text-gray-900">{{ $usuario->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $usuario->email }}</p>
                                        <p class="text-xs text-gray-400">{{ $usuario->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-center">
                                <a href="{{ route('usuarios.index') }}" class="text-sm text-yellow-600 hover:text-yellow-500">
                                    Gestionar usuarios pendientes
                                </a>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">
                                No hay usuarios pendientes de confirmación
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Últimos usuarios registrados -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden col-span-2">
                    <div class="p-5 bg-indigo-600">
                        <h3 class="text-lg font-semibold text-white">Últimos Usuarios Registrados</h3>
                    </div>
                    <div class="p-5">
                        @if(count($ultimosUsuarios) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nombre
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Email
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estado
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($ultimosUsuarios as $usuario)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $usuario->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $usuario->email }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($usuario->status === 'active')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Activo
                                                        </span>
                                                    @elseif($usuario->status === 'pending')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Pendiente
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Inactivo
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $usuario->created_at->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="{{ route('usuarios.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                                    Ver todos los usuarios
                                </a>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">
                                No hay usuarios registrados recientemente
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Actividad reciente -->
    <div class="mt-8 bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Actividad Reciente</h3>
        </div>
        <div class="p-5">
            @if(count($actividadReciente) > 0)
                <div class="space-y-4">
                    @foreach($actividadReciente as $actividad)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-indigo-100 rounded-full p-2">
                                @if($actividad['icono'] === 'document-add')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                @elseif($actividad['icono'] === 'user-add')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                @elseif($actividad['icono'] === 'academic-cap')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path fill="#fff" d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path fill="#fff" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $actividad['titulo'] }}</p>
                                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($actividad['fecha'])->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-4">
                    No hay actividad reciente para mostrar
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
