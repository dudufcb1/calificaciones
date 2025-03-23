<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Aplicación de Calificaciones

## Uso del Servicio de Asistencia

La aplicación ahora cuenta con un servicio dedicado para calcular estadísticas de asistencia, lo que permite reutilizar esta lógica en cualquier parte de la aplicación.

### Calcular Estadísticas de Asistencia

```php
use App\Services\AsistenciaService;

// Crear una instancia del servicio
$asistenciaService = new AsistenciaService();

// Calcular estadísticas para un alumno en un rango de fechas
$estadisticas = $asistenciaService->calcularEstadisticas(
    $alumnoId, // Puede ser un ID único o un array de IDs
    '2023-10-01', // Fecha de inicio
    '2023-10-31', // Fecha de fin
    $diasNoLaborables // Array opcional de fechas no laborables
);

// O para un mes completo
$estadisticasMes = $asistenciaService->obtenerEstadisticasMensuales(
    $alumnoId,
    10, // Mes (1-12)
    2023, // Año
    $diasNoLaborables // Array opcional de fechas no laborables
);

// Ejemplo de uso para un grupo completo
$alumnosIds = $grupo->alumnos->pluck('id')->toArray();
$estadisticasGrupo = $asistenciaService->calcularEstadisticas(
    $alumnosIds,
    $fechaInicio,
    $fechaFin
);

// Para calcular estadísticas por campo formativo
$estadisticasPorCampo = $asistenciaService->calcularEstadisticasPorCampoFormativo(
    $alumnoId,
    $fechaInicio,
    $fechaFin,
    $diasNoLaborables,
    $camposFormativosPorDia // Array asociativo [fecha => [campo_formativo_ids]]
);
```

### Estructura de Datos Devuelta

El servicio devuelve un array con la siguiente estructura:

```php
[
    $alumnoId => [
        'alumno' => $objetoAlumno, // Modelo del alumno
        'total_dias' => 20, // Total de días laborables
        'asistencias' => 18, // Número de asistencias
        'inasistencias' => 2, // Número de inasistencias
        'justificadas' => 0, // Número de inasistencias justificadas
        'porcentaje_asistencia' => 90.00, // Porcentaje de asistencia (redondeado a 2 decimales)
        'porcentaje_inasistencia' => 10.00, // Porcentaje de inasistencia
    ],
    // Más alumnos si se proporcionó un array de IDs
]
```

Para las estadísticas por campo formativo:

```php
[
    $alumnoId => [
        $campoFormativoId => [
            'campo' => $objetoCampoFormativo, // Modelo del campo formativo
            'total_dias' => 5, // Total de días en que se impartió este campo
            'asistencias' => 4, // Número de asistencias a este campo
            'inasistencias' => 1, // Número de inasistencias a este campo
            'justificadas' => 0, // Número de inasistencias justificadas
            'porcentaje_asistencia' => 80.00, // Porcentaje de asistencia a este campo
            'porcentaje_inasistencia' => 20.00, // Porcentaje de inasistencia
        ],
        // Más campos formativos
    ],
    // Más alumnos
]
```

### Beneficios

-   **Reutilización de código**: Evita duplicar la lógica de cálculo en diferentes partes de la aplicación
-   **Mantenibilidad**: Los cambios en la lógica de cálculo solo deben realizarse en un lugar
-   **Consistencia**: Asegura que los cálculos sean consistentes en toda la aplicación
-   **Flexibilidad**: Permite calcular estadísticas para diferentes períodos y agrupaciones
-   **Rendimiento**: Optimiza consultas al calcular estadísticas para múltiples alumnos en una sola operación

## Licencia

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
