<?php

namespace App\Livewire\Asistencia;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\ConfiguracionAsistencia;
use App\Models\Grupo;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class PasarLista extends Component
{
    use WithPagination;

    public $fecha;
    public $grupo_id;
    public $search = '';
    public $asistencias = [];
    public $asistenciasOriginales = [];
    public $configuracionMes;
    public $hayCambiosPendientes = false;
    public $alumnosConFalta = [];
    public $mostrarConfirmacionSMS = false;

    protected $rules = [
        'asistencias.*.estado' => 'required|in:asistio,falta,justificada',
        'asistencias.*.observaciones' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'asistencias.*.estado.required' => 'El estado de asistencia es obligatorio',
        'asistencias.*.estado.in' => 'El estado debe ser: asistió, falta o justificada',
        'asistencias.*.observaciones.max' => 'Las observaciones no deben exceder los 255 caracteres',
    ];

    public function mount()
    {
        $this->fecha = Carbon::now()->format('Y-m-d');
        $this->cargarConfiguracionMes();
    }

    public function updatedFecha()
    {
        $this->resetPage();
        $this->cargarAsistencias();
        $this->cargarConfiguracionMes();
    }

    public function updatedGrupoId()
    {
        $this->resetPage();
        $this->cargarAsistencias();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function cargarConfiguracionMes()
    {
        $fecha = Carbon::parse($this->fecha);
        $this->configuracionMes = ConfiguracionAsistencia::where('user_id', Auth::id())
            ->where('mes', $fecha->month)
            ->where('anio', $fecha->year)
            ->first();
    }

    public function cargarAsistencias()
    {
        $this->hayCambiosPendientes = false;
        $alumnos = $this->obtenerAlumnos();

        $this->asistencias = [];
        $fecha = $this->fecha;

        foreach ($alumnos as $alumno) {
            // Consultamos el registro de asistencia de forma explícita
            $asistencia = Asistencia::where('alumno_id', $alumno->id)
                ->where('fecha', $fecha)
                ->where('user_id', Auth::id())
                ->first();

            if (!$asistencia) {
                $this->asistencias[$alumno->id] = [
                    'alumno_id' => $alumno->id,
                    'nombre_completo' => $alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombre,
                    'estado' => 'asistio',
                    'observaciones' => '',
                    'existe' => false
                ];
            } else {
                // Corregir el problema usando directamente los valores de la base de datos
                // en lugar de usar el getter/accessor
                $estadoDirecto = $asistencia->getAttribute('estado');

                $this->asistencias[$alumno->id] = [
                    'id' => $asistencia->id,
                    'alumno_id' => $alumno->id,
                    'nombre_completo' => $alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombre,
                    'estado' => $estadoDirecto, // Usar el valor directo del campo
                    'observaciones' => $asistencia->observaciones ?? '',
                    'existe' => true
                ];
            }
        }

        // Guardar una copia de las asistencias originales para comparación
        $this->asistenciasOriginales = json_encode($this->asistencias);
    }

    public function marcarTodos($estado)
    {
        foreach ($this->asistencias as $alumno_id => $asistencia) {
            $this->asistencias[$alumno_id]['estado'] = $estado;
        }
        $this->verificarCambios();
    }

    public function cambiarEstado($alumno_id, $estado)
    {
        $this->asistencias[$alumno_id]['estado'] = $estado;
        $this->verificarCambios();
    }

    public function verificarCambios()
    {
        $this->hayCambiosPendientes = json_encode($this->asistencias) !== $this->asistenciasOriginales;
    }

    public function guardarAsistencias()
    {
        $this->validate();

        $fecha = $this->fecha;
        $user_id = Auth::id();
        $alumnosModificados = [];
        $this->alumnosConFalta = []; // Reiniciar la lista de alumnos con falta

        foreach ($this->asistencias as $alumno_id => $asistencia) {
            // Determinar el valor de 'asistio' basado en el estado
            $asistioValue = ($asistencia['estado'] === 'asistio') ? 1 : 0;
            $estado = $asistencia['estado']; // Guardamos explícitamente el valor de estado para descartar problemas de referencia

            // Para la columna justificacion
            $justificacion = null;
            if ($asistencia['estado'] === 'justificada') {
                $justificacion = $asistencia['observaciones'] ?: 'Justificada';
            }

            // Almacenamos los IDs que modificamos para verificarlos después
            $alumnosModificados[] = $alumno_id;

            if ($asistencia['existe']) {
                // Actualizar registro existente de manera directa mediante query builder para evitar cualquier problema con el modelo
                DB::table('asistencias')
                    ->where('id', $asistencia['id'])
                    ->update([
                        'estado' => $estado,
                        'asistio' => $asistioValue,
                        'justificacion' => $justificacion,
                        'observaciones' => $asistencia['observaciones'],
                        'updated_at' => now()
                    ]);
            } else {
                // Crear nuevo registro directamente
                DB::table('asistencias')->insert([
                    'alumno_id' => $alumno_id,
                    'user_id' => $user_id,
                    'fecha' => $fecha,
                    'estado' => $estado,
                    'asistio' => $asistioValue,
                    'justificacion' => $justificacion,
                    'observaciones' => $asistencia['observaciones'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Si el alumno tiene falta, agregarlo a la lista de alumnos con falta
            if ($estado === 'falta') {
                // Obtener el alumno para tener sus datos
                $alumno = Alumno::find($alumno_id);
                if ($alumno) {
                    Log::info("Procesando alumno con falta: {$alumno->nombre_completo}");

                    // Verificar si tiene teléfono del tutor
                    if (!empty($alumno->telefono_tutor)) {
                        Log::info("Alumno {$alumno->nombre_completo} tiene teléfono de tutor: {$alumno->telefono_tutor}");
                        $this->alumnosConFalta[] = [
                            'id' => $alumno->id,
                            'nombre' => $alumno->nombre_completo,
                            'telefono_tutor' => $alumno->telefono_tutor
                        ];
                    } else {
                        Log::warning("Alumno {$alumno->nombre_completo} NO tiene teléfono de tutor registrado");
                    }
                }
            }
        }

        // Forzamos una recarga completa de los datos desde la base de datos
        $this->cargarAsistencias();

        // Notificar que se guardaron las asistencias
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Asistencias guardadas correctamente'
        ]);

        // Si hay alumnos con falta, mostrar diálogo de confirmación para enviar SMS
        if (count($this->alumnosConFalta) > 0) {
            Log::info("Hay " . count($this->alumnosConFalta) . " alumnos con falta. Enviando evento de confirmación SMS");
            $this->mostrarConfirmacionSMS = true;

            // Enviar el array directamente para que JavaScript lo pueda procesar correctamente
            $this->js('
                console.log("Ejecutando evento SMS desde PHP");

                // Datos de alumnos con falta para enviar SMS
                const alumnosConFalta = '.json_encode($this->alumnosConFalta).';

                // Crear lista de alumnos
                let listaAlumnos = "";
                alumnosConFalta.forEach(alumno => {
                    listaAlumnos += `<li>${alumno.nombre}</li>`;
                });

                // Mostrar diálogo de confirmación
                Swal.fire({
                    title: "Notificar Faltas por SMS",
                    html: `
                        <p>Los siguientes alumnos han faltado:</p>
                        <ul class="text-left">${listaAlumnos}</ul>
                        <p>¿Deseas enviar un SMS a sus padres/tutores?</p>
                    `,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Sí, enviar SMS",
                    cancelButtonText: "No, gracias",
                    confirmButtonColor: "#4f46e5",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar aviso de servicio contratado
                        Swal.fire({
                            title: "Servicio de Notificaciones",
                            html: `
                                <p class="text-warning font-bold">Esta función está disponible solo para usuarios que tengan contratado el servicio de notificaciones.</p>
                                <p class="mt-3">Para contratar este servicio, contacta con nosotros:</p>
                                <a href="https://wa.me/9616085491" target="_blank" class="btn btn-success mt-2">
                                    <i class="fab fa-whatsapp mr-1"></i> Contactar por WhatsApp
                                </a>
                            `,
                            icon: "warning",
                            showCancelButton: false,
                            confirmButtonText: "Entendido",
                            confirmButtonColor: "#4f46e5",
                        }).then((segundoResultado) => {
                            Toast.fire({
                                icon: "info",
                                title: "Envío de SMS cancelado"
                            });
                        });
                    } else {
                        Toast.fire({
                            icon: "info",
                            title: "Envío de SMS cancelado"
                        });
                    }
                });
            ');

            Log::info("Código JavaScript para mostrar confirmación SMS generado con " . count($this->alumnosConFalta) . " alumnos");
        } else {
            Log::info("No hay alumnos con falta para enviar SMS");
        }
    }

    /**
     * Enviar SMS a los tutores de los alumnos con falta
     */
    #[On('confirmarEnvioSMS')]
    public function enviarSMS()
    {
        try {
            Log::info("Método enviarSMS iniciado");

            // Verificar si el usuario tiene el servicio de SMS contratado
            $user = Auth::user();
            if (!$user->hasSmsService()) {
                Log::warning("Usuario no tiene contratado el servicio de SMS");
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No tienes contratado el servicio de notificaciones SMS'
                ]);

                // Mostrar mensaje de contratación de servicio
                $this->js('
                    Swal.fire({
                        title: "Servicio no contratado",
                        html: `
                            <p class="text-warning font-bold">No tienes contratado el servicio de notificaciones SMS.</p>
                            <p class="mt-3">Para contratar este servicio y poder enviar notificaciones a los padres/tutores, contacta con nosotros:</p>
                            <a href="https://wa.me/9616085491" target="_blank" class="btn btn-success mt-2">
                                <i class="fab fa-whatsapp mr-1"></i> Contactar por WhatsApp
                            </a>
                        `,
                        icon: "warning",
                        confirmButtonText: "Entendido",
                        confirmButtonColor: "#4f46e5",
                    });
                ');
                return;
            }

            $twilioService = app(TwilioService::class);

            if (!$twilioService->isConfigured()) {
                Log::error("Servicio Twilio no configurado correctamente");
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'El servicio de SMS no está configurado correctamente'
                ]);
                return;
            }

            Log::info("Servicio Twilio configurado correctamente");

            if (empty($this->alumnosConFalta)) {
                Log::warning("No hay alumnos con falta para enviar SMS en el momento de la ejecución");
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No hay alumnos con faltas para enviar SMS'
                ]);
                return;
            }

            Log::info("Procesando " . count($this->alumnosConFalta) . " alumnos con faltas para SMS");

            $mensajesEnviados = 0;
            $errores = 0;
            $fecha = Carbon::parse($this->fecha)->format('d/m/Y');
            $detallesMensajes = [];

            foreach ($this->alumnosConFalta as $alumno) {
                if (empty($alumno['telefono_tutor'])) {
                    Log::warning("Alumno {$alumno['nombre']} no tiene teléfono de tutor registrado");
                    continue;
                }

                Log::info("Enviando SMS a tutor de {$alumno['nombre']} al número {$alumno['telefono_tutor']}");

                $mensaje = "NOTIFICACIÓN ESCOLAR: El alumno {$alumno['nombre']} no asistió a clases el día {$fecha}. Por favor contacte a la escuela para más información.";

                $resultado = $twilioService->sendSMS($alumno['telefono_tutor'], $mensaje);

                if ($resultado['success']) {
                    $mensajesEnviados++;
                    Log::info("SMS enviado correctamente a tutor de {$alumno['nombre']}");
                    $detallesMensajes[] = "✓ {$alumno['nombre']}";
                } else {
                    $errores++;
                    Log::error("Error al enviar SMS a tutor de {$alumno['nombre']}: " . $resultado['message']);
                    $detallesMensajes[] = "✗ {$alumno['nombre']} - Error: " . substr($resultado['message'], 0, 50) . "...";
                }
            }

            // Generar mensaje detallado
            $mensajeDetallado = "";
            if ($mensajesEnviados > 0) {
                $mensajeDetallado = "Se enviaron {$mensajesEnviados} mensajes correctamente. ";
            }
            if ($errores > 0) {
                $mensajeDetallado .= "Hubo {$errores} errores.";
            }

            // Mostrar mensaje de resultado
            if ($mensajesEnviados > 0) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => $mensajeDetallado
                ]);

                // Mostrar diálogo de resultados usando JavaScript directamente
                $this->js('
                    console.log("Mostrando resultados de envío SMS");

                    // Datos de resultados
                    const resultados = {
                        exito: '.$mensajesEnviados.',
                        errores: '.$errores.',
                        detalles: '.json_encode($detallesMensajes).'
                    };

                    // Determinar icono y mensaje basado en los resultados
                    let mensaje = "";
                    let icon = "info";

                    if (resultados.exito > 0 && resultados.errores === 0) {
                        mensaje = `<p>Todos los mensajes se enviaron correctamente:</p>`;
                        icon = "success";
                    } else if (resultados.exito > 0 && resultados.errores > 0) {
                        mensaje = `<p>Se enviaron ${resultados.exito} mensajes correctamente, pero hubo ${resultados.errores} errores:</p>`;
                        icon = "warning";
                    } else {
                        mensaje = `<p>No se pudo enviar ningún mensaje. Se encontraron ${resultados.errores} errores:</p>`;
                        icon = "error";
                    }

                    // Agregar detalles al mensaje
                    mensaje += \'<ul class="text-left mt-3" style="max-height: 300px; overflow-y: auto;">\';
                    resultados.detalles.forEach(detalle => {
                        mensaje += `<li class="mb-1">${detalle}</li>`;
                    });
                    mensaje += "</ul>";

                    // Mostrar modal con detalles
                    Swal.fire({
                        title: "Resultado del envío de SMS",
                        html: mensaje,
                        icon: icon,
                        confirmButtonText: "Entendido",
                        confirmButtonColor: "#4f46e5",
                    });
                ');

                Log::info("Código JavaScript para mostrar resultado de SMS generado: {$mensajesEnviados} exitosos, {$errores} errores");
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => "No se pudo enviar ningún mensaje. " . ($errores > 0 ? "Hubo {$errores} errores." : "")
                ]);
            }

            if ($errores > 0) {
                Log::warning("Hubo {$errores} errores al enviar SMS. Revise los logs para más detalles.");
            }

        } catch (\Exception $e) {
            Log::error('Error al enviar SMS: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al enviar SMS: ' . $e->getMessage()
            ]);
        }

        // Reiniciar variables
        $this->alumnosConFalta = [];
        $this->mostrarConfirmacionSMS = false;
    }

    public function obtenerAlumnos()
    {
        $query = Alumno::query()
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre');

        if ($this->grupo_id) {
            $query->where('grupo_id', $this->grupo_id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function esDiaHabil()
    {
        if (!$this->configuracionMes) {
            return false;
        }

        if ($this->configuracionMes->es_periodo_vacacional) {
            return false;
        }

        $fecha = Carbon::parse($this->fecha);
        $diaSemana = $fecha->dayOfWeek;

        // Si es sábado (6) o domingo (0), no es día hábil
        if ($diaSemana == 0 || $diaSemana == 6) {
            return false;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.asistencia.pasar-lista', [
            'grupos' => Grupo::orderBy('nombre')->get(),
            'esDiaHabil' => $this->esDiaHabil(),
            'configuracionExiste' => (bool) $this->configuracionMes,
        ]);
    }
}
