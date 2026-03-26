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
        .button { display: inline-block; margin-top: 16px; padding: 12px 24px; background: #2563eb; color: #fff !important; text-decoration: none; border-radius: 8px; font-weight: bold; }
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
    <p>Para realizar el pago, ingresá al portal de padres, iniciá sesión, informá el pago y subí el comprobante desde la aplicación.</p>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 16px 0 12px;">
        <tr>
            <td align="center" bgcolor="#2563eb" style="border-radius:8px;">
                <a
                    href="https://juvefutbol.institutojuvenilia.edu.ar"
                    style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:8px;"
                >
                    Ir al Portal de Padres
                </a>
            </td>
        </tr>
    </table>
    <p style="font-size: 12px; color: #6b7280;">
        Si el botón no se visualiza correctamente, ingresá directamente a:
        <a href="https://juvefutbol.institutojuvenilia.edu.ar" style="color:#2563eb; text-decoration:underline;">
            juvefutbol.institutojuvenilia.edu.ar
        </a>
    </p>
    <p>Si tenés dudas o necesitás ayuda, contactanos en <strong>juvefutbol@institutojuvenilia.edu.ar</strong>.</p>
    <p class="footer">Escuela de Deportes Juvenilia</p>
</body>
</html>
