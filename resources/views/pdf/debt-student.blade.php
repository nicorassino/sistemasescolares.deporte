<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deuda del alumno - Juvenilia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; padding: 24px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1f2937; padding-bottom: 12px; }
        .header-logos { display: flex; align-items: center; justify-content: center; gap: 16px; margin-bottom: 8px; }
        .header-logos img { max-height: 48px; width: auto; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .student-name { font-size: 14px; font-weight: bold; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 6px 8px; text-align: left; border: 1px solid #e5e7eb; }
        th { font-weight: bold; background: #f9fafb; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 14px; background: #f3f4f6; }
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
        <div class="subtitle">Resumen de deuda por alumno</div>
    </div>

    <div class="student-name">
        {{ $student->last_name }}, {{ $student->first_name }}
        @if($student->dni)
            — DNI {{ $student->dni }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Período</th>
                <th class="text-right">Monto</th>
                <th class="text-right">Pagado</th>
                <th>Estado</th>
                <th class="text-right">Adeudado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->period_label }}</td>
                    <td class="text-right">$ {{ number_format($row->amount, 2, ',', '.') }}</td>
                    <td class="text-right">$ {{ number_format($row->paid_amount, 2, ',', '.') }}</td>
                    <td>
                        @if($row->status === 'paid')
                            Pagado
                        @elseif($row->status === 'partial')
                            Parcial
                        @else
                            Pendiente
                        @endif
                    </td>
                    <td class="text-right">$ {{ number_format($row->owed, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay cuotas registradas.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right">Total adeudado</td>
                    <td class="text-right">$ {{ number_format($totalDebt, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Documento generado el {{ $generatedAt->format('d/m/Y H:i') }} — Juvenilia
    </div>
</body>
</html>
