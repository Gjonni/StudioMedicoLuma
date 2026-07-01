<?php

use App\Http\Controllers\AdguardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SambaController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\Settings\ActivityController;
use App\Http\Controllers\Settings\ModuleSettingsController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:modulo-stampa')->group(function () {
        Route::get('/print', [PrintController::class, 'index'])->name('print.index');
        Route::get('/print/setup', [PrintController::class, 'setup'])->name('print.setup');
    });

    Route::middleware('permission:modulo-scansione')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    });

    Route::middleware('permission:modulo-samba')->group(function () {
        Route::get('/samba', [SambaController::class, 'index'])->name('samba.index');
    });

    Route::middleware('permission:modulo-adguard')->group(function () {
        Route::get('/adguard', [AdguardController::class, 'index'])->name('adguard.index');
    });

    Route::middleware('permission:modulo-calendario')->group(function () {
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
        Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
        Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
        Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

        Route::get('/google-calendar/connect', [GoogleCalendarController::class, 'connect'])->name('google-calendar.connect');
        Route::get('/google-calendar/callback', [GoogleCalendarController::class, 'callback'])->name('google-calendar.callback');
        Route::post('/google-calendar/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google-calendar.disconnect');
        Route::get('/google-calendar/calendars', [GoogleCalendarController::class, 'listCalendars'])->name('google-calendar.list-calendars');
        Route::post('/google-calendar/calendar', [GoogleCalendarController::class, 'setCalendar'])->name('google-calendar.set-calendar');
    });

    // Azioni dirette sui servizi nativi: throttle non solo anti-abuso ma anche
    // anti doppio-click/retry automatico, dato che ogni chiamata invoca un
    // processo reale (lpstat/cancel, scanimage, HTTP verso AdGuard) su un
    // dispositivo con 1GB di RAM.
    Route::middleware(['throttle:10,1', 'permission:modulo-stampa'])->group(function () {
        Route::post('/print/{job}/cancel', [PrintController::class, 'cancel'])->name('print.cancel');
    });

    Route::middleware(['throttle:10,1', 'permission:modulo-adguard'])->group(function () {
        Route::post('/adguard/protection', [AdguardController::class, 'toggleProtection'])->name('adguard.protection');
    });

    // La scansione è l'azione più pesante (SANE + elaborazione immagine): limite più stretto.
    Route::middleware(['throttle:3,1', 'permission:modulo-scansione'])->group(function () {
        Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');
    });

    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('roles', RoleController::class)->except('show');
        Route::get('modules', [ModuleSettingsController::class, 'edit'])->name('modules.edit');
        Route::put('modules', [ModuleSettingsController::class, 'update'])->name('modules.update');
        Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
