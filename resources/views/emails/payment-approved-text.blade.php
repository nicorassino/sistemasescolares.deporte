Hola,

@if($isPartial)
Te informamos que tu pago parcial fue acreditado por la administración.
@else
Te informamos que tu pago ha sido acreditado por la administración.
@endif

Alumno: {{ $fee->student->last_name }}, {{ $fee->student->first_name }}
Concepto: {{ \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y') }}
Monto acreditado: $ {{ number_format((float) $appliedAmount, 2, ',', '.') }}
@if($isPartial)
Saldo pendiente: $ {{ number_format((float) $remainingAmount, 2, ',', '.') }}
@endif

Tu comprobante quedó guardado y podés descargarlo desde el Portal de Padres:
https://juvefutbol.institutojuvenilia.edu.ar

Para consultas administrativas:
juvefutbol@institutojuvenilia.edu.ar

Escuela de Deportes Juvenilia
