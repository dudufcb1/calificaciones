<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">
                        {{ $editing ? 'Editar' : 'Nueva' }} Evaluación
                    </h2>
                    <div class="text-sm text-gray-500">
                        {{ $autoSaveMessage }}
                    </div>
                </div>

                <form wire:submit.prevent="finalizar" class="space-y-6">
                    <!-- Campo Formativo -->
                    <div>
                        <label for="campoFormativoId" class="block text-sm font-medium text-gray-700">Campo Formativo</label>
                        <select wire:model.live="campoFormativoId" id="campoFormativoId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Seleccione un campo formativo</option>
                            @foreach($camposFormativos as $campo)
                                <option value="{{ $campo->id }}">{{ $campo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('campoFormativoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Búsqueda y Selección de Alumno -->
                    <div>
                        <label for="alumnoSearch" class="block text-sm font-medium text-gray-700">Alumno</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" wire:model.live="alumnoSearch" wire:keyup="buscarAlumno" id="alumnoSearch"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Buscar alumno...">
                            <button type="button" wire:click="toggleCreateAlumno"
                                class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Nuevo Alumno
                            </button>
                        </div>

                        @if($alumnoSearch && count($alumnosSugeridos) > 0)
                            <div class="relative mt-1">
                                <div class="absolute z-50 w-full bg-gray-50 border border-gray-300 rounded-md shadow-xl">
                                    <ul class="max-h-60 py-1 overflow-auto text-base divide-y divide-gray-200">
                                        @foreach($alumnosSugeridos as $alumno)
                                            <li class="cursor-pointer px-4 py-3 hover:bg-indigo-600 hover:text-white transition-colors duration-150 text-gray-900 bg-white"
                                                wire:click="seleccionarAlumno({{ $alumno->id }})">
                                                {{ $alumno->nombre_completo }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($nombreAlumno)
                            <div class="mt-2 text-sm text-gray-600">
                                Alumno seleccionado: {{ $nombreAlumno }}
                            </div>
                        @endif

                        @error('alumnoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Formulario de Nuevo Alumno -->
                    @if($showCreateAlumno)
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Nuevo Alumno</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                                    <input type="text" wire:model="nuevoAlumno.nombre" id="nombre"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nuevoAlumno.nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                                    <input type="text" wire:model="nuevoAlumno.apellido_paterno" id="apellido_paterno"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nuevoAlumno.apellido_paterno') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="apellido_materno" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
                                    <input type="text" wire:model="nuevoAlumno.apellido_materno" id="apellido_materno"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nuevoAlumno.apellido_materno') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="grupo_id" class="block text-sm font-medium text-gray-700">Grupo</label>
                                    <select wire:model="nuevoAlumno.grupo_id" id="grupo_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleccione un grupo</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end space-x-3">
                                <button type="button" wire:click="toggleCreateAlumno"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="crearAlumno"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Crear Alumno
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Criterios de Evaluación -->
                    @if(count($criterios) > 0)
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Criterios de Evaluación</h3>
                            <div class="space-y-4">
                                @foreach($criterios as $criterio)
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-700">
                                                {{ $criterio['nombre'] }} ({{ $criterio['porcentaje'] }}%)
                                            </label>
                                            <input type="number" step="1" min="1" max="100"
                                                wire:model.live="calificaciones.{{ $criterio['id'] }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('calificaciones.'.$criterio['id']) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            @if(isset($calificaciones[$criterio['id']]))
                                                {{ number_format($calificaciones[$criterio['id']] * ($criterio['porcentaje'] / 100), 2) }}
                                            @else
                                                0.00
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Promedio Final -->
                        <div class="mt-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Promedio Final</h3>
                                <div class="text-2xl font-bold text-indigo-600">
                                    {{ number_format($promedioFinal, 2) }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Botones de Acción -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('evaluaciones.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Finalizar Evaluación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
