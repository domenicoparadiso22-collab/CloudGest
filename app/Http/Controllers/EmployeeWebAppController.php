<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Attendance;
use App\Models\WorkReport;
use App\Models\Notice;
use App\Models\Absence;
use Carbon\Carbon;

class EmployeeWebAppController extends Controller
{
    // 1. LOGIN PAGE
    public function showLogin() {
        return view('webapp.login');
    }

    // 2. EFFETTUA LOGIN
    public function login(Request $request) {
        $credentials = $request->validate([
            'registration_number' => 'required',
            'password' => 'required',
        ]);

        // Tentativo di accesso con il guard 'employee'
        // NOTA: Se le password vecchie sono in chiaro, questo fallirà finché non le risalvi criptate.
        // Se vuoi mantenere le password in chiaro (meno sicuro), dobbiamo fare un controllo manuale come prima.
        // Per ora assumiamo che tu voglia usare Auth standard di Laravel (richiede password Hashate).
        
        // Se usi password IN CHIARO, usa questo blocco custom:
        $emp = \App\Models\Employee::where('registration_number', $request->registration_number)->first();
        if ($emp && $emp->password === $request->password) {
            Auth::guard('employee')->login($emp); // Loggiamo manualmente l'utente
            return redirect()->route('webapp.dashboard');
        }

        return back()->withErrors(['msg' => 'Credenziali non valide.']);
    }

    // 3. LOGOUT
    public function logout() {
        Auth::guard('employee')->logout();
        return redirect()->route('webapp.login');
    }

    // 4. DASHBOARD PRINCIPALE
    public function dashboard() {
    $employee = Auth::guard('employee')->user();
    
    // 1. BACHECA (Sempre visibile)
    $notices = Notice::where('user_id', $employee->user_id)
                ->where(function($q) use ($employee) {
                    $q->where('employee_id', $employee->id)->orWhereNull('employee_id');
                })->orderBy('created_at', 'desc')->get();

    // 2. TIMBRATURA & STORICO
    $lastAttendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', Carbon::today())->latest()->first();
    
    $status = (!$lastAttendance || $lastAttendance->clock_out) ? 'enter' : 'exit';
    
    $history = Attendance::where('employee_id', $employee->id)
                ->orderBy('date', 'desc')->take(10)->get();

    // 3. RAPPORTI
    $pendingReports = WorkReport::where('user_id', $employee->user_id)
                        ->where('status', 'draft')->get();

    // 4. FERIE & STATO
    $leaves = Absence::where('employee_id', $employee->id)
                ->orderBy('start_date', 'desc')->get();

    return view('webapp.dashboard', compact('employee', 'status', 'notices', 'history', 'pendingReports', 'leaves'));
}

    // 5. TIMBRATURA (Copiata e adattata)
    public function clock(Request $request) {
        $employee = Auth::guard('employee')->user();
        $now = Carbon::now();
        
        // Recupera l'ultima timbratura di oggi
        $lastAttendance = Attendance::where('employee_id', $employee->id)
                        ->whereDate('created_at', Carbon::today())
                        ->latest()
                        ->first();

        $coords = $request->input('coords');

        // LOGICA:
        // 1. Se non esiste nulla oggi -> CREA NUOVA (Ingresso)
        // 2. Se esiste ma è chiusa (ha clock_out) -> CREA NUOVA (Rientro)
        // 3. Se esiste ed è aperta (no clock_out) -> AGGIORNA (Uscita)

        if (!$lastAttendance || $lastAttendance->clock_out) {
            // NUOVO INGRESSO
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => Carbon::today(),
                'clock_in' => $now,
                'location_in' => $coords
            ]);
            $msg = "Ingresso registrato!";
        } else {
            // REGISTRA USCITA SULL'ULTIMA APERTA
            $lastAttendance->update([
                'clock_out' => $now,
                'location_out' => $coords
            ]);
            $msg = "Uscita registrata! Buon riposo.";
        }

        return back()->with('success', $msg);
    }

    // 6. RICHIESTA FERIE RAPIDA
    public function requestLeave(Request $request) {
        $employee = Auth::guard('employee')->user();
        
        $request->validate(['start_date' => 'required|date', 'days' => 'required|integer|min:1']);

        $start = Carbon::parse($request->start_date);
        $end = $start->copy()->addDays($request->days - 1);

        $employee->absences()->create([
            'type' => 'ferie', // Default ferie
            'start_date' => $start,
            'end_date' => $end,
            'notes' => 'Richiesta da WebApp'
        ]);

        return back()->with('success', 'Richiesta ferie inviata!');
    }

    public function checkUpdates() {
    $employee = Auth::guard('employee')->user();
    
    // Conteggio messaggi delle ultime 24 ore
    $newNotices = Notice::where('user_id', $employee->user_id)
        ->where(function($q) use ($employee) {
            $q->where('employee_id', $employee->id)->orWhereNull('employee_id');
        })
        ->where('created_at', '>', now()->subMinutes(1)) // Messaggi creati nell'ultimo minuto
        ->count();

    // Rapporti bozza assegnati
    $newReports = WorkReport::where('user_id', $employee->user_id)
        ->where('status', 'draft')
        ->count();

    return response()->json([
        'new_notices' => $newNotices,
        'new_reports' => $newReports,
    ]);
}
}