<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Livewire\Evaluacion\Form;
use Carbon\Carbon;

class EvaluacionAsistenciaTest extends TestCase
{
    /** @test */
    public function obtener_porcentajes_asistencia_inicializa_fechas_automaticamente()
    {
        // Crear una instancia del componente Form
        $form = new Form();

        // Simular que tenemos alumnos evaluados (array vacío para evitar errores)
        $form->alumnosEvaluados = [];

        // Verificar que inicialmente las fechas están vacías
        $form->inicioMes = null;
        $form->finMes = null;

        // Llamar al método que debería inicializar las fechas automáticamente
        $form->obtenerPorcentajesAsistencia();

        // Verificar que las fechas se inicializaron con el mes actual
        $this->assertNotNull($form->inicioMes);
        $this->assertNotNull($form->finMes);

        // Verificar que las fechas tienen el formato correcto
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $form->inicioMes);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $form->finMes);

        // Verificar que las fechas corresponden al mes actual
        $fechaActual = Carbon::now();
        $this->assertEquals($fechaActual->startOfMonth()->format('Y-m-d'), $form->inicioMes);
        $this->assertEquals($fechaActual->endOfMonth()->format('Y-m-d'), $form->finMes);
    }

    /** @test */
    public function obtener_porcentajes_asistencia_no_sobrescribe_fechas_existentes()
    {
        // Crear una instancia del componente Form
        $form = new Form();

        // Simular que tenemos alumnos evaluados (array vacío para evitar errores)
        $form->alumnosEvaluados = [];

        // Establecer fechas específicas
        $form->inicioMes = '2023-10-01';
        $form->finMes = '2023-10-31';

        // Llamar al método
        $form->obtenerPorcentajesAsistencia();

        // Verificar que las fechas NO se cambiaron
        $this->assertEquals('2023-10-01', $form->inicioMes);
        $this->assertEquals('2023-10-31', $form->finMes);
    }
}
