<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                            Configuración de Días Hábiles
                        </h1>
                        <a href="{{ route('asistencia.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700">
                            Volver
                        </a>
                    </div>

                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Configura los días hábiles para cada mes del año. Esta configuración es necesaria para calcular correctamente las estadísticas de asistencia.
                    </p>
                </div>

                <div class="p-6">
                    <div class="mb-6 flex justify-between items-center">
                        <div class="flex space-x-4">
                            <button wire:click="cambiarAnio({{ $anio - 1 }})" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Anterior
                            </button>
                            <div class="text-lg font-bold">{{ $anio }}</div>
                            <button wire:click="cambiarAnio({{ $anio + 1 }})" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300">
                                Siguiente
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <div class="text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 mr-1 bg-green-500 rounded-full"></span>
                                Configurado
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                <span class="w-2 h-2 mr-1 bg-yellow-500 rounded-full"></span>
                                Vacaciones
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                                <span class="w-2 h-2 mr-1 bg-red-500 rounded-full"></span>
                                No configurado
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($configuraciones as $mesNum => $config)
                            <div class="border rounded-lg shadow p-4 {{ $config['id'] ? ($config['es_periodo_vacacional'] ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200') : 'bg-red-50 border-red-200' }}">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-medium text-lg mb-2">{{ $config['nombre_mes'] }}</h3>
                                    <button wire:click="editarConfiguracion({{ $mesNum }})" class="text-indigo-600 hover:text-indigo-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="mb-2">
                                    <span class="text-sm font-medium text-gray-700">Días hábiles:</span>
                                    <span class="text-sm ml-1">{{ $config['dias_habiles'] }}</span>
                                </div>
                                @if($config['es_periodo_vacacional'])
                                    <div class="mt-2 text-sm text-yellow-700 bg-yellow-100 p-1 px-2 rounded">
                                        Periodo de vacaciones
                                    </div>
                                @endif
                                @if(!$config['id'])
                                    <div class="mt-2 text-sm text-red-700 bg-red-100 p-1 px-2 rounded">
                                        No configurado
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar configuración -->
    @if($editando)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-10">
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Configurar {{ $mes ? $this->obtenerNombreMes($mes) : '' }} {{ $anio }}
                        </h3>
                        <div class="mt-4">
                            <div class="mb-4">
                                <label for="diasHabiles" class="block text-sm font-medium text-gray-700">Días hábiles</label>
                                <input wire:model="diasHabiles" id="diasHabiles" type="number" min="0" max="31" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" {{ $esPeriodoVacacional ? 'disabled' : '' }}>
                                @error('diasHabiles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input wire:model="esPeriodoVacacional" id="esPeriodoVacacional" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="esPeriodoVacacional" class="ml-2 block text-sm text-gray-900">Es periodo de vacaciones</label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Si marca esta opción, los días hábiles se establecerán en 0.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button wire:click="guardarConfiguracion" type="button" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                            Guardar
                        </button>
                        <button wire:click="cancelarEdicion" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
