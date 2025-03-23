<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateAIAgentToken
{
    /**
     * Handle an incoming request.
     *
     * Este middleware valida que las solicitudes al endpoint de la API para el agente de IA
     * contengan un token válido en los headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-AI-Agent-Token');

        // Verificar que el token existe
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación no proporcionado',
            ], 401);
        }

        // Verificar que el token es válido
        // El token debe ser configurado en el archivo .env
        if ($token !== config('services.ai_agent.token')) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación inválido',
            ], 403);
        }

        return $next($request);
    }
}
