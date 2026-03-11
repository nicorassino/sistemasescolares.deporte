<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago acreditado - Juvenilia</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #1f2937; max-width: 560px; margin: 0 auto; padding: 24px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 16px; }
        p { margin: 0 0 12px; }
        .button { display: inline-block; margin-top: 16px; padding: 12px 24px; background: #2563eb; color: #fff !important; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="title">Hola,</div>
    <p>Te informamos que tu pago ha sido acreditado por la administración.</p>
    <p><strong>Alumno:</strong> {{ $fee->student->last_name }}, {{ $fee->student->first_name }}</p>
    <p><strong>Concepto:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y') }}</p>
    <p><strong>Monto:</strong> $ {{ number_format($fee->amount, 2, ',', '.') }}</p>
    <p>Podés descargar tu recibo en PDF desde el siguiente enlace:</p>
    <p>
        <a href="{{ $receiptUrl }}" class="button">Descargar recibo PDF</a>
    </p>
    <p class="footer">Escuela de Deportes Juvenilia</p>
</body>
</html>
