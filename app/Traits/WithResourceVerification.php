<?php

namespace App\Traits;

trait WithResourceVerification
{
    /**
     * Determina el contexto actual basado en el nombre del componente
     *
     * @return string|null
     */
    public function getResourceContext(): ?string
    {
        $class = get_class($this);
        $parts = explode('\\', $class);
        $namespace = isset($parts[2]) ? $parts[2] : '';
        $componentName = end($parts);

        // Registrar información para depuración
        logger("Determinando contexto - Clase: {$class}, Namespace: {$namespace}, Componente: {$componentName}");

        // Si el namespace es uno de los contextos conocidos, usarlo directamente
        $namespaceMap = [
            'Alumno' => 'alumnos',
            'Grupo' => 'grupos',
            'CampoFormativo' => 'campos-formativos',
            'Ciclos' => 'ciclos',
            'Momentos' => 'momentos',
            'Evaluacion' => 'evaluaciones'
        ];

        if (isset($namespaceMap[$namespace])) {
            logger("Contexto determinado por namespace: {$namespaceMap[$namespace]}");
            return $namespaceMap[$namespace];
        }

        // Convertir Index a minúsculas y quitar sufijos comunes
        $context = strtolower(str_replace(['Index', 'Component', 'Page'], '', $componentName));

        // Manejar casos específicos
        switch ($context) {
            case 'campo':
                return 'campos-formativos';
            case 'alumno':
                return 'alumnos';
            case 'grupo':
                return 'grupos';
            case 'ciclo':
            case 'ciclos':
                return 'ciclos';
            case 'momento':
            case 'momentos':
                return 'momentos';
            case 'evaluacion':
                return 'evaluaciones';
            case 'dashboard':
                return 'dashboard';
            case 'test':
                return 'evaluaciones'; // Para nuestro componente de prueba
            default:
                // Intentar detectar por namespace
                if (strpos($class, 'Alumno') !== false) {
                    return 'alumnos';
                } elseif (strpos($class, 'Grupo') !== false) {
                    return 'grupos';
                } elseif (strpos($class, 'CampoFormativo') !== false || strpos($class, 'Campos') !== false) {
                    return 'campos-formativos';
                } elseif (strpos($class, 'Ciclos') !== false) {
                    return 'ciclos';
                } elseif (strpos($class, 'Momentos') !== false) {
                    return 'momentos';
                } elseif (strpos($class, 'Evaluacion') !== false) {
                    return 'evaluaciones';
                }

                // Si no se pudo determinar, registrarlo
                logger("No se pudo determinar el contexto para la clase: {$class}");
                return null;
        }
    }
}
