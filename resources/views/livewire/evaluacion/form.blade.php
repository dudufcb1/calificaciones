<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-3">
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ $editing ? 'Editar Evaluación' : 'Evaluar Momento' }}
                            </h2>

                            @if($editing)
                                @if($is_draft ?? true)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Borrador
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Finalizada
                                    </span>
                                @endif
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $autoSaveMessage }}
                        </div>
                    </div>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ $editing ? 'Actualice los datos de la evaluación' : 'Seleccione el momento y grupo para crear evaluaciones para todos los campos formativos asociados' }}
                    </p>

                    <form wire:submit.prevent="finalizar" class="space-y-6">
                        <!-- Selección de Momento y Grupo -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="momentoId" class="block text-sm font-medium text-gray-700">Momento a Evaluar</label>
                                <select wire:model.live="momentoId" id="momentoId"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ $editing ? 'disabled' : '' }}>
                                    <option value="">Seleccione un momento</option>
                                    @foreach($momentos as $momento)
                                        <option value="{{ $momento->id }}">{{ $momento->nombre }} ({{ $momento->fecha->format('d/m/Y') }})</option>
                                    @endforeach
                                </select>
                                @error('momentoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="grupoId" class="block text-sm font-medium text-gray-700">Grupo a Evaluar</label>
                                <select wire:model.live="grupoId" id="grupoId"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ $editing ? 'disabled' : '' }}>
                                    <option value="">Seleccione un grupo</option>
                                    @foreach($grupos as $grupo)
                                        <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('grupoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="fecha_evaluacion" class="block text-sm font-medium text-gray-700">Fecha de Evaluación</label>
                                <input type="date" wire:model="fecha_evaluacion" id="fecha_evaluacion"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        @if(!$editing && $momentoId)
                            <div class="bg-indigo-50 p-4 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1 md:flex md:justify-between">
                                        <p class="text-sm text-indigo-700">
                                            Al guardar, se crearán evaluaciones para todos los campos formativos del momento seleccionado.
                                            @if($momentoId && count($camposFormativos) > 0)
                                                <span class="font-medium">Campos formativos incluidos ({{ count($camposFormativos) }}):
                                                    {{ collect($camposFormativos)->pluck('nombre')->join(', ') }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($editing)
                            <div>
                                <label for="campoFormativoId" class="block text-sm font-medium text-gray-700">Campo Formativo</label>
                                <select wire:model.live="campoFormativoId" id="campoFormativoId"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ $editing ? 'disabled' : '' }}>
                                    <option value="">Seleccione un campo formativo</option>
                                    @foreach($camposFormativos as $campo)
                                        <option value="{{ $campo['id'] }}">{{ $campo['nombre'] }}</option>
                                    @endforeach
                                </select>
                                @error('campoFormativoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Criterios de evaluación -->
                        @if($showCriterios)
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900">Criterios de Evaluación</h3>
                                <div class="mt-2 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criterio</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                                <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($criterios as $index => $criterio)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $criterio['nombre'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $criterio['descripcion'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-center text-gray-500">{{ $criterio['porcentaje'] }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Selección de alumnos -->
                        @if (!$editing && $grupoId && $campoFormativoId)
                            <div class="mt-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Alumnos a Evaluar</h3>
                                    <button type="button" wire:click="toggleSeleccionAlumnos"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        {{ $mostrarSeleccionAlumnos ? 'Ocultar selección' : 'Modificar selección' }}
                                    </button>
                                </div>

                                @if($mostrarSeleccionAlumnos)
                                    <div class="mt-2 p-4 border border-gray-300 rounded-md">
                                        <div class="flex justify-between items-center mb-2">
                                            <h4 class="text-sm font-medium text-gray-700">Seleccionar alumnos</h4>
                                            <button type="button" wire:click="agregarAlumnosSeleccionados"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Agregar seleccionados
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                            @forelse($alumnosSeleccionados as $index => $alumno)
                                                <div class="flex items-center">
                                                    <input type="checkbox" wire:model="alumnosSeleccionados.{{ $index }}.selected"
                                                        id="alumno-{{ $alumno['id'] }}"
                                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                                    <label for="alumno-{{ $alumno['id'] }}" class="ml-2 text-sm text-gray-700">
                                                        {{ $alumno['nombre'] }}
                                                    </label>
                                                </div>
                                            @empty
                                                <div class="col-span-3 text-sm text-gray-500">No hay más alumnos disponibles para agregar.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Evaluación de alumnos -->
                        @if($showAlumnos)
                            <div class="mt-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Alumnos a Evaluar</h3>
                                    @if($editing && $campoFormativoId)
                                    <button type="button" wire:click="cargarAlumnosGrupo"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Recargar Alumnos
                                    </button>
                                    @endif
                                </div>

                                @if(empty($alumnosEvaluados))
                                    <div class="mt-4 p-4 border border-yellow-300 bg-yellow-50 rounded-md">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">No hay alumnos cargados</h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>Asegúrese de que el grupo y el campo formativo estén seleccionados correctamente.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="overflow-x-auto mt-4">
                                        <!-- Tabla de alumnos y calificaciones -->
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
                                                    @foreach($criterios as $criterio)
                                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ $criterio['nombre'] }} ({{ $criterio['porcentaje'] }}%)
                                                        </th>
                                                    @endforeach
                                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</th>
                                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($alumnosEvaluados as $index => $alumno)
                                                    <tr>
                                                        <td class="px-4 py-2 whitespace-nowrap">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                @if(isset($alumno['nombre']))
                                                                    {{ $alumno['nombre'] }}
                                                                @elseif(isset($alumno['nombre_completo']))
                                                                    {{ $alumno['nombre_completo'] }}
                                                                @else
                                                                    Alumno #{{ $alumno['alumno_id'] ?? $index+1 }}
                                                                @endif
                                                            </div>
                                                        </td>

                                                        @foreach($alumno['calificaciones'] as $calIndex => $calificacion)
                                                            <td class="px-4 py-2">
                                                                <input type="text"
                                                                    x-data="{
                                                                        value: '{{ $alumno['calificaciones'][$calIndex]['valor'] }}',
                                                                        init() {
                                                                            this.$watch('value', value => {
                                                                                // Remove non-numeric characters except . and ,
                                                                                let cleaned = value.replace(/[^\d.,]/g, '');

                                                                                // Convert comma to period for consistency
                                                                                cleaned = cleaned.replace(',', '.');

                                                                                // Check if it's a decimal
                                                                                if (cleaned.includes('.')) {
                                                                                    // Convert 6.7 format to 67
                                                                                    let parts = cleaned.split('.');
                                                                                    if (parts[1]) {
                                                                                        cleaned = parts[0] + parts[1].substring(0, 1);
                                                                                    } else {
                                                                                        cleaned = parts[0];
                                                                                    }
                                                                                }

                                                                                // Ensure value is between 0 and 100
                                                                                let num = parseInt(cleaned, 10) || 0;
                                                                                if (num > 100) num = 100;
                                                                                if (num < 0) num = 0;

                                                                                this.value = num.toString();

                                                                                // Update Livewire model
                                                                                $wire.set('alumnosEvaluados.{{ $index }}.calificaciones.{{ $calIndex }}.valor', num);
                                                                            });
                                                                        }
                                                                    }"
                                                                    x-model="value"
                                                                    inputmode="numeric"
                                                                    class="input-calificacion block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </td>
                                                        @endforeach

                                                        <td class="px-4 py-2 whitespace-nowrap text-center">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $alumno['promedio'] >= 70 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                {{ number_format($alumno['promedio'], 1) }}
                                                            </span>
                                                        </td>

                                                        <td class="px-4 py-2">
                                                            <textarea
                                                                wire:model.live="alumnosEvaluados.{{ $index }}.observaciones"
                                                                rows="1"
                                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                placeholder="Observaciones"></textarea>
                                                        </td>

                                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                                            <button type="button" wire:click="limpiarCalificaciones({{ $index }})"
                                                                class="text-indigo-600 hover:text-indigo-900">Limpiar</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Botones -->
                        <div class="flex justify-end space-x-2">
                            @if($editing)
                            <button type="button" wire:click="recalcularTodos"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Recalcular promedios
                            </button>
                            <button type="button" wire:click="mostrarModalAsistencias"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Aplicar % Asistencia
                            </button>
                            <button type="button" wire:click="exportarExcel" class="btn-export-excel
                                inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Exportar a Excel
                            </button>
                            @endif
                            <a href="{{ route('evaluaciones.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancelar
                            </a>

                            @if($editing)
                                <!-- Botón para guardar cambios (mantiene como borrador) -->
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Guardar Cambios
                                </button>

                                <!-- Botón para finalizar definitivamente -->
                                @if($is_draft ?? true)
                                    <button type="button" wire:click="finalizarDefinitivamente"
                                            wire:confirm="¿Está seguro de finalizar esta evaluación? Una vez finalizada no se podrá editar."
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Finalizar Evaluación
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Evaluación Finalizada
                                    </span>
                                @endif
                            @else
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Crear Evaluaciones
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para aplicar porcentajes de asistencia -->
    <div x-data="{
        open: @entangle('mostrarModalAsistencia').live,
        tab: 'preview',
        toggleTab(tabName) {
            this.tab = tabName;
        }
    }"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-1">
                                Aplicar Porcentajes de Asistencia
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Campo formativo: <span class="font-semibold">{{ $selectedCampoFormativo ? $selectedCampoFormativo['nombre'] : 'No seleccionado' }}</span>
                            </p>

                            <div class="mb-4 border-b border-gray-200">
                                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                                    <li class="mr-2">
                                        <a href="#"
                                           @click.prevent="toggleTab('preview')"
                                           :class="tab === 'preview' ? 'border-b-2 border-blue-500 text-blue-500' : 'border-transparent text-gray-500 hover:text-gray-600'"
                                           class="inline-flex p-4 rounded-t-lg group">
                                            Vista Previa
                                        </a>
                                    </li>
                                    <li class="mr-2">
                                        <a href="#"
                                           @click.prevent="toggleTab('settings')"
                                           :class="tab === 'settings' ? 'border-b-2 border-blue-500 text-blue-500' : 'border-transparent text-gray-500 hover:text-gray-600'"
                                           class="inline-flex p-4 rounded-t-lg group">
                                            Configuración
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div x-show="tab === 'preview'" class="mb-4">
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600">
                                        Esta tabla muestra los porcentajes de asistencia calculados para el campo formativo seleccionado.
                                        Seleccione una columna de evaluación para aplicar estos porcentajes como calificaciones.
                                    </p>
                                </div>

                                @if($hayColumnaConPorcentajes)
                                <div class="mb-4 p-4 bg-yellow-50 rounded border border-yellow-200">
                                    <p class="text-sm text-yellow-700">
                                        Ya hay una columna con porcentajes de asistencia aplicados. Solo se permite aplicar porcentajes a una columna a la vez.
                                    </p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Si desea aplicar porcentajes a otra columna, primero debe eliminar la asignación actual.
                                    </p>
                                    <button type="button" wire:click="resetearAsignacionPorcentajes" class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Eliminar asignación actual
                                    </button>
                                </div>
                                @endif

                                <div class="mb-4">
                                    <label for="criterioSelector" class="block text-sm font-medium text-gray-700 mb-2">Seleccione la columna donde aplicar los porcentajes:</label>
                                    <select id="criterioSelector" wire:model.live="criterioSeleccionadoId"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Seleccione una columna...</option>
                                        @foreach($criterios ?? [] as $criterio)
                                            <option value="{{ $criterio['id'] }}"
                                                @class(['bg-green-100 font-semibold' => in_array($criterio['id'], $columnasConPorcentajes ?? [])])
                                                {{ $hayColumnaConPorcentajes && !in_array($criterio['id'], $columnasConPorcentajes ?? []) ? 'disabled' : '' }}>
                                                {{ $criterio['nombre'] }} {{ in_array($criterio['id'], $columnasConPorcentajes ?? []) ? '(ya asignado)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($columnaAsignadaPorcentajes)
                                <div class="mb-4 p-4 bg-yellow-50 rounded border border-yellow-200">
                                    <p class="text-sm text-yellow-700 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Esta columna ya tiene porcentajes de asistencia aplicados.
                                    </p>
                                    <button type="button" wire:click="resetearAsignacionPorcentajes" class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Eliminar asignación
                                    </button>
                                </div>
                                @endif

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Alumno
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Total Días
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Asistencias
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Inasistencias
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    % Asistencia
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($porcentajesAsistencia as $alumnoId => $datos)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $datos['nombre'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $datos['total_dias'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $datos['asistencias'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $datos['inasistencias'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            {{ round($datos['porcentaje']) }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div x-show="tab === 'settings'" class="mb-4" style="display: none;">
                                <div class="mb-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-3">
                                        <label for="inicioMes" class="block text-sm font-medium text-gray-700">Fecha de inicio</label>
                                        <div class="mt-1">
                                            <input type="date" id="inicioMes" wire:model.live="inicioMes" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label for="finMes" class="block text-sm font-medium text-gray-700">Fecha de fin</label>
                                        <div class="mt-1">
                                            <input type="date" id="finMes" wire:model.live="finMes" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="button" wire:click="actualizarPorcentajesAsistencia" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Actualizar Porcentajes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="aplicarPorcentajesAsistencia"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                            {{ (!$criterioSeleccionadoId || $columnaAsignadaPorcentajes || ($hayColumnaConPorcentajes && !in_array($criterioSeleccionadoId, $columnasConPorcentajes))) ? 'disabled' : '' }}>
                        Aplicar a calificaciones
                    </button>
                    <button type="button" wire:click="cerrarModalAsistencia" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Escuchar evento para confirmar aplicación de asistencia
            document.addEventListener('livewire:init', () => {
                Livewire.on('confirm-apply-attendance', (event) => {
                    const data = event[0];
                    Swal.fire({
                        title: '¿Aplicar porcentajes de asistencia?',
                        text: data.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, aplicar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('confirmarAplicarAsistencia');
                        }
                    });
                });
            });
        </script>
    @endpush
</div>
