<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Material;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    private function getNextNumber()
    {
        // 1. Prendi l'anno corrente
        $year = date('Y');

        // 2. Cerca l'ultimo numero convertendolo in INTERO (CAST)
        // Questo risolve il problema che "9" > "10" nelle stringhe
        $lastInvoice = Invoice::where('user_id', Auth::id())
            ->whereYear('date', $year) // Resetta ogni anno (opzionale, se vuoi infinito togli questa riga)
            ->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num') // O 'CAST(number AS INTEGER)' per SQLite
            ->value('max_num');

        return $lastInvoice ? ($lastInvoice + 1) : 1;
    }

    public function index(Request $request)
    {
        $query = Invoice::where('user_id', Auth::id())->with('client');

        // 1. RICERCA
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. FILTRO STATO
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // 3. ORDINAMENTO
        $sort = $request->get('sort', 'date');
        $direction = $request->get('direction', 'desc');

        if ($sort === 'client') {
            $query->join('clients', 'invoices.client_id', '=', 'clients.id')
                  ->orderBy('clients.name', $direction)
                  ->select('invoices.*');
        } elseif ($sort === 'number') {
            // Ordinamento corretto per numero (1, 2, ... 10, 11)
            $query->orderByRaw("CAST(number AS UNSIGNED) $direction");
        } else {
            $query->orderBy($sort, $direction);
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    // Usiamo la nuova logica nel CREATE
    public function create()
    {
        // 1. Recupera Clienti
        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();
        
        // 2. Recupera Materiali (QUESTO MANCAVA)
        $materials = \App\Models\Material::where('user_id', Auth::id())->orderBy('description')->get();

        // 3. Calcola prossimo numero
        $nextNumber = $this->getNextNumber();

        // 4. Passa tutto alla vista
        return view('invoices.create', compact('clients', 'materials', 'nextNumber'));
    }

    // Usiamo la nuova logica nello STORE
    public function store(Request $request)
    {
        // 1. Validazione (assicuriamoci che ci siano i dati delle righe)
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'rows' => 'required|array|min:1',
            'rows.*.description' => 'required|string',
            'rows.*.quantity' => 'required|numeric',
            'rows.*.price' => 'required|numeric',
        ]);

        // Usiamo una transazione per essere sicuri che salvi tutto o niente
        $invoice = DB::transaction(function () use ($request) {
            
            // 2. Calcolo numero (usando la logica anti-bug del "10")
            $number = $request->number ?? $this->getNextNumber();

            // 3. Crea la Testata Fattura
            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'client_id' => $request->client_id,
                'number' => $number,
                'date' => $request->date,
                'status' => 'unpaid',
                'notes' => $request->notes,
                'total_net' => 0, 
                'total_vat' => 0, 
                'total_gross' => 0
            ]);

            $totalNet = 0;

            // 4. CICLO SULLE RIGHE E SALVATAGGIO
            foreach ($request->rows as $row) {
                $subtotal = $row['quantity'] * $row['price'];
                
                $invoice->rows()->create([
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pz',
                    'price' => $row['price'],
                    'total' => $subtotal,
                    'vat_rate' => 22, // Puoi renderlo dinamico se serve
                ]);

                $totalNet += $subtotal;
            }

            // 5. Aggiorna i totali della testata
            $totalVat = $totalNet * 0.22;
            $invoice->update([
                'total_net' => $totalNet,
                'total_vat' => $totalVat,
                'total_gross' => $totalNet + $totalVat
            ]);

            return $invoice;
        });

        return redirect()->route('invoices.edit', $invoice)->with('success', 'Fattura creata con tutti i materiali!');
    }

    // --- AZIONE DI MASSA: ELIMINA ---
    public function bulkDestroy(Request $request)
    {
        $request->validate(['invoice_ids' => 'required|array']);
        
        Invoice::whereIn('id', $request->invoice_ids)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Fatture eliminate correttamente.');
    }

    // --- AZIONE DI MASSA: SEGNA COME PAGATA ---
    public function bulkMarkPaid(Request $request)
    {
        $request->validate(['invoice_ids' => 'required|array']);

        Invoice::whereIn('id', $request->invoice_ids)
            ->where('user_id', Auth::id())
            ->update(['status' => 'paid']);

        return back()->with('success', 'Fatture segnate come PAGATE.');
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) abort(403);

        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();
        $materials = Material::where('user_id', Auth::id())->orderBy('description')->get();

        $existingRows = $invoice->rows->map(function($row) {
            return [
                'id' => $row->id,
                'material_id' => '', 
                'description' => $row->description,
                'quantity' => $row->quantity,
                'unit' => $row->unit,
                'price' => $row->price,
                'vat_rate' => $row->vat_rate // Importante per le fatture
            ];
        });

        return view('invoices.edit', compact('invoice', 'clients', 'materials', 'existingRows'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) abort(403);

        // AGGIUNTA VALIDAZIONE MANCANTE
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',      // <--- Questo mancava!
            'rows' => 'required|array|min:1',
            'rows.*.description' => 'required|string',
            'rows.*.quantity' => 'required|numeric',
            'rows.*.price' => 'required|numeric',
            'rows.*.vat_rate' => 'required|integer',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $invoice->update([
                'client_id' => $request->client_id,
                'date' => $request->date,
                'due_date' => $request->due_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'private_notes' => $request->private_notes,
                'status' => $request->status,
            ]);

            // Cancelliamo le vecchie righe e ricreiamo le nuove
            $invoice->rows()->delete();

            $totalNet = 0;
            $totalVat = 0;

            foreach ($request->rows as $row) {
                $rowTotal = $row['quantity'] * $row['price'];
                $rowVat = $rowTotal * ($row['vat_rate'] / 100);

                $invoice->rows()->create([
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pz',
                    'price' => $row['price'],
                    'vat_rate' => $row['vat_rate'],
                    'total' => $rowTotal,
                ]);

                $totalNet += $rowTotal;
                $totalVat += $rowVat;
            }

            // Aggiorniamo i totali
            $invoice->update([
                'total_net' => $totalNet,
                'total_vat' => $totalVat,
                'total_gross' => $totalNet + $totalVat
            ]);
        });

        return redirect()->route('invoices.edit', $invoice)->with('success', 'Fattura aggiornata!');
    }

    public function streamPdf(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) abort(403);
        $settings = CompanySetting::where('user_id', Auth::id())->first();
        
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'settings' => $settings
        ]);
        
        return $pdf->stream('fattura_'.$invoice->number.'.pdf');
    }
    public function destroy(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) abort(403);
        
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Fattura eliminata correttamente.');
    }
}