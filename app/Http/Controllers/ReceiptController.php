<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function download(Request $request, Fee $fee)
    {
        $fee->load(['student.tutors.user', 'payments' => fn ($q) => $q->where('status', 'approved')->orderByDesc('paid_on_date')]);

        if (! $request->hasValidSignature()) {
            $user = Auth::user();
            $tutor = $user?->tutor;
            $isAdmin = $user && $user->role === 'admin';
            $isTutorOwner = $tutor && $fee->student->tutors->contains('id', $tutor->id);
            if (! $isAdmin && ! $isTutorOwner) {
                abort(403);
            }
        }

        $latestPayment = $fee->payments->first();
        $paymentMethod = $latestPayment && $latestPayment->teacher_id ? 'Efectivo' : 'Transferencia';

        $pdf = Pdf::loadView('pdf.receipt-pdf', [
            'fee' => $fee,
            'paymentMethod' => $paymentMethod,
            'paidAt' => $fee->paid_at ?? $latestPayment?->paid_on_date,
            'logoJuvenilia' => public_path('IMG/logo_juvenilia.jpeg'),
            'logoDeporte' => public_path('IMG/logodepte.jpeg'),
        ]);

        $filename = 'recibo-' . $fee->id . '-' . $fee->period . '.pdf';

        return $pdf->download($filename);
    }
}
