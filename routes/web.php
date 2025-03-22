<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\CampoFormativo\Index as CampoFormativoIndex;
use App\Livewire\CampoFormativo\Form as CampoFormativoForm;
use App\Livewire\Alumno\Index as AlumnoIndex;
use App\Livewire\Alumno\Form as AlumnoForm;
use App\Livewire\Evaluacion\Form as EvaluacionForm;
use App\Livewire\Evaluacion\Index as EvaluacionIndex;
use App\Livewire\Grupo\Index as GrupoIndex;
use App\Livewire\Grupo\Form as GrupoForm;
use App\Livewire\Usuario\Index as UsuarioIndex;
use App\Livewire\Asistencia\Index as AsistenciaIndex;
use App\Livewire\Asistencia\Configuracion as AsistenciaConfiguracion;
use App\Livewire\Asistencia\PasarLista as AsistenciaPasarLista;
use App\Livewire\Asistencia\Reporte as AsistenciaReporte;
use App\Livewire\Asistencia\AsistenciaMensual;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluacionController;
use App\Livewire\Dashboard;

Route::view('/', 'welcome');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    // Rutas de administración de usuarios (solo para administradores)
    Route::get('/usuarios', UsuarioIndex::class)->name('usuarios.index');

    // Rutas protegidas por el middleware 'owner' para verificar la propiedad
    Route::middleware([\App\Http\Middleware\CheckResourceOwnership::class])->group(function () {
        // Rutas de CampoFormativo
        Route::get('/campos-formativos', CampoFormativoIndex::class)->name('campos-formativos.index');
        Route::get('/campos-formativos/create', CampoFormativoForm::class)->name('campos-formativos.create');
        Route::get('/campos-formativos/{campoFormativoId}/edit', CampoFormativoForm::class)->name('campos-formativos.edit');

        // Rutas de Alumno
        Route::get('/alumnos', AlumnoIndex::class)->name('alumnos.index');
        Route::get('/alumnos/create', AlumnoForm::class)->name('alumnos.create');
        Route::get('/alumnos/{alumnoId}/edit', AlumnoForm::class)->name('alumnos.edit');

        // Rutas de Evaluacion
        Route::get('/evaluaciones', EvaluacionIndex::class)->name('evaluaciones.index');
        Route::get('/evaluaciones/create', EvaluacionForm::class)->name('evaluaciones.create');
        Route::get('/evaluaciones/{evaluacionId}/edit', EvaluacionForm::class)->name('evaluaciones.edit');
        Route::get('/evaluaciones/{evaluacionId}/show', \App\Livewire\Evaluacion\Show::class)->name('evaluaciones.show');
        Route::get('/evaluaciones/{evaluacionId}/excel', [EvaluacionController::class, 'exportarExcel'])->name('evaluaciones.excel');

        // Rutas de Grupo
        Route::get('/grupos', GrupoIndex::class)->name('grupos.index');
        Route::get('/grupos/create', GrupoForm::class)->name('grupos.create');
        Route::get('/grupos/{grupoId}/edit', GrupoForm::class)->name('grupos.edit');

        // Rutas de Asistencia
        Route::get('/asistencia', AsistenciaIndex::class)->name('asistencia.index');
        Route::get('/asistencia/configuracion', AsistenciaConfiguracion::class)->name('asistencia.configuracion');
        Route::get('/asistencia/pasar-lista', AsistenciaPasarLista::class)->name('asistencia.pasar-lista');
        Route::get('/asistencia/reporte', AsistenciaReporte::class)->name('asistencia.reporte');
        Route::get('/asistencia/mensual', AsistenciaMensual::class)->name('asistencia.mensual');

        // Rutas de Ciclos y Momentos
        Route::get('/ciclos', \App\Livewire\Ciclos\Index::class)->name('ciclos.index');
        Route::get('/momentos', \App\Livewire\Momentos\Index::class)->name('momentos.index');
    });
});

require __DIR__.'/auth.php';
