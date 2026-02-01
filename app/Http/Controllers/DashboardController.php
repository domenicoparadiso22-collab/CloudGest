<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\WorkReport;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Employee; // <--- NUOVO
use Carbon\Carbon;       // <--- NUOVO (Per gestire le date delle ferie)
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Statistiche Rapide
        $clientsCount = Client::where('user_id', $userId)->count();
        $reportsThisMonth = WorkReport::where('user_id', $userId)
                                ->whereMonth('date', now()->month)
                                ->whereYear('date', now()->year)
                                ->count();
        $quotesPending = Quote::where('user_id', $userId)->where('status', 'sent')->count();
        
        // Somma delle fatture non pagate o scadute
        $unpaidAmount = Invoice::where('user_id', $userId)
                            ->whereIn('status', ['unpaid', 'overdue'])
                            ->sum('total_gross');

        // 2. Ultimi 5 Rapporti
        $recentReports = WorkReport::where('user_id', $userId)
                            ->with('client')
                            ->orderBy('date', 'desc')
                            ->limit(5)
                            ->get();

        // 3. Fatture in Scadenza o Scadute (ultime 5)
        $unpaidInvoices = Invoice::where('user_id', $userId)
                            ->whereIn('status', ['unpaid', 'overdue'])
                            ->with('client')
                            ->orderBy('due_date', 'asc') // Le più urgenti prima
                            ->limit(5)
                            ->get();

        // 4. PANORAMICA DIPENDENTI (NUOVA SEZIONE)
        // Recuperiamo i dipendenti con:
        // - L'ultima timbratura (per capire se sono IN o OUT)
        // - La prossima assenza/ferie programmata (o quella in corso)
        $employees = Employee::where('user_id', $userId)
            ->with(['attendances' => function($query) {
                $query->latest('date')->take(1); 
            }, 'absences' => function($query) {
                // Prende assenze che finiscono oggi o nel futuro
                $query->where('end_date', '>=', Carbon::today())
                      ->orderBy('start_date', 'asc')
                      ->take(1);
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('dashboard', compact(
            'clientsCount', 
            'reportsThisMonth', 
            'quotesPending', 
            'unpaidAmount',
            'recentReports',
            'unpaidInvoices',
            'employees' // <--- Passiamo la variabile alla vista
        ));
    }
}