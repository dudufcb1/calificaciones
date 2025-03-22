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
        $componentName = end($parts);

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
                return 'ciclos';
            case 'momento':
                return 'momentos';
            case 'evaluacion':
                return 'evaluaciones';
            case 'dashboard':
                return 'dashboard';
            default:
                // Intentar detectar por namespace
                if (strpos($class, 'Alumnos') !== false) {
                    return 'alumnos';
                } elseif (strpos($class, 'Grupos') !== false) {
                    return 'grupos';
                } elseif (strpos($class, 'CamposFormativos') !== false || strpos($class, 'Campos') !== false) {
                    return 'campos-formativos';
                } elseif (strpos($class, 'Ciclos') !== false) {
                    return 'ciclos';
                } elseif (strpos($class, 'Momentos') !== false) {
                    return 'momentos';
                } elseif (strpos($class, 'Evaluaciones') !== false) {
                    return 'evaluaciones';
                }

                return null;
        }
    }
}
