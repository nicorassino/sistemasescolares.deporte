<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\GeneradorCuotasMasivo;
use App\Livewire\Admin\GroupsPage;
use App\Livewire\Admin\StudentsPage;
use App\Livewire\Tutor\TutorDashboard;
use App\Livewire\Tutor\TutorLogin;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', fn () => redirect()->route('tutor.login'))->name('login');

Route::prefix('admin')->group(function () {
    Route::get('/grupos', GroupsPage::class)->name('admin.groups');
    Route::get('/alumnos', StudentsPage::class)->name('admin.students');
    Route::get('/cuotas/generar', GeneradorCuotasMasivo::class)->name('admin.fees.generate');
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
