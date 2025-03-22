<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ $editing ? 'Editar Evaluación' : 'Evaluar Momento' }}
                    </h2>
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
                        @endif
                        <a href="{{ route('evaluaciones.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $editing ? 'Actualizar Evaluación' : 'Crear Evaluaciones' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
