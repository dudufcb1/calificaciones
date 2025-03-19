<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tablero') }}
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Welcome Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5 bg-indigo-600">
                <h3 class="text-lg font-semibold text-white">Bienvenido</h3>
            </div>
            <div class="p-5">
                <p class="text-gray-700">¡Has iniciado sesión correctamente!</p>
                <p class="mt-3 text-gray-600 text-sm">Bienvenido al sistema de calificaciones, desde aquí podrás gestionar todas tus tareas.</p>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5 bg-emerald-600">
                <h3 class="text-lg font-semibold text-white">Estadísticas</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-gray-600">Campos Formativos</div>
                    <div class="text-gray-900 font-medium">5</div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="text-gray-600">Calificaciones</div>
                    <div class="text-gray-900 font-medium">12</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">Estudiantes</div>
                    <div class="text-gray-900 font-medium">24</div>
                </div>
            </div>
        </div>

        <!-- Quick Access Card -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-5 bg-amber-600">
                <h3 class="text-lg font-semibold text-white">Acceso Rápido</h3>
            </div>
            <div class="p-5">
                <a href="{{ route('campos-formativos.index') }}" class="block w-full py-2 px-3 mb-2 bg-indigo-50 text-indigo-600 rounded hover:bg-indigo-100 transition-colors">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        Ver Campos Formativos
                    </div>
                </a>
                <a href="#" class="block w-full py-2 px-3 mb-2 bg-emerald-50 text-emerald-600 rounded hover:bg-emerald-100 transition-colors">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Gestionar Calificaciones
                    </div>
                </a>
                <a href="#" class="block w-full py-2 px-3 bg-amber-50 text-amber-600 rounded hover:bg-amber-100 transition-colors">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Ver Estudiantes
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Actividad Reciente</h3>
        </div>
        <div class="p-5">
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-full p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Se agregó un nuevo campo formativo</p>
                        <p class="text-sm text-gray-500">Hace 2 horas</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0 bg-emerald-100 rounded-full p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Se actualizaron calificaciones</p>
                        <p class="text-sm text-gray-500">Ayer</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0 bg-amber-100 rounded-full p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Nuevos estudiantes agregados al sistema</p>
                        <p class="text-sm text-gray-500">Hace 3 días</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="#" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Ver toda la actividad
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
