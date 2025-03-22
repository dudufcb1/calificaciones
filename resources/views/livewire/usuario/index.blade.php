<div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Gestión de Usuarios</h2>
        </div>

        <div class="mb-4">
            <input wire:model.live="search" type="text" placeholder="Buscar usuarios..."
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div class="bg-white shadow-md rounded my-6">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Nombre</th>
                        <th class="py-3 px-6 text-left">Teléfono</th>
                        <th class="py-3 px-6 text-center">Estado</th>
                        <th class="py-3 px-6 text-center">Rol</th>
                        <th class="py-3 px-6 text-center">Confirmado</th>
                        <th class="py-3 px-6 text-center">Trial</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @foreach($usuarios as $usuario)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left">{{ $usuario->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $usuario->phone_number ?? 'No registrado' }}</td>
                            <td class="py-3 px-6 text-center">
                                @if($usuario->status === 'active')
                                    <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Activo</span>
                                @elseif($usuario->status === 'pending')
                                    <span class="bg-yellow-100 text-yellow-800 py-1 px-3 rounded-full text-xs">Pendiente</span>
                                @else
                                    <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Inactivo</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($usuario->role === 'admin')
                                    <span class="bg-purple-100 text-purple-800 py-1 px-3 rounded-full text-xs">Administrador</span>
                                @else
                                    <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs">Usuario</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($usuario->is_confirmed)
                                    <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Sí</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs">No</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($usuario->trial)
                                    <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs">Sí</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs">No</span>
                                @endif
                                <button wire:click="toggleTrial({{ $usuario->id }})"
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-2 rounded text-xs ml-1">
                                    {{ $usuario->trial ? 'Desactivar' : 'Activar' }}
                                </button>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center space-x-2">
                                    <button wire:click="editUserModal({{ $usuario->id }})"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                        Editar
                                    </button>

                                    @if(!$usuario->is_confirmed)
                                        <button wire:click="confirmUserModal({{ $usuario->id }})"
                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs">
                                            Confirmar
                                        </button>
                                    @endif

                                    @if($usuario->status === 'active')
                                        <button wire:click="deactivateUserModal({{ $usuario->id }})"
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-xs">
                                            Desactivar
                                        </button>
                                    @elseif($usuario->status === 'inactive')
                                        <button wire:click="activateUser({{ $usuario->id }})"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                            Activar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>

        <!-- Modal de confirmación de usuario -->
        @if($showConfirmModal)
            <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Confirmar usuario
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            ¿Estás seguro de que deseas confirmar a este usuario? Esta acción permitirá al usuario acceder al sistema.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button wire:click="confirmUserAction" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Confirmar
                            </button>
                            <button wire:click="cancelConfirm" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal de desactivación de usuario -->
        @if($showDeactivateModal)
            <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Desactivar usuario
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Por favor, indique el motivo de la desactivación. Este mensaje se mostrará al usuario cuando intente iniciar sesión.
                                        </p>
                                        <textarea
                                            wire:model="deactivationReason"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            rows="3"
                                            placeholder="Motivo de la desactivación"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button wire:click="deactivateUserAction" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Desactivar
                            </button>
                            <button wire:click="cancelDeactivate" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('message'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3000)"
                 class="fixed bottom-0 right-0 m-6 p-4 bg-green-500 text-white rounded-lg shadow-lg">
                {{ session('message') }}
            </div>
        @endif

        <!-- Modal de edición de usuario -->
        @if($showEditModal)
            <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Editar usuario
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                                            <input type="text"
                                                id="name"
                                                wire:model.blur="userData.name"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            @error('userData.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                            <input type="email"
                                                id="email"
                                                wire:model.blur="userData.email"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            @error('userData.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="phone_number" class="block text-sm font-medium text-gray-700">Teléfono</label>
                                            <input type="text"
                                                id="phone_number"
                                                wire:model.blur="userData.phone_number"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            @error('userData.phone_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                                            <select id="status" wire:model.blur="userData.status"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="active">Activo</option>
                                                <option value="pending">Pendiente</option>
                                                <option value="inactive">Inactivo</option>
                                            </select>
                                            @error('userData.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                                            <select id="role" wire:model.blur="userData.role"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="user">Usuario</option>
                                                <option value="admin">Administrador</option>
                                            </select>
                                            @error('userData.role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox"
                                                id="trial"
                                                wire:model.blur="userData.trial"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <label for="trial" class="ml-2 block text-sm font-medium text-gray-700">
                                                Usuario en modo Trial
                                            </label>
                                        </div>

                                        <div class="mt-6">
                                            <h4 class="font-medium text-gray-900 mb-2">Servicios contratados</h4>
                                            <div class="space-y-2 border rounded-md p-3 bg-gray-50">
                                                <div class="flex items-center">
                                                    <input type="checkbox"
                                                        id="sms_notifications"
                                                        wire:model.blur="userData.benefits.sms_notifications"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <label for="sms_notifications" class="ml-2 block text-sm font-medium text-gray-700">
                                                        Servicio de notificaciones SMS
                                                    </label>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Permite enviar SMS automáticos a padres/tutores cuando los alumnos faltan a clase.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button wire:click="updateUser" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Guardar cambios
                            </button>
                            <button wire:click="cancelEdit" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
