<?php

namespace App\Livewire\Asistencia;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\ConfiguracionAsistencia;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class Configuracion extends Component
{
    public $anioActual;
    public $configuraciones = [];
    public $editando = false;
    public $configuracionId = null;
    public $mes;
    public $anio;
    public $diasHabiles;
    public $esPeriodoVacacional = false;

    protected $rules = [
        'mes' => 'required|integer|min:1|max:12',
        'anio' => 'required|integer|min:2020|max:2030',
        'diasHabiles' => 'required|integer|min:0|max:31',
        'esPeriodoVacacional' => 'boolean',
    ];

    public function mount()
    {
        $this->anioActual = date('Y');
        $this->anio = $this->anioActual;
        $this->cargarConfiguraciones();
    }

    public function cargarConfiguraciones()
    {
        // Obtenemos las configuraciones para el año seleccionado
        $configuracionesDB = ConfiguracionAsistencia::where('user_id', Auth::id())
            ->where('anio', $this->anio)
            ->orderBy('mes')
            ->get();

        // Preparamos array para todos los meses
        $this->configuraciones = [];
        for ($i = 1; $i <= 12; $i++) {
            $this->configuraciones[$i] = [
                'id' => null,
                'mes' => $i,
                'nombre_mes' => $this->obtenerNombreMes($i),
                'dias_habiles' => 0,
                'es_periodo_vacacional' => false,
            ];
        }

        // Llenamos con datos de la base de datos
        foreach ($configuracionesDB as $config) {
            $this->configuraciones[$config->mes] = [
                'id' => $config->id,
                'mes' => $config->mes,
                'nombre_mes' => $this->obtenerNombreMes($config->mes),
                'dias_habiles' => $config->dias_habiles,
                'es_periodo_vacacional' => $config->es_periodo_vacacional,
            ];
        }
    }

    public function obtenerNombreMes($mes)
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        return $meses[$mes] ?? 'Desconocido';
    }

    public function editarConfiguracion($mesNum)
    {
        $this->reset(['configuracionId', 'mes', 'diasHabiles', 'esPeriodoVacacional']);

        $this->mes = $mesNum;
        $config = $this->configuraciones[$mesNum];

        if ($config['id']) {
            $this->configuracionId = $config['id'];
            $this->diasHabiles = $config['dias_habiles'];
            $this->esPeriodoVacacional = $config['es_periodo_vacacional'];
        } else {
            $this->diasHabiles = $this->obtenerDiasHabilesDefault($mesNum);
            $this->esPeriodoVacacional = false;
        }

        $this->editando = true;
    }

    public function obtenerDiasHabilesDefault($mes)
    {
        // Meses con 30 días: Abril, Junio, Septiembre, Noviembre
        // Meses con 31 días: Enero, Marzo, Mayo, Julio, Agosto, Octubre, Diciembre
        // Febrero: 28 o 29 días dependiendo del año bisiesto

        $diasPorMes = [
            1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
            7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31
        ];

        // Ajustar febrero para años bisiestos
        if ($this->anio % 4 == 0 && ($this->anio % 100 != 0 || $this->anio % 400 == 0)) {
            $diasPorMes[2] = 29;
        }

        // Por defecto, asumimos que hay 5 fines de semana (10 días no hábiles)
        $diasNoHabiles = 10;

        return $diasPorMes[$mes] - $diasNoHabiles;
    }

    public function cancelarEdicion()
    {
        $this->editando = false;
    }

    public function guardarConfiguracion()
    {
        $this->validate();

        if ($this->esPeriodoVacacional) {
            $this->diasHabiles = 0;
        }

        if ($this->configuracionId) {
            // Actualizar configuración existente
            ConfiguracionAsistencia::where('id', $this->configuracionId)
                ->update([
                    'dias_habiles' => $this->diasHabiles,
                    'es_periodo_vacacional' => $this->esPeriodoVacacional
                ]);

            $mensaje = 'Configuración actualizada correctamente';
        } else {
            // Crear nueva configuración
            ConfiguracionAsistencia::create([
                'user_id' => Auth::id(),
                'mes' => $this->mes,
                'anio' => $this->anio,
                'dias_habiles' => $this->diasHabiles,
                'es_periodo_vacacional' => $this->esPeriodoVacacional
            ]);

            $mensaje = 'Configuración creada correctamente';
        }

        $this->editando = false;
        $this->cargarConfiguraciones();

        $this->dispatch('notify', [
            'message' => $mensaje,
            'type' => 'success'
        ]);
    }

    public function cambiarAnio($nuevoAnio)
    {
        $this->anio = $nuevoAnio;
        $this->cargarConfiguraciones();
    }

    public function render()
    {
        return view('livewire.asistencia.configuracion');
    }
}
