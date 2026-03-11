<?php

use App\Http\Controllers\ReceiptController;
use App\Models\Fee;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Admin\AnnouncementManager;
use App\Livewire\Admin\FeeManager;
use App\Livewire\Admin\GeneradorCuotasMasivo;
use App\Livewire\Admin\GroupsPage;
use App\Livewire\Admin\StudentsPage;
use App\Livewire\Admin\TeachersPage;
use App\Livewire\Admin\TreasuryPanel;
use App\Livewire\Teacher\TeacherDashboard;
use App\Livewire\Teacher\TeacherLogin;
use App\Livewire\Tutor\TutorDashboard;
use App\Livewire\Tutor\TutorLogin;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', fn () => redirect()->route('tutor.login'))->name('login');

Route::get('/recibo/{fee}', [ReceiptController::class, 'download'])
    ->middleware(['auth'])
    ->name('receipt.download');

Route::get('/recibo/{fee}/descargar', [ReceiptController::class, 'download'])
    ->middleware(['signed'])
    ->name('receipt.download.signed');

Route::prefix('admin')->group(function () {
    Route::get('/grupos', GroupsPage::class)->name('admin.groups');
    Route::get('/alumnos', StudentsPage::class)->name('admin.students');
    Route::get('/novedades', AnnouncementManager::class)->name('admin.announcements');
    Route::get('/profesores', TeachersPage::class)->name('admin.teachers');
    Route::get('/cuotas/generar', GeneradorCuotasMasivo::class)->name('admin.fees.generate');
    Route::get('/tesoreria', TreasuryPanel::class)->name('admin.treasury');
    Route::get('/deudas', FeeManager::class)->name('admin.fee-manager');
    Route::get('/tesoreria/comprobante/{payment}', function (Payment $payment) {
        if (! $payment->evidence_file_path || ! Storage::exists($payment->evidence_file_path)) {
            abort(404);
        }

        $downloadName = basename($payment->evidence_file_path);
        $headers = [];
        if ($payment->evidence_mime_type) {
            $headers['Content-Type'] = $payment->evidence_mime_type;
        }

        return Storage::download($payment->evidence_file_path, $downloadName, $headers);
    })->name('admin.treasury.payment-file');
});

Route::prefix('tutor')->group(function () {
    Route::get('/login', TutorLogin::class)->name('tutor.login')->middleware('guest');
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('tutor.login');
    })->name('tutor.logout');
    Route::get('/', TutorDashboard::class)->name('tutor.dashboard')->middleware('auth');
});

Route::prefix('profesor')->group(function () {
    Route::get('/login', TeacherLogin::class)->name('profesor.login')->middleware('guest');
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('profesor.login');
    })->name('profesor.logout');
    Route::get('/', TeacherDashboard::class)->name('profesor.dashboard')->middleware('auth');
});
