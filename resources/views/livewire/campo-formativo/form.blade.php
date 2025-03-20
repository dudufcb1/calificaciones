<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Mensajes de error -->
    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
            <div class="flex items-center">
                <svg class="h-6 w-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="font-bold">¡Error! </span>
                <span class="ml-1">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    {{ $editing ? 'Editar' : 'Crear' }} Campo Formativo
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Complete la información del campo formativo y sus criterios de evaluación.
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <form wire:submit.prevent="save">
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" wire:model="nombre" id="nombre"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea wire:model="descripcion" id="descripcion" rows="3"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-sm font-medium text-gray-700">Criterios de Evaluación</label>
                                <button type="button" wire:click.prevent="addCriterio"
                                        class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-sm">
                                    Agregar Criterio
                                </button>
                            </div>

                            <div x-data="{ sumaPorcentajes: 0 }"
                                 x-init="sumaPorcentajes = @js(collect($criterios)->sum('porcentaje'))">
                                <div class="space-y-4">
                                    @foreach($criterios as $index => $criterio)
                                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                                            <div class="flex-1">
                                                <input type="text" wire:model="criterios.{{ $index }}.nombre"
                                                       placeholder="Nombre del criterio"
                                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                                @error("criterios.{$index}.nombre")
                                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="w-32">
                                                <input type="number" wire:model.live="criterios.{{ $index }}.porcentaje"
                                                       placeholder="%" min="0" max="100" step="1"
                                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                       x-on:input="sumaPorcentajes = [...document.querySelectorAll('[wire\\:model\\.live*=porcentaje]')].reduce((sum, input) => sum + (Number(input.value) || 0), 0)"
                                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 @error('criterios.'.$index.'.porcentaje') border-red-500 @enderror">
                                                @error("criterios.{$index}.porcentaje")
                                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="flex-1">
                                                <input type="text" wire:model="criterios.{{ $index }}.descripcion"
                                                       placeholder="Descripción (opcional)"
                                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                            </div>

                                            <button type="button" wire:click="removeCriterio({{ $index }})"
                                                    class="text-red-600 hover:text-red-800">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Total:</span>
                                    <div class="flex items-center">
                                        <span x-text="sumaPorcentajes + '%'"
                                              :class="{'text-red-600': sumaPorcentajes !== 100, 'text-green-600': sumaPorcentajes === 100}"
                                              class="font-bold mr-3"></span>

                                        <button type="button" wire:click="ajustarPorcentajes"
                                                x-show="sumaPorcentajes !== 100"
                                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Ajustar a 100%
                                        </button>
                                    </div>
                                </div>

                                <!-- Alerta en tiempo real del total -->
                                <div x-show="sumaPorcentajes > 100" class="mt-2 bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-medium">Error:</span>
                                        <span class="ml-1">La suma de los porcentajes no puede superar el 100%</span>
                                    </div>
                                </div>

                                <div x-show="sumaPorcentajes < 100" class="mt-2 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-medium">Atención:</span>
                                        <span class="ml-1">La suma debe ser exactamente 100% (actual: <span x-text="sumaPorcentajes + '%'"></span>)</span>
                                    </div>
                                </div>

                                @error('criterios')
                                    <span class="block mt-2 text-red-500 text-sm font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 space-x-3">
                        <a href="{{ route('campos-formativos.index') }}"
                           class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $editing ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="fixed bottom-0 right-0 m-6 p-4 bg-green-500 text-white rounded-lg shadow-lg">
            {{ session('message') }}
        </div>
    @endif
</div>
