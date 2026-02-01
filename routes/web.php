<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\WorkReportController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestWorkReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Pagina di Benvenuto (Pubblica)
Route::get('/', function () {
    return view('welcome');
});

// --- AREA PROTETTA (SOLO UTENTI LOGGATI) ---
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- ROTTE CLIENTI ---
    Route::get('/clients/import', [App\Http\Controllers\ClientController::class, 'showImportForm'])->name('clients.import');
    Route::post('/clients/import/parse', [App\Http\Controllers\ClientController::class, 'parseImport'])->name('clients.parse');
    Route::post('/clients/import/process', [App\Http\Controllers\ClientController::class, 'processImport'])->name('clients.process');
    
    Route::post('/clients/bulk-delete', [App\Http\Controllers\ClientController::class, 'bulkDestroy'])->name('clients.bulk-delete');
    Route::post('/clients/bulk-document', [App\Http\Controllers\ClientController::class, 'bulkToDocument'])->name('clients.bulk-document');

    Route::resource('clients', App\Http\Controllers\ClientController::class);

    // Rapporti d'Intervento
    Route::resource('work-reports', WorkReportController::class);
    Route::get('/work-reports/{workReport}/pdf', [WorkReportController::class, 'streamPdf'])->name('work-reports.pdf');
    Route::post('/work-reports/{workReport}/convert', [WorkReportController::class, 'convertToInvoice'])->name('work-reports.convert');
    Route::post('/work-reports/{workReport}/sign', [App\Http\Controllers\WorkReportController::class, 'sign'])->name('work-reports.sign');
    // Preventivi
    // --- ROTTE PREVENTIVI ---
    Route::post('/quotes/bulk-delete', [App\Http\Controllers\QuoteController::class, 'bulkDestroy'])->name('quotes.bulk-delete');
    Route::post('/quotes/bulk-invoice', [App\Http\Controllers\QuoteController::class, 'bulkToInvoice'])->name('quotes.bulk-invoice');
    
    Route::resource('quotes', QuoteController::class);
    Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'streamPdf'])->name('quotes.pdf');
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convertToInvoice'])->name('quotes.convert');

    // Fatture
    Route::post('/invoices/bulk-delete', [App\Http\Controllers\InvoiceController::class, 'bulkDestroy'])->name('invoices.bulk-delete');
    Route::post('/invoices/bulk-paid', [App\Http\Controllers\InvoiceController::class, 'bulkMarkPaid'])->name('invoices.bulk-paid'); // Nuova funzione utile!
    
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'streamPdf'])->name('invoices.pdf');

    // Importazione CSV Materiali
    Route::get('/materials/import', [App\Http\Controllers\MaterialController::class, 'showImportForm'])->name('materials.import');
    Route::post('/materials/import/parse', [App\Http\Controllers\MaterialController::class, 'parseImport'])->name('materials.parse');
    Route::post('/materials/import/process', [App\Http\Controllers\MaterialController::class, 'processImport'])->name('materials.process');

    // --- AZIONI DI MASSA MATERIALI ---

    // 1. Importazione CSV
    Route::get('/materials/import', [App\Http\Controllers\MaterialController::class, 'showImportForm'])->name('materials.import');
    Route::post('/materials/import/parse', [App\Http\Controllers\MaterialController::class, 'parseImport'])->name('materials.parse');
    Route::post('/materials/import/process', [App\Http\Controllers\MaterialController::class, 'processImport'])->name('materials.process');

    // 2. Azioni di Massa (Elimina e Documenti)
    Route::post('/materials/bulk-delete', [App\Http\Controllers\MaterialController::class, 'bulkDestroy'])->name('materials.bulk-delete');
    
    // NOTA: Qui usiamo il nome 'materials.bulk-document' per non dover modificare il file index.blade.php
    Route::post('/materials/bulk-select-client', [App\Http\Controllers\MaterialController::class, 'showClientSelection'])->name('materials.bulk-document');
    
    Route::post('/materials/bulk-create-confirm', [App\Http\Controllers\MaterialController::class, 'createDocumentFromMaterials'])->name('materials.bulk-create-confirm');
    
    // Materiali
    Route::resource('materials', MaterialController::class);

    // Profilo Azienda (Impostazioni)
    Route::get('/company-profile', [CompanySettingController::class, 'edit'])->name('company.edit');
    Route::patch('/company-profile', [CompanySettingController::class, 'update'])->name('company.update');

    // Profilo Utente (Account)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestione Backup
    Route::get('/backups', [App\Http\Controllers\BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/create', [App\Http\Controllers\BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/download', [App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/delete', [App\Http\Controllers\BackupController::class, 'delete'])->name('backups.delete');
    Route::post('/backups/upload', [App\Http\Controllers\BackupController::class, 'upload'])->name('backups.upload');
    Route::post('/backups/restore', [App\Http\Controllers\BackupController::class, 'restore'])->name('backups.restore');

    // Gestione generazione fatture in blocco
    Route::post('/work-reports/bulk-convert', [WorkReportController::class, 'bulkConvert'])->name('work-reports.bulk-convert');
    Route::post('/work-reports/bulk-pdf-list', [WorkReportController::class, 'bulkPdfList'])->name('work-reports.bulk-pdf-list');

    // Area Master
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);
    Route::get('/employees/{employee}/history', [App\Http\Controllers\EmployeeController::class, 'history'])->name('employees.history');

    // Area Dipendente (Login e Timbratura)
    Route::get('/clock-in', [App\Http\Controllers\AttendanceController::class, 'loginForm'])->name('employee.login');
    Route::post('/clock-in/login', [App\Http\Controllers\AttendanceController::class, 'login'])->name('employee.login.post');
    Route::get('/employee/dashboard', [App\Http\Controllers\AttendanceController::class, 'dashboard'])->name('employee.dashboard');
    Route::post('/employee/clock', [App\Http\Controllers\AttendanceController::class, 'clock'])->name('employee.clock');
    
    Route::post('/employees/{employee}/absence', [App\Http\Controllers\EmployeeController::class, 'storeAbsence'])->name('employees.absence.store');
    Route::delete('/absences/{id}', [App\Http\Controllers\EmployeeController::class, 'destroyAbsence'])->name('absences.destroy');  

    Route::get('/employees/{employee}/details', [App\Http\Controllers\EmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees/{employee}/message', [App\Http\Controllers\EmployeeController::class, 'storeMessage'])->name('employees.message.store');

    Route::patch('/absences/{absence}', function(Request $request, \App\Models\Absence $absence) {
    $absence->update([
        'status' => $request->status
    ]);

    // Fondamentale: deve tornare indietro alla pagina precedente
    return redirect()->back()->with('success', 'Stato aggiornato correttamente.');
})->name('employees.absence.update');

    Route::get('/check-updates', [EmployeeWebAppController::class, 'checkUpdates'])->name('updates.check');
    Route::delete('/notices/{notice}', [App\Http\Controllers\EmployeeController::class, 'destroyMessage'])->name('employees.message.destroy');
}); 
// <--- FINE GRUPPO AUTH

// --- WEBAPP DIPENDENTI ---
Route::prefix('webapp')->name('webapp.')->group(function () {
    
    // Login
    Route::get('/login', [App\Http\Controllers\EmployeeWebAppController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\EmployeeWebAppController::class, 'login'])->name('login.post');
    Route::post('/logout', [App\Http\Controllers\EmployeeWebAppController::class, 'logout'])->name('logout');

    // Area Protetta
    Route::middleware('auth:employee')->group(function () {
        Route::get('/', [App\Http\Controllers\EmployeeWebAppController::class, 'dashboard'])->name('dashboard');
        Route::post('/clock', [App\Http\Controllers\EmployeeWebAppController::class, 'clock'])->name('clock');
        Route::post('/leave', [App\Http\Controllers\EmployeeWebAppController::class, 'requestLeave'])->name('leave');
        // QUESTA È LA ROTTA CHE MANCAVA O AVEVA UN NOME DIVERSO
        Route::get('/check-updates', [App\Http\Controllers\EmployeeWebAppController::class, 'checkUpdates'])->name('updates.check');
    });
});

// --- AREA GUEST (ACCESSO PUBBLICO PER CLIENTI) ---
// Queste rotte permettono ai clienti di firmare senza login
Route::post('/guest/search', [GuestWorkReportController::class, 'search'])->name('guest.search');
Route::get('/report-view/{code}', [GuestWorkReportController::class, 'show'])->name('guest.report.show');
Route::post('/report-view/{code}/sign', [GuestWorkReportController::class, 'sign'])->name('guest.report.sign');
Route::get('/report-view/{code}/download', [GuestWorkReportController::class, 'downloadPdf'])->name('guest.report.download');

// --- AREA DIPENDENTI (Accesso Pubblico/Autonomo) ---
Route::get('/clock-in', [App\Http\Controllers\AttendanceController::class, 'loginForm'])->name('employee.login');
Route::post('/clock-in/login', [App\Http\Controllers\AttendanceController::class, 'login'])->name('employee.login.post');
Route::post('/clock-in/logout', [App\Http\Controllers\AttendanceController::class, 'logout'])->name('employee.logout');

// Rotte protette per il dipendente (controllo sessione nel controller)
Route::get('/employee/dashboard', [App\Http\Controllers\AttendanceController::class, 'dashboard'])->name('employee.dashboard');
Route::post('/employee/clock', [App\Http\Controllers\AttendanceController::class, 'clock'])->name('employee.clock');


// Include le rotte di autenticazione (Login, Register, ecc.)
require __DIR__.'/auth.php';

