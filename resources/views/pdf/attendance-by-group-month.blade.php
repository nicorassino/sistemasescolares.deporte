<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asistencia por grupo y mes - Juvenilia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; padding: 24px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1f2937; padding-bottom: 12px; }
        .header-logos { margin-bottom: 8px; }
        .header-logos img { max-height: 48px; width: auto; vertical-align: middle; margin: 0 8px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .meta { margin-top: 16px; margin-bottom: 8px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 6px 8px; text-align: left; border: 1px solid #e5e7eb; vertical-align: top; }
        th { font-weight: bold; background: #f9fafb; }
        .text-right { text-align: right; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($logoJuvenilia) && is_file($logoJuvenilia))
            <div class="header-logos">
                <img src="{{ $logoJuvenilia }}" alt="Juvenilia">
                @if(!empty($logoDeporte) && is_file($logoDeporte))
                    <img src="{{ $logoDeporte }}" alt="Deporte">
                @endif
            </div>
        @endif
        <div class="title">Escuela de Deportes Juvenilia</div>
        <div class="subtitle">Reporte de asistencia por grupo y mes</div>
    </div>

    <div class="meta">
        <strong>Grupo:</strong> {{ $group->name }}<br>
        <strong>Mes:</strong> {{ \Illuminate\Support\Str::ucfirst($monthLabel) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th class="text-right">Presentes</th>
                <th class="text-right">Ausentes</th>
                <th class="text-right">Total marcadas</th>
                <th>Detalle (día/estado)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->student->last_name }}, {{ $row->student->first_name }}</td>
                    <td class="text-right">{{ $row->present_count }}</td>
                    <td class="text-right">{{ $row->absent_count }}</td>
                    <td class="text-right">{{ $row->total_marked }}</td>
                    <td>{{ $row->detail !== '' ? $row->detail : 'Sin asistencias cargadas en el mes.' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay alumnos activos en el grupo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento generado el {{ $generatedAt->format('d/m/Y H:i') }} — Juvenilia
    </div>
</body>
</html>
