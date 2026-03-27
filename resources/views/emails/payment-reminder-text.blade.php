Aviso de vencimiento de cuota

Hola,

Te recordamos que tenés una cuota pendiente para el siguiente alumno:
Alumno: {{ $fee->student->last_name }}, {{ $fee->student->first_name }}
Concepto: {{ \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y') }}
Monto total: $ {{ number_format($fee->amount, 2, ',', '.') }}
@if((float) $fee->paid_amount > 0)
Pagado: $ {{ number_format($fee->paid_amount, 2, ',', '.') }}
@endif
Deuda: $ {{ number_format((float) $fee->amount - (float) $fee->paid_amount, 2, ',', '.') }}

Para realizar el pago, ingresá al portal de padres:
https://juvefutbol.institutojuvenilia.edu.ar

Si tenés dudas o necesitás ayuda:
juvefutbol@institutojuvenilia.edu.ar

Escuela de Deportes Juvenilia
