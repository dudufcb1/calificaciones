<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                            Reportes de Asistencia
                        </h1>
                        <a href="{{ route('asistencia.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700">
                            Volver
                        </a>
                    </div>

                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Genera reportes de asistencia por alumno, grupo o periodo. Selecciona los filtros para visualizar las estadísticas.
                    </p>
                </div>

                <div class="p-6">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Este componente está pendiente de implementación. Aquí podrás generar reportes y visualizar las estadísticas de asistencia.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="tipo-reporte" class="block text-sm font-medium text-gray-700">Tipo de Reporte</label>
                            <select id="tipo-reporte" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="alumno">Por Alumno</option>
                                <option value="grupo">Por Grupo</option>
                                <option value="mes">Por Mes</option>
                                <option value="periodo">Por Periodo</option>
                            </select>
                        </div>

                        <div>
                            <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo</label>
                            <select id="grupo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Todos los grupos</option>
                                <option value="1">1° A</option>
                                <option value="2">1° B</option>
                                <option value="3">2° A</option>
                                <option value="4">2° B</option>
                            </select>
                        </div>

                        <div>
                            <label for="mes" class="block text-sm font-medium text-gray-700">Mes</label>
                            <select id="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecciona un mes</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>

                        <div>
                            <label for="anio" class="block text-sm font-medium text-gray-700">Año</label>
                            <select id="anio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="2023">2023</option>
                                <option value="2024" selected>2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="alumno" class="block text-sm font-medium text-gray-700">Buscar alumno</label>
                        <input type="text" id="alumno" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Nombre del alumno">
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700">
                            Generar Reporte
                        </button>
                    </div>

                    <div class="mt-10">
                        <h2 class="text-xl font-medium text-gray-900 dark:text-white mb-4">Resultados del reporte</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="px-4 py-5 sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de días hábiles</dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">20</dd>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="px-4 py-5 sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de asistencias</dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">18</dd>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="px-4 py-5 sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de ausencias</dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">2</dd>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="px-4 py-5 sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">Porcentaje de asistencia</dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">90%</dd>
                                </div>
                            </div>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Alumno</th>
                                        <th scope="col" class="px-6 py-3">Grupo</th>
                                        <th scope="col" class="px-6 py-3">Días hábiles</th>
                                        <th scope="col" class="px-6 py-3">Asistencias</th>
                                        <th scope="col" class="px-6 py-3">Ausencias</th>
                                        <th scope="col" class="px-6 py-3">% Asistencia</th>
                                        <th scope="col" class="px-6 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= 3; $i++)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            Alumno Ejemplo {{ $i }}
                                        </th>
                                        <td class="px-6 py-4">1° A</td>
                                        <td class="px-6 py-4">20</td>
                                        <td class="px-6 py-4">{{ 20 - $i }}</td>
                                        <td class="px-6 py-4">{{ $i }}</td>
                                        <td class="px-6 py-4">{{ (20 - $i) * 5 }}%</td>
                                        <td class="px-6 py-4">
                                            <button class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Detalles</button>
                                        </td>
                                    </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
