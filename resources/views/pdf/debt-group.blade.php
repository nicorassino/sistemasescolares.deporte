<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deuda del grupo - Juvenilia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; padding: 24px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1f2937; padding-bottom: 12px; }
        .header-logos { margin-bottom: 8px; }
        .header-logos img { max-height: 48px; width: auto; vertical-align: middle; margin: 0 8px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .group-name { font-size: 14px; font-weight: bold; margin: 16px 0 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 8px 10px; text-align: left; border: 1px solid #e5e7eb; }
        th { font-weight: bold; background: #f9fafb; }
        .text-right { text-align: right; }
        .group-total { font-size: 14px; font-weight: bold; margin-top: 16px; padding: 8px; background: #e5e7eb; }
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
        <div class="subtitle">Resumen de deuda por grupo</div>
    </div>

    <div class="group-name">Grupo: {{ $group->name }}</div>

    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Concepto (meses no saldados)</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($studentRows as $row)
                <tr>
                    <td>{{ $row->student->last_name }}, {{ $row->student->first_name }}</td>
                    <td>{{ $row->concept ?: '—' }}</td>
                    <td class="text-right">$ {{ number_format($row->saldo, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No hay cuotas registradas para este grupo.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($studentRows) > 0)
            <tfoot>
                <tr class="group-total">
                    <td colspan="2" class="text-right">Total deuda del grupo</td>
                    <td class="text-right">$ {{ number_format($groupTotal, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Documento generado el {{ $generatedAt->format('d/m/Y H:i') }} — Juvenilia
    </div>
</body>
</html>
