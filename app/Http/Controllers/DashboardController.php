<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Alumno;
use App\Models\CampoFormativo;
use App\Models\Evaluacion;
use App\Models\Grupo;
use App\Models\Criterio;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        // Estadísticas básicas para todos los usuarios
        $stats = [
            'camposFormativos' => CampoFormativo::where('user_id', $user->id)->count(),
            'evaluaciones' => Evaluacion::where('user_id', $user->id)->count(),
            'alumnos' => Alumno::where('user_id', $user->id)->count(),
            'grupos' => Grupo::where('user_id', $user->id)->count(),
        ];

        // Datos de actividad reciente
        $actividadReciente = $this->getActividadReciente($user->id);

        // Enlaces rápidos para todos los usuarios
        $quickLinks = [
            [
                'route' => 'campos-formativos.index',
                'text' => 'Ver Campos Formativos',
                'icon' => 'academics',
                'color' => 'indigo'
            ],
            [
                'route' => 'evaluaciones.index',
                'text' => 'Gestionar Evaluaciones',
                'icon' => 'document',
                'color' => 'emerald'
            ],
            [
                'route' => 'alumnos.index',
                'text' => 'Ver Alumnos',
                'icon' => 'users',
                'color' => 'amber'
            ],
            [
                'route' => 'grupos.index',
                'text' => 'Gestionar Grupos',
                'icon' => 'group',
                'color' => 'blue'
            ],
            [
                'route' => 'ciclos.index',
                'text' => 'Ciclos Escolares',
                'icon' => 'academics',
                'color' => 'purple'
            ],
            [
                'route' => 'momentos.index',
                'text' => 'Momentos Educativos',
                'icon' => 'document',
                'color' => 'pink'
            ]
        ];

        // Datos específicos para administradores
        $adminStats = [];
        $usuariosPendientes = [];
        $ultimosUsuarios = [];

        if ($isAdmin) {
            $adminStats = [
                'totalUsuarios' => User::count(),
                'usuariosPendientes' => User::where('status', 'pending')->count(),
                'usuariosActivos' => User::where('status', 'active')->count(),
                'usuariosInactivos' => User::where('status', 'inactive')->count(),
                'totalEvaluaciones' => Evaluacion::count(),
                'totalAlumnos' => Alumno::count(),
                'totalGrupos' => Grupo::count(),
            ];

            // Usuarios pendientes de confirmación
            $usuariosPendientes = User::where('is_confirmed', false)
                ->orWhere('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Últimos usuarios registrados
            $ultimosUsuarios = User::orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Añadir enlace de gestión de usuarios para admin
            $quickLinks[] = [
                'route' => 'usuarios.index',
                'text' => 'Gestionar Usuarios',
                'icon' => 'admin',
                'color' => 'purple'
            ];
        }

        // Alumnos por grupo para gráficos
        $alumnosPorGrupo = DB::table('alumnos')
            ->select('grupos.nombre', DB::raw('count(*) as total'))
            ->join('grupos', 'alumnos.grupo_id', '=', 'grupos.id')
            ->where('alumnos.user_id', $user->id)
            ->groupBy('grupos.id', 'grupos.nombre')
            ->get();

        // Evaluaciones recientes
        $evaluacionesRecientes = Evaluacion::where('user_id', $user->id)
            ->with('campoFormativo')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('dashboard', compact(
            'user',
            'isAdmin',
            'stats',
            'adminStats',
            'actividadReciente',
            'quickLinks',
            'usuariosPendientes',
            'ultimosUsuarios',
            'alumnosPorGrupo',
            'evaluacionesRecientes'
        ));
    }

    private function getActividadReciente($userId)
    {
        $fechaLimite = Carbon::now()->subDays(7);

        // Nuevas evaluaciones
        $evaluaciones = Evaluacion::where('user_id', $userId)
            ->where('created_at', '>=', $fechaLimite)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => 'evaluacion',
                    'titulo' => "Nueva evaluación: {$item->titulo}",
                    'fecha' => $item->created_at,
                    'icono' => 'document-add'
                ];
            });

        // Nuevos alumnos
        $alumnos = Alumno::where('user_id', $userId)
            ->where('created_at', '>=', $fechaLimite)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => 'alumno',
                    'titulo' => "Nuevo alumno: {$item->nombre} {$item->apellido_paterno}",
                    'fecha' => $item->created_at,
                    'icono' => 'user-add'
                ];
            });

        // Nuevos campos formativos
        $campos = CampoFormativo::where('user_id', $userId)
            ->where('created_at', '>=', $fechaLimite)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => 'campo',
                    'titulo' => "Nuevo campo formativo: {$item->nombre}",
                    'fecha' => $item->created_at,
                    'icono' => 'academic-cap'
                ];
            });

        // Combinar y ordenar por fecha
        return $evaluaciones->concat($alumnos)
            ->concat($campos)
            ->sortByDesc('fecha')
            ->take(5)
            ->values()
            ->all();
    }
}
