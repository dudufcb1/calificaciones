<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación #{{ $evaluacion->id }} - {{ $evaluacion->titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #000066;
        }
        h2 {
            font-size: 16px;
            margin-bottom: 8px;
            color: #000066;
        }
        .info-box {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #cccccc;
        }
        .info-title {
            font-weight: bold;
            color: #333333;
        }
        .info-content {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #cccccc;
        }
        th {
            background-color: #eeeeee;
            text-align: left;
            padding: 6px;
            border: 1px solid #cccccc;
            font-weight: bold;
        }
        td {
            padding: 6px;
            border: 1px solid #cccccc;
        }
        .trial-notice {
            background-color: #ffffcc;
            color: #333333;
            padding: 8px;
            margin-top: 15px;
            text-align: center;
            border: 1px solid #ffcc00;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666666;
            font-size: 10px;
            border-top: 1px solid #cccccc;
            padding-top: 5px;
        }
        .promedio {
            font-weight: bold;
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
            <span class="info-title">Docente:</span>
            <span class="info-content">{{ $nombreDocente }}</span>
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
            Exportación limitada a 10 registros (modo Trial).
        </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ $fecha }}</p>
        <p>Sistema de Calificaciones</p>
    </div>
</body>
</html>
