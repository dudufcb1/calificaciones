<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Panel de control</h2>

        <!-- Guía de flujo de trabajo -->
        <div class="mb-8">
            @livewire('components.resource-verifier', ['context' => $resourceContext])
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Ciclos y Grupos</h3>
                        <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['ciclos'] }} / {{ $stats['grupos'] }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-indigo-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">Ciclos y grupos disponibles</p>
                <div class="mt-4 flex justify-between">
                    <a href="{{ route('ciclos.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Ver Ciclos</a>
                    <a href="{{ route('grupos.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Ver Grupos</a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Alumnos</h3>
                        <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['alumnos'] }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">Alumnos registrados</p>
                <div class="mt-4">
                    <a href="{{ route('alumnos.index') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">Ver Alumnos</a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Momentos y Evaluaciones</h3>
                        <div class="mt-2 text-3xl font-bold text-purple-600">{{ $stats['momentos'] }} / {{ $stats['evaluaciones'] }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-purple-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">Momentos educativos y evaluaciones</p>
                <div class="mt-4 flex justify-between">
                    <a href="{{ route('momentos.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">Ver Momentos</a>
                    <a href="{{ route('evaluaciones.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">Ver Evaluaciones</a>
                </div>
            </div>
        </div>

        <!-- Ciclo actual -->
        <div class="bg-white rounded-lg shadow-md p-5 mb-8">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ciclo escolar activo</h3>
            @if($cicloActivo)
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xl font-bold text-indigo-600">{{ $cicloActivo->nombre }}</h4>
                        <p class="text-gray-500">{{ $cicloActivo->anio_inicio }} - {{ $cicloActivo->anio_fin }}</p>
                    </div>
                    <a href="{{ route('ciclos.index') }}" class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-200 transition">
                        Gestionar ciclos
                    </a>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                No hay un ciclo escolar activo.
                                <a href="{{ route('ciclos.index') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                    Configurar un ciclo activo
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Últimos registros -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Últimos momentos -->
            <div class="bg-white rounded-lg shadow-md p-5">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Últimos momentos educativos</h3>
                @if(count($ultimosMomentos) > 0)
                    <ul class="space-y-3">
                        @foreach($ultimosMomentos as $momento)
                            <li class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <h4 class="font-medium text-gray-800">{{ $momento->nombre }}</h4>
                                    <p class="text-sm text-gray-500">{{ $momento->fecha->format('d/m/Y') }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full {{ $momento->ciclo ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $momento->ciclo ? $momento->ciclo->nombre : 'Sin ciclo' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 text-right">
                        <a href="{{ route('momentos.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Ver todos</a>
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No hay momentos educativos registrados
                    </div>
                @endif
            </div>

            <!-- Últimas evaluaciones -->
            <div class="bg-white rounded-lg shadow-md p-5">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Últimas evaluaciones</h3>
                @if(count($ultimasEvaluaciones) > 0)
                    <ul class="space-y-3">
                        @foreach($ultimasEvaluaciones as $evaluacion)
                            <li class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <h4 class="font-medium text-gray-800">{{ $evaluacion->titulo }}</h4>
                                    <p class="text-sm text-gray-500">{{ $evaluacion->created_at->format('d/m/Y') }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">
                                    {{ $evaluacion->campo_formativo->nombre ?? 'Sin campo' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 text-right">
                        <a href="{{ route('evaluaciones.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Ver todas</a>
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No hay evaluaciones registradas
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
