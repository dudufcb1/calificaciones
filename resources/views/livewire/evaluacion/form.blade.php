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
                    <!-- Información general de la evaluación -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="col-span-2">
                            <label for="titulo" class="block text-sm font-medium text-gray-700">Título de la Evaluación</label>
                            <input type="text" wire:model="titulo" id="titulo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('titulo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea wire:model="descripcion" id="descripcion" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div>
                            <label for="fecha_evaluacion" class="block text-sm font-medium text-gray-700">Fecha de Evaluación</label>
                            <input type="date" wire:model="fecha_evaluacion" id="fecha_evaluacion"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="momento" class="block text-sm font-medium text-gray-700">Momento de Evaluación</label>
                            <select wire:model="momento" id="momento"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(\App\Enums\MomentoEvaluacion::cases() as $momentoCase)
                                    <option value="{{ $momentoCase->value }}">{{ $momentoCase->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="campoFormativoId" class="block text-sm font-medium text-gray-700">Campo Formativo</label>
                            <select wire:model.live="campoFormativoId" id="campoFormativoId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Seleccione un campo formativo</option>
                                @foreach($camposFormativos as $campo)
                                    <option value="{{ $campo->id }}">{{ $campo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('campoFormativoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Criterios de Evaluación -->
                    @if(count($criterios) > 0)
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Criterios de Evaluación</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Criterio
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Descripción
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Porcentaje
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($criterios as $criterio)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $criterio['nombre'] }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $criterio['descripcion'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $criterio['porcentaje'] }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Selección y Gestión de Alumnos -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Alumnos a Evaluar</h3>
                            <button type="button" wire:click="toggleSeleccionAlumnos"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Agregar Alumnos
                            </button>
                        </div>

                        <!-- Modal para seleccionar alumnos -->
                        @if($mostrarSeleccionAlumnos)
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                                <div class="bg-white rounded-lg overflow-hidden shadow-xl max-w-3xl w-full max-h-screen">
                                    <div class="p-6">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-lg font-medium text-gray-900">Seleccionar Alumnos</h3>
                                            <button type="button" wire:click="toggleSeleccionAlumnos" class="text-gray-500 hover:text-gray-700">
                                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mb-4">
                                            <label for="grupoId" class="block text-sm font-medium text-gray-700">Filtrar por Grupo</label>
                                            <div class="flex space-x-2 mt-1">
                                                <select wire:model.live="grupoId" id="grupoId"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">Seleccione un grupo</option>
                                                    @foreach($grupos as $grupo)
                                                        <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" wire:click="cargarAlumnosGrupo"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Cargar Todo
                                                </button>
                                            </div>
                                        </div>

                                        @if($grupoId)
                                            <div class="max-h-96 overflow-y-auto border rounded-md p-2">
                                                <div class="mb-2">
                                                    <label class="inline-flex items-center">
                                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                            wire:click="$set('alumnosSeleccionados', @js(array_fill_keys($alumnos->pluck('id')->toArray(), true)))">
                                                        <span class="ml-2 text-sm text-gray-700">Seleccionar todos</span>
                                                    </label>
                                                </div>
                                                <div class="divide-y divide-gray-200">
                                                    @forelse($alumnos as $alumno)
                                                        <div class="py-2">
                                                            <label class="inline-flex items-center">
                                                                <input type="checkbox" wire:model="alumnosSeleccionados.{{ $alumno->id }}"
                                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                                <span class="ml-2 text-sm text-gray-700">{{ $alumno->nombre_completo }}</span>
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <div class="py-2 text-center text-gray-500">
                                                            No hay alumnos disponibles en este grupo
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @else
                                            <div class="py-4 text-center text-gray-500">
                                                Seleccione un grupo para ver los alumnos
                                            </div>
                                        @endif

                                        <div class="mt-4 flex justify-end">
                                            <button type="button" wire:click="toggleSeleccionAlumnos"
                                                class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Cancelar
                                            </button>
                                            <button type="button" wire:click="agregarAlumnosSeleccionados"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Agregar Seleccionados
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Lista de alumnos a evaluar -->
                        @if(count($alumnosEvaluados) > 0)
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Tip:</strong> Si ingresa un valor decimal como 6.7, se convertirá automáticamente a 67. La validación completa se realizará al finalizar la evaluación.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Alumno
                                            </th>
                                            @foreach($criterios as $criterio)
                                                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ $criterio['nombre'] }}
                                                </th>
                                            @endforeach
                                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Promedio
                                            </th>
                                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($alumnosEvaluados as $index => $alumno)
                                            <tr x-data="{
                                                calificaciones: @js(collect($alumno['calificaciones'])->pluck('valor')->toArray()),
                                                criterios: @js(collect($criterios)->pluck('porcentaje')->toArray()),
                                                calcularPromedio() {
                                                    let sumaPonderada = 0;
                                                    let sumaPesos = 0;

                                                    for (let i = 0; i < this.calificaciones.length; i++) {
                                                        let valor = Number(this.calificaciones[i] || 0);
                                                        let porcentaje = Number(this.criterios[i] || 0);

                                                        let ponderada = (valor * porcentaje) / 100;
                                                        sumaPonderada += ponderada;
                                                        sumaPesos += porcentaje;
                                                    }

                                                    return sumaPesos > 0 ? (sumaPonderada).toFixed(2) : '0.00';
                                                },
                                                limpiarFila() {
                                                    // Limpiar todas las calificaciones
                                                    this.calificaciones = this.calificaciones.map(() => '');

                                                    // Actualizar los inputs en el DOM
                                                    setTimeout(() => {
                                                        const inputs = this.$el.querySelectorAll('input[type=text]');
                                                        inputs.forEach((input, i) => {
                                                            input.value = '';
                                                            input.dispatchEvent(new Event('input', { bubbles: true }));
                                                        });
                                                    }, 10);
                                                }
                                            }"
                                            x-init="$watch('calificaciones', () => {
                                                $el.querySelector('.promedio-display').textContent = calcularPromedio();
                                            })">
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $alumno['nombre'] }}
                                                </td>
                                                @foreach($alumno['calificaciones'] as $calIndex => $calificacion)
                                                    <td class="px-2 py-4 whitespace-nowrap text-sm">
                                                        <input type="text"
                                                            wire:model="alumnosEvaluados.{{ $index }}.calificaciones.{{ $calIndex }}.valor"
                                                            x-model="calificaciones[{{ $calIndex }}]"
                                                            x-on:input="
                                                                let value = $el.value.trim();
                                                                // Reemplazar cualquier caracter que no sea número o punto
                                                                value = value.replace(/[^0-9.]/g, '');

                                                                // Si hay un punto decimal, multiplicar por 10
                                                                if (value.includes('.')) {
                                                                    let parts = value.split('.');
                                                                    // Si hay más de un punto, solo considerar el primero
                                                                    if (parts.length > 1) {
                                                                        // Si el decimal es de una cifra (ej: 6.7), multiplicar por 10
                                                                        if (parts[1].length === 1) {
                                                                            value = parseInt(parts[0] + parts[1]);
                                                                        } else if (parts[1].length > 1) {
                                                                            // Si hay más de un decimal, tomar solo dos (ej: 6.75 -> 68)
                                                                            value = Math.round(parseFloat(parts[0] + '.' + parts[1]) * 10);
                                                                        }
                                                                    }
                                                                }

                                                                // Asegurar que sea un número
                                                                value = isNaN(value) ? 0 : value;

                                                                // Limitar entre 0 y 100
                                                                value = Math.max(0, Math.min(100, value));

                                                                // Actualizar el valor
                                                                $el.value = value;
                                                                calificaciones[{{ $calIndex }}] = value;

                                                                // Disparar el evento de cambio para que Livewire actualice el modelo
                                                                $dispatch('input', value);
                                                            "
                                                            class="block w-16 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error("alumnosEvaluados.{$index}.calificaciones.{$calIndex}.valor") border-red-500 @enderror">
                                                        @error("alumnosEvaluados.{$index}.calificaciones.{$calIndex}.valor")
                                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                                        @enderror
                                                    </td>
                                                @endforeach
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium">
                                                    <span class="promedio-display" x-text="calcularPromedio()">{{ number_format($alumno['promedio'], 2) }}</span>
                                                </td>
                                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <div class="flex space-x-2">
                                                        <button type="button"
                                                            x-on:click="limpiarFila()"
                                                            wire:click="limpiarCalificaciones({{ $index }})"
                                                            class="text-gray-600 hover:text-gray-900"
                                                            title="Borrar calificaciones">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" wire:click="eliminarAlumno({{ $index }})"
                                                            class="text-red-600 hover:text-red-900" title="Eliminar alumno">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            No hay alumnos agregados a la evaluación. Haga clic en "Agregar Alumnos" para seleccionar los alumnos a evaluar.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('evaluaciones.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button type="button" wire:click="finalizar"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Finalizar Evaluación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
