<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aviso de vencimiento - Juvenilia</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #1f2937; max-width: 560px; margin: 0 auto; padding: 24px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 16px; }
        p { margin: 0 0 12px; }
        .debt { font-size: 16px; font-weight: bold; margin: 16px 0; }
        .bank { background: #f3f4f6; padding: 16px; border-radius: 8px; margin: 16px 0; font-size: 14px; }
        .bank p { margin: 4px 0; }
        .footer { margin-top: 32px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="title">Aviso de vencimiento de cuota</div>
    <p>Hola,</p>
    <p>Te recordamos que tenés una cuota pendiente para el siguiente alumno:</p>
    <p><strong>Alumno:</strong> {{ $fee->student->last_name }}, {{ $fee->student->first_name }}</p>
    <p><strong>Concepto:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y') }}</p>
    <p><strong>Monto total:</strong> $ {{ number_format($fee->amount, 2, ',', '.') }}</p>
    @if((float) $fee->paid_amount > 0)
        <p><strong>Pagado:</strong> $ {{ number_format($fee->paid_amount, 2, ',', '.') }}</p>
    @endif
    <p class="debt">Deuda: $ {{ number_format((float) $fee->amount - (float) $fee->paid_amount, 2, ',', '.') }}</p>
    <p>Para abonar por transferencia, utilizá los siguientes datos:</p>
    <div class="bank">
        <p><strong>Titular:</strong> Yacono Emanuel Rodrigo</p>
        <p><strong>CUIT/CUIL:</strong> 23-30658273-9</p>
        <p><strong>Cuenta:</strong> CA $ 925 0013909602</p>
        <p><strong>CBU:</strong> 0200925811000013909626</p>
        <p><strong>Alias:</strong> JUVENILIA.FUTBOL.EMA</p>
    </div>
    <p class="footer">Escuela de Deportes Juvenilia</p>
</body>
</html>
