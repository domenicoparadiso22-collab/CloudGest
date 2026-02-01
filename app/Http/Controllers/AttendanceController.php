<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 1. MOSTRA PAGINA LOGIN DIPENDENTE
    public function loginForm()
    {
        // Se è già loggato, va diretto alla dashboard
        if (session()->has('employee_id')) {
            return redirect()->route('employee.dashboard');
        }
        return view('employee.login');
    }

    // 2. PROCESSA IL LOGIN (Matricola + Password)
    public function login(Request $request)
    {
        $request->validate([
            'registration_number' => 'required',
            'password' => 'required'
        ]);

        $employee = Employee::where('registration_number', $request->registration_number)->first();

        // CONFRONTO DIRETTO (Stringa == Stringa)
        if ($employee && $request->password === $employee->password) {
            
            session([
                'employee_id' => $employee->id,
                'employee_name' => $employee->name
            ]);
            return redirect()->route('employee.dashboard');
        }

        return back()->withErrors(['msg' => 'Matricola o Password errati.']);
    }

    // 3. LOGOUT DIPENDENTE
    public function logout()
    {
        session()->forget(['employee_id', 'employee_name']);
        return redirect()->route('employee.login');
    }

    // 4. DASHBOARD (Il Tasto Timbratura)
    public function dashboard()
    {
        if (!session()->has('employee_id')) return redirect()->route('employee.login');

        $employeeId = session('employee_id');
        $today = Carbon::today();

        // Cerchiamo se esiste già una timbratura oggi
        $attendance = Attendance::where('employee_id', $employeeId)
                        ->where('date', $today)
                        ->first();

        // Determiniamo lo stato: 
        // null = Deve ancora entrare
        // clock_in presente & clock_out null = È dentro (deve uscire)
        // entrambi presenti = Ha finito per oggi
        
        $status = 'enter'; 
        if ($attendance) {
            if ($attendance->clock_in && !$attendance->clock_out) {
                $status = 'exit';
            } elseif ($attendance->clock_in && $attendance->clock_out) {
                $status = 'finished';
            }
        }

        return view('employee.dashboard', compact('status', 'attendance'));
    }

    // 5. AZIONE DI TIMBRATURA (Riceve GPS)
    public function clock(Request $request)
    {
        if (!session()->has('employee_id')) return redirect()->route('employee.login');

        $employeeId = session('employee_id');
        $coords = $request->input('coords');

        // FORZIAMO IL FUSO ORARIO EUROPE/ROME
        $now = Carbon::now('Europe/Rome');
        $today = Carbon::today('Europe/Rome'); // Anche la data odierna deve seguire il fuso

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employeeId,
            'date' => $today
        ]);

        if (!$attendance->exists) {
            // INGRESSO
            $attendance->clock_in = $now;
            $attendance->location_in = $coords;
            $attendance->save();
            $msg = "Ingresso registrato alle " . $now->format('H:i');
        } else {
            // USCITA
            if (!$attendance->clock_out) {
                $attendance->clock_out = $now;
                $attendance->location_out = $coords;
                $attendance->save();
                $msg = "Uscita registrata alle " . $now->format('H:i');
            } else {
                return back()->withErrors(['msg' => 'Hai già completato il turno oggi!']);
            }
        }

        return redirect()->route('employee.dashboard')->with('success', $msg);
    }
}
