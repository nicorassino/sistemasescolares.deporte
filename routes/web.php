<?php

use App\Http\Controllers\DebtPdfController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\StudentPdfController;
use App\Models\Announcement;
use App\Models\Fee;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Middleware\EnsureTutorPortal;
use App\Middleware\EnsureTeacherPortal;
use App\Middleware\EnsureAdmin;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\AdminLogin;
use App\Livewire\Admin\AnnouncementManager;
use App\Livewire\Admin\ChangeAdminPassword;
use App\Livewire\Admin\FeeManager;
use App\Livewire\Admin\GeneradorCuotasMasivo;
use App\Livewire\Admin\GroupsPage;
use App\Livewire\Admin\ImpersonateTeacher;
use App\Livewire\Admin\StudentsPage;
use App\Livewire\Admin\TeachersPage;
use App\Livewire\Admin\TreasuryPanel;
use App\Livewire\Teacher\TeacherDashboard;
use App\Livewire\Teacher\TeacherLogin;
use App\Livewire\Tutor\TutorChangePassword;
use App\Livewire\Tutor\TutorDashboard;
use App\Livewire\Tutor\TutorLogin;

Route::get('/', fn () => redirect()->route('tutor.login'));

Route::get('/login', fn () => redirect()->route('tutor.login'))->name('login');

// Destino estándar para usuarios autenticados (evita loops con middleware guest).
Route::get('/home', function () {
    $user = Auth::user();

    if (! $user) {
        return redirect()->route('tutor.login');
    }

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'teacher') {
        return redirect()->route('profesor.dashboard');
    }

    if ($user->role === 'tutor') {
        if ((bool) $user->must_change_password) {
            return redirect()->route('tutor.change-password');
        }

        return redirect()->route('tutor.dashboard');
    }

    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('tutor.login');
})->name('home')->middleware('auth');

Route::get('/recibo/{fee}', [ReceiptController::class, 'download'])
    ->middleware(['auth'])
    ->name('receipt.download');

Route::get('/recibo/{fee}/descargar', [ReceiptController::class, 'download'])
    ->middleware(['signed'])
    ->name('receipt.download.signed');

Route::get('/novedades/imagen/{announcement}', function (Announcement $announcement) {
    $disk = Storage::disk('public');

    if (! $announcement->image_path || ! $disk->exists($announcement->image_path)) {
        abort(404);
    }

    $extension = strtolower(pathinfo($announcement->image_path, PATHINFO_EXTENSION));
    $mimeType = match ($extension) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        default => 'application/octet-stream',
    };

    return response($disk->get($announcement->image_path), 200, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->middleware('auth')->name('announcements.image');

Route::get('/admin/login', AdminLogin::class)->name('admin.login')->middleware('guest');

Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('admin.logout')->middleware(EnsureAdmin::class);

Route::prefix('admin')->middleware(EnsureAdmin::class)->group(function () {
    Route::get('/', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/cambiar-clave', ChangeAdminPassword::class)->name('admin.change-password');
    Route::get('/grupos', GroupsPage::class)->name('admin.groups');
    Route::get('/alumnos', StudentsPage::class)->name('admin.students');
    Route::get('/alumnos/pdf/listado', [StudentPdfController::class, 'groups'])->name('admin.students-pdf.by-group');
    Route::get('/alumnos/pdf/asistencia', [AttendanceReportController::class, 'adminGroupMonth'])->name('admin.attendance-pdf.by-group-month');
    Route::get('/novedades', AnnouncementManager::class)->name('admin.announcements');
    Route::get('/profesores', TeachersPage::class)->name('admin.teachers');
    Route::get('/cuotas/generar', GeneradorCuotasMasivo::class)->name('admin.fees.generate');
    Route::get('/tesoreria', TreasuryPanel::class)->name('admin.treasury');
    Route::get('/deudas', FeeManager::class)->name('admin.fee-manager');
    Route::get('/deudas/pdf/alumno/{student}', [DebtPdfController::class, 'student'])->name('admin.debt-pdf.student');
    Route::get('/deudas/pdf/grupo/{group}', [DebtPdfController::class, 'group'])->name('admin.debt-pdf.group');
    Route::get('/impersonate/profesor', ImpersonateTeacher::class)->name('admin.impersonate.teacher');
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
    Route::get('/cambiar-clave', TutorChangePassword::class)->name('tutor.change-password')->middleware(EnsureTutorPortal::class);
    Route::get('/', TutorDashboard::class)->name('tutor.dashboard')->middleware(EnsureTutorPortal::class);
});

Route::prefix('profesor')->group(function () {
    Route::get('/login', TeacherLogin::class)->name('profesor.login')->middleware('guest');
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('profesor.login');
    })->name('profesor.logout');
    Route::get('/', TeacherDashboard::class)->name('profesor.dashboard')->middleware(EnsureTeacherPortal::class);
    Route::get('/asistencia/pdf', [AttendanceReportController::class, 'teacherGroupMonth'])->name('profesor.attendance-pdf.by-group-month')->middleware(EnsureTeacherPortal::class);
});

// Cualquier ruta no definida => login de tutor
Route::fallback(fn () => redirect()->route('tutor.login'));
