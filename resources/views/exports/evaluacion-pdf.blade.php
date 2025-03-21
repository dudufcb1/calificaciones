<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación #{{ $evaluacion->id }} - {{ $evaluacion->titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #1a56db;
        }
        h2 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #1a56db;
        }
        .info-box {
            background-color: #f9fafb;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-title {
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 3px;
        }
        .info-content {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: bold;
            color: #6b7280;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .trial-notice {
            background-color: #fef3c7;
            color: #92400e;
            padding: 8px;
            margin-top: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        .promedio {
            font-weight: bold;
            color: #1a56db;
        }
    </style>
</head>
<body>
    <h1>Evaluación: {{ $evaluacion->titulo }}</h1>

    <div class="info-box">
        <div>
            <span class="info-title">Campo Formativo:</span>
            <span class="info-content">{{ $evaluacion->campoFormativo->nombre }}</span>
        </div>
        <div>
            <span class="info-title">Fecha de Evaluación:</span>
            <span class="info-content">{{ $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'No definida' }}</span>
        </div>
        <div>
            <span class="info-title">Momento:</span>
            <span class="info-content">{{ $evaluacion->momento ? $evaluacion->momento->value : 'No definido' }}</span>
        </div>
        <div>
            <span class="info-title">Docente:</span>
            <span class="info-content">{{ $nombreDocente }}</span>
        </div>
        <div>
            <span class="info-title">Descripción:</span>
            <span class="info-content">{{ $evaluacion->descripcion ?: 'Sin descripción' }}</span>
        </div>
    </div>

    <h2>Criterios de Evaluación</h2>
    <table>
        <thead>
            <tr>
                <th>Criterio</th>
                <th>Descripción</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($criterios as $criterio)
                <tr>
                    <td>{{ $criterio->nombre }}</td>
                    <td>{{ $criterio->descripcion }}</td>
                    <td>{{ $criterio->porcentaje }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Alumnos Evaluados</h2>
    @if(count($detalles) > 0)
        <table>
            <thead>
                <tr>
                    <th>Alumno</th>
                    @foreach($criterios as $criterio)
                        <th>{{ $criterio->nombre }}</th>
                    @endforeach
                    <th>Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $detalle)
                    <tr>
                        <td>{{ $detalle['nombre'] }}</td>
                        @foreach($detalle['calificaciones'] as $calificacion)
                            <td>
                                {{ $calificacion['valor'] }}
                                <small>({{ number_format($calificacion['ponderada'], 2) }})</small>
                            </td>
                        @endforeach
                        <td class="promedio">{{ number_format($detalle['promedio'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No hay alumnos evaluados.</p>
    @endif

    @if($limitarRegistros)
        <div class="trial-notice">
            Exportación limitada a 10 registros (modo Trial). Actualice a la versión completa para exportar todos los registros.
        </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ $fecha }}</p>
        <p>Sistema de Calificaciones</p>
    </div>
</body>
</html>
