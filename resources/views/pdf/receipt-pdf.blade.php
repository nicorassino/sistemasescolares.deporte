<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo - Juvenilia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; padding: 24px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1f2937; padding-bottom: 12px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px 0; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { font-weight: bold; color: #374151; width: 140px; }
        .amount { font-size: 16px; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Escuela de Deportes Juvenilia</div>
        <div class="subtitle">Recibo de pago</div>
    </div>

    <table>
        <tr>
            <th>Alumno</th>
            <td>{{ $fee->student->last_name }}, {{ $fee->student->first_name }}</td>
        </tr>
        <tr>
            <th>Concepto</th>
            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y') }}</td>
        </tr>
        <tr>
            <th>Monto</th>
            <td class="amount">$ {{ number_format($fee->amount, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Fecha de pago</th>
            <td>{{ $paidAt ? \Carbon\Carbon::parse($paidAt)->format('d/m/Y') : '—' }}</td>
        </tr>
        <tr>
            <th>Forma de pago</th>
            <td>{{ $paymentMethod }}</td>
        </tr>
    </table>

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} — Juvenilia
    </div>
</body>
</html>
