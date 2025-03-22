<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    {{ $editing ? 'Editar' : 'Crear' }} Alumno
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Ingrese los datos del alumno.
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <form wire:submit.prevent="save">
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-2">
                                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre *</label>
                                <input type="text" wire:model="nombre" id="nombre"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">Apellido Paterno *</label>
                                <input type="text" wire:model="apellido_paterno" id="apellido_paterno"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('apellido_paterno') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label for="apellido_materno" class="block text-sm font-medium text-gray-700">Apellido Materno *</label>
                                <input type="text" wire:model="apellido_materno" id="apellido_materno"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('apellido_materno') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Información Personal del Alumno -->
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Información Personal</h3>

                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-2">
                                <label for="curp" class="block text-sm font-medium text-gray-700">CURP</label>
                                <input type="text" wire:model="curp" id="curp" maxlength="18"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('curp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                                <input type="date" wire:model="fecha_nacimiento" id="fecha_nacimiento"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('fecha_nacimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label for="genero" class="block text-sm font-medium text-gray-700">Género</label>
                                <select wire:model="genero" id="genero"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Seleccionar</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                    <option value="otro">Otro</option>
                                </select>
                                @error('genero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6">
                                <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                                <textarea wire:model="direccion" id="direccion" rows="2"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                @error('direccion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label for="telefono_emergencia" class="block text-sm font-medium text-gray-700">Teléfono de Emergencia</label>
                                <input type="text" wire:model="telefono_emergencia" id="telefono_emergencia"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('telefono_emergencia') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <label for="alergias" class="block text-sm font-medium text-gray-700">Alergias o Condiciones Médicas</label>
                                <textarea wire:model="alergias" id="alergias" rows="2"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                @error('alergias') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Información del Tutor -->
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Información del Tutor</h3>

                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-2">
                                <label for="tutor_nombre" class="block text-sm font-medium text-gray-700">Nombre del Tutor</label>
                                <input type="text" wire:model="tutor_nombre" id="tutor_nombre"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('tutor_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label for="telefono_tutor" class="block text-sm font-medium text-gray-700">Teléfono del Tutor</label>
                                <input type="text" wire:model="telefono_tutor" id="telefono_tutor"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <span class="text-xs text-gray-500">Este número se utilizará para enviar notificaciones SMS cuando el alumno falte a clases.</span>
                                @error('telefono_tutor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-2">
                                <label for="tutor_email" class="block text-sm font-medium text-gray-700">Email del Tutor</label>
                                <input type="email" wire:model="tutor_email" id="tutor_email"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('tutor_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Grupo y Estado -->
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Información Académica</h3>

                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="grupo_id" class="block text-sm font-medium text-gray-700">Grupo</label>
                                <select wire:model="grupo_id" id="grupo_id"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Seleccione un grupo</option>
                                    @foreach($grupos as $grupo)
                                        <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('grupo_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select wire:model="estado" id="estado"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                                @error('estado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea wire:model="observaciones" id="observaciones" rows="3"
                                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            @error('observaciones') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 space-x-3">
                        <a href="{{ route('alumnos.index') }}"
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
</div>
