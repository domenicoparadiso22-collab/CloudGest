<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    // 1. ELENCO DIPENDENTI
    public function index()
    {
        $employees = Employee::where('user_id', Auth::id())->orderBy('name')->get();
        return view('employees.index', compact('employees'));
    }

    // 2. FORM DI CREAZIONE
    public function create()
    {
        return view('employees.create');
    }

    // 3. SALVATAGGIO NUOVO DIPENDENTE (Generazione Credenziali)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // NUOVA LOGICA MATRICOLA: M + 6 NUMERI CASUALI
        do {
            // mt_rand genera un numero intero casuale tra min e max
            $matricola = 'M' . mt_rand(100000, 999999); 
        } while (Employee::where('registration_number', $matricola)->exists());

        $plainPassword = Str::random(8); 
        
        Employee::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'registration_number' => $matricola,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $plainPassword, 
        ]);

        return redirect()->route('employees.index')
            ->with('success', "Dipendente creato! Matricola: $matricola - Password: $plainPassword");
    }

    // 4. FORM DI MODIFICA
    public function edit(Employee $employee)
    {
        if ($employee->user_id !== Auth::id()) abort(403);
        return view('employees.edit', compact('employee'));
    }

    // 5. AGGIORNAMENTO DATI
    public function update(Request $request, Employee $employee)
    {
        if ($employee->user_id !== Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'password' => 'nullable|string|min:4', // Campo password diretto
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        // Se il master scrive una nuova password, la salviamo così com'è
        if ($request->filled('password')) {
            $data['password'] = $request->password; 
        }

        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Dati aggiornati.');
    }

    // 6. ELIMINAZIONE
    public function destroy(Employee $employee)
    {
        if ($employee->user_id !== Auth::id()) abort(403);
        
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Dipendente eliminato.');
    }

    // 7. PAGINA STORICO (Quella che abbiamo creato prima)
    public function history(Employee $employee)
    {
        if ($employee->user_id !== Auth::id()) abort(403);

        // Timbrature (Paginazione)
        $attendances = $employee->attendances()->orderBy('date', 'desc')->paginate(15);
        
        // Assenze (Recuperiamo le ultime 20)
        $absences = $employee->absences()->orderBy('start_date', 'desc')->take(20)->get();

        return view('employees.history', compact('employee', 'attendances', 'absences'));
    }

    // --- REGISTRAZIONE ASSENZA ---
    public function storeAbsence(Request $request, Employee $employee)
    {
        if ($employee->user_id !== Auth::id()) abort(403);

        $request->validate([
            'type' => 'required|in:ferie,malattia,ingiustificata',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string'
        ]);

        $employee->absences()->create([
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Assenza registrata correttamente.');
    }
    
    // --- ELIMINAZIONE ASSENZA (Opzionale, utile per correzioni) ---
    public function destroyAbsence($id)
    {
        $absence = \App\Models\Absence::findOrFail($id);
        if ($absence->employee->user_id !== Auth::id()) abort(403);
        
        $absence->delete();
        return back()->with('success', 'Assenza rimossa.');
    }

    // VISUALIZZA DETTAGLIO DIPENDENTE (Chat + Storico rapido)
    public function show(Employee $employee)
    {
        if ($employee->user_id !== Auth::id()) abort(403);

        // Carica messaggi privati (Chat)
        $messages = \App\Models\Notice::where('user_id', Auth::id())
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employees.show', compact('employee', 'messages'));
    }

    // INVIA MESSAGGIO PRIVATO
    public function storeMessage(Request $request, Employee $employee)
    {
        // Validazione: tutto nullable, basta che siano stringhe valide
        $request->validate([
            'message' => 'nullable|string',
            'target_location' => 'nullable|string',
            'target_email' => 'nullable|email',
            'target_phone' => 'nullable|string',
        ]);

        // Controllo logico: Almeno uno dei campi deve essere pieno
        if (empty($request->message) && empty($request->target_location) && empty($request->target_email) && empty($request->target_phone)) {
            return back()->withErrors(['message' => 'Inserisci almeno un dato (messaggio, gps, email o telefono).']);
        }

        \App\Models\Notice::create([
            'user_id' => Auth::id(),
            'employee_id' => $employee->id,
            'message' => $request->message, // Può essere null se mandi solo telefono
            'target_location' => $request->target_location,
            'target_email' => $request->target_email,
            'target_phone' => $request->target_phone,
            'is_urgent' => $request->has('is_urgent')
        ]);

        return back()->with('success', 'Inviato correttamente.');
    }

    public function destroyMessage(\App\Models\Notice $notice)
{
    // Verifica che il messaggio appartenga all'utente loggato
    if ($notice->user_id !== Auth::id()) {
        abort(403);
    }

    $notice->delete();

    return back()->with('success', 'Messaggio eliminato con successo.');
}
}