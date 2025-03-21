<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                            Control de Asistencia Diaria
                        </h1>
                        <a href="{{ route('asistencia.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700">
                            Volver
                        </a>
                    </div>

                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Registra la asistencia diaria de los alumnos seleccionando la fecha y grupo.
                    </p>
                </div>

                <div class="p-6">
                    @if(!$configuracionExiste)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No existe configuración para el mes actual. Por favor, configure los días hábiles primero.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif(!$esDiaHabil)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        La fecha seleccionada no es un día hábil (fin de semana o periodo vacacional).
                                        Puedes continuar pero no se contabilizará como día hábil para las estadísticas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                            <input wire:model.live="fecha" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo</label>
                            <select wire:model.live="grupo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Todos los grupos</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Buscar alumno</label>
                            <input wire:model.live.debounce.300ms="search" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Nombre o apellidos...">
                        </div>
                    </div>

                    <div class="mb-4 flex space-x-2">
                        <button wire:click="marcarTodos('asistio')" type="button" class="inline-flex items-center px-4 py-2 bg-green-100 border border-transparent rounded-md font-semibold text-xs text-green-800 uppercase tracking-widest hover:bg-green-200 focus:bg-green-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Todos Presentes
                        </button>
                        <button wire:click="marcarTodos('falta')" type="button" class="inline-flex items-center px-4 py-2 bg-red-100 border border-transparent rounded-md font-semibold text-xs text-red-800 uppercase tracking-widest hover:bg-red-200 focus:bg-red-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Todos Ausentes
                        </button>
                    </div>

                    @if(count($asistencias) > 0)
                        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($asistencias as $alumno_id => $asistencia)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $asistencia['nombre_completo'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-2">
                                                    <button
                                                        wire:click="cambiarEstado({{ $alumno_id }}, 'asistio')"
                                                        type="button"
                                                        class="px-3 py-1 rounded-full text-xs font-medium transition-all duration-200 {{ $asistencia['estado'] === 'asistio' ? 'bg-green-500 text-white shadow-md ring-2 ring-green-300' : 'bg-green-100 text-green-800 hover:bg-green-200' }}"
                                                    >
                                                        <span class="flex items-center">
                                                            @if($asistencia['estado'] === 'asistio')
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            @endif
                                                            Presente
                                                        </span>
                                                    </button>
                                                    <button
                                                        wire:click="cambiarEstado({{ $alumno_id }}, 'falta')"
                                                        type="button"
                                                        class="px-3 py-1 rounded-full text-xs font-medium transition-all duration-200 {{ $asistencia['estado'] === 'falta' ? 'bg-red-500 text-white shadow-md ring-2 ring-red-300' : 'bg-red-100 text-red-800 hover:bg-red-200' }}"
                                                    >
                                                        <span class="flex items-center">
                                                            @if($asistencia['estado'] === 'falta')
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            @endif
                                                            Ausente
                                                        </span>
                                                    </button>
                                                    <button
                                                        wire:click="cambiarEstado({{ $alumno_id }}, 'justificada')"
                                                        type="button"
                                                        class="px-3 py-1 rounded-full text-xs font-medium transition-all duration-200 {{ $asistencia['estado'] === 'justificada' ? 'bg-yellow-500 text-white shadow-md ring-2 ring-yellow-300' : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' }}"
                                                    >
                                                        <span class="flex items-center">
                                                            @if($asistencia['estado'] === 'justificada')
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @endif
                                                            Justificada
                                                        </span>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input
                                                    wire:model="asistencias.{{ $alumno_id }}.observaciones"
                                                    wire:change="verificarCambios"
                                                    type="text"
                                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                    placeholder="Observaciones (opcional)"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div x-data="{ saving: false }" class="mt-4 flex justify-end">
                            <button
                                wire:click="guardarAsistencias"
                                x-on:click="saving = true"
                                x-on:notify.window="saving = false"
                                type="button"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{ 'opacity-75 cursor-not-allowed': saving }"
                                :disabled="saving || {{ $hayCambiosPendientes ? 'false' : 'true' }}"
                            >
                                <template x-if="!saving">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="saving">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="saving ? 'Guardando...' : 'Guardar Asistencias'"></span>
                            </button>
                        </div>

                        <!-- Notificación fija para confirmación de acciones -->
                        <div class="fixed bottom-4 right-4 z-50" id="asistencia-notification"
                             x-data="{ show: false, message: '' }"
                             x-on:notify.window="
                                if ($event.detail.message === 'Asistencias guardadas correctamente') {
                                    show = true;
                                    message = $event.detail.message;
                                    setTimeout(() => { show = false }, 3000);
                                }
                             ">
                            <div x-show="show"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                 x-transition:leave-end="opacity-0 transform translate-y-2"
                                 class="bg-green-500 text-white px-4 py-3 rounded-md shadow-lg flex items-center">
                                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="message"></span>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-6 text-center">
                            <p class="text-gray-500">No hay alumnos disponibles con los filtros seleccionados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
