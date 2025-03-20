<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckResourceOwnership
{
    /**
     * Modelos que deben verificarse para la propiedad.
     */
    protected $models = [
        'alumno' => \App\Models\Alumno::class,
        'grupo' => \App\Models\Grupo::class,
        'campo-formativo' => \App\Models\CampoFormativo::class,
        'criterio' => \App\Models\Criterio::class,
        'evaluacion' => \App\Models\Evaluacion::class,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si no hay usuario autenticado, redirigir al login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Obtener la ruta actual y sus parámetros
        $routeName = $request->route()->getName();
        $routeParts = explode('.', $routeName);

        // Solo verificar si es una ruta de edición, eliminación o visualización de un recurso específico
        if (count($routeParts) >= 2 && in_array($routeParts[1], ['edit', 'update', 'destroy', 'show'])) {
            $resourceType = $routeParts[0];

            // Verificar si es un tipo de recurso que debe ser verificado
            if (array_key_exists($resourceType, $this->models)) {
                $modelClass = $this->models[$resourceType];
                $resourceId = $request->route()->parameter($resourceType.'Id');

                if ($resourceId) {
                    $resource = $modelClass::find($resourceId);

                    // Si el recurso existe y no pertenece al usuario actual, redirigir con error
                    if ($resource && $resource->user_id !== Auth::id()) {
                        return redirect()->route($resourceType.'.index')
                            ->with('error', 'No tienes permiso para acceder a este recurso.');
                    }
                }
            }
        }

        return $next($request);
    }
}
