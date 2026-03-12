<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumnos por grupo - Juvenilia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; padding: 24px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1f2937; padding-bottom: 12px; }
        .header-logos { margin-bottom: 8px; }
        .header-logos img { max-height: 48px; width: auto; vertical-align: middle; margin: 0 8px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .group-title { font-size: 13px; font-weight: bold; margin-top: 16px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { padding: 5px 6px; text-align: left; border: 1px solid #e5e7eb; }
        th { font-weight: bold; background: #f9fafb; }
        .no-students { font-size: 10px; color: #6b7280; margin-bottom: 8px; }
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
        <div class="subtitle">Listado de alumnos por grupo</div>
    </div>

    @forelse($groups as $group)
        <div class="group-block">
            <div class="group-title">
                Grupo: {{ $group->name }}
                @if($group->year)
                    — {{ $group->year }}
                @endif
            </div>

            @if($group->students->isEmpty())
                <p class="no-students">No hay alumnos asignados a este grupo.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Apellido</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Fecha de nacimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->students as $student)
                            <tr>
                                <td>{{ $student->last_name }}</td>
                                <td>{{ $student->first_name }}</td>
                                <td>{{ $student->dni ?? '—' }}</td>
                                <td>
                                    {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <p class="no-students">No hay grupos cargados.</p>
    @endforelse

    <div class="footer">
        Documento generado el {{ $generatedAt->format('d/m/Y H:i') }} — Juvenilia
    </div>
</body>
</html>

