<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Client;
use App\Models\Material;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use App\Models\InvoiceRow;

class QuoteController extends Controller
{
    private function getNextQuoteNumber()
{
    $year = date('Y');
    $lastNumber = \App\Models\Quote::where('user_id', Auth::id())
        ->whereYear('date', $year)
        ->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num')
        ->value('max_num');

    return $lastNumber ? ($lastNumber + 1) : 1;
}

    public function index(Request $request)
    {
        $query = Quote::where('user_id', Auth::id())->with('client');

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
            $query->join('clients', 'quotes.client_id', '=', 'clients.id')
                  ->orderBy('clients.name', $direction)
                  ->select('quotes.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $quotes = $query->paginate(15)->withQueryString();

        return view('quotes.index', compact('quotes'));
    }

    // --- AZIONE DI MASSA: ELIMINA ---
    public function bulkDestroy(Request $request)
    {
        $request->validate(['quote_ids' => 'required|array']);
        
        Quote::whereIn('id', $request->quote_ids)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Preventivi eliminati correttamente.');
    }

    // --- AZIONE DI MASSA: CONVERTI IN FATTURA ---
    // (Crea una fattura bozza per ogni preventivo selezionato)
    public function bulkToInvoice(Request $request)
    {
        $request->validate(['quote_ids' => 'required|array']);

        // Carichiamo esplicitamente i preventivi CON le loro righe
        $quotes = Quote::whereIn('id', $request->quote_ids)
                    ->where('user_id', Auth::id())
                    ->with('rows') 
                    ->get();
        
        $year = date('Y');
        $lastNumber = Invoice::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->selectRaw('MAX(CAST(number AS INTEGER)) as max_num')
            ->value('max_num') ?? 0;

        $nextNumCounter = $lastNumber + 1;
        $count = 0;

        foreach ($quotes as $quote) {
            DB::transaction(function () use ($quote, &$nextNumCounter, &$count) {
                // 1. Crea Fattura
                $newInvoice = Invoice::create([
                    'user_id' => Auth::id(),
                    'client_id' => $quote->client_id,
                    'number' => $nextNumCounter,
                    'date' => now(),
                    'status' => 'unpaid',
                    'notes' => "Convertito da Preventivo n. {$quote->number}. " . $quote->notes,
                    'total_net' => $quote->total_net,
                    'total_vat' => $quote->total_vat,
                    'total_gross' => $quote->total_gross,
                ]);

                // 2. COPIA MATERIALI (Fondamentale)
                foreach ($quote->rows as $quoteRow) {
                    $newInvoice->rows()->create([
                        'description' => $quoteRow->description,
                        'quantity' => $quoteRow->quantity,
                        'unit' => $quoteRow->unit,
                        'price' => $quoteRow->price,
                        'total' => $quoteRow->total,
                        'vat_rate' => $quoteRow->vat_rate ?? 22,
                    ]);
                }

                $quote->update(['status' => 'accepted']);
                $nextNumCounter++;
                $count++;
            });
        }

        return redirect()->route('invoices.index')->with('success', "$count Preventivi convertiti con successo.");
    }

    public function create()
    {
        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();
        $materials = Material::where('user_id', Auth::id())->orderBy('description')->get();
        
        $currentYear = date('Y');
        $lastQuote = Quote::where('user_id', Auth::id())
                        ->whereYear('date', $currentYear)
                        ->orderByRaw('CAST(number AS INTEGER) DESC')
                        ->first();
        $nextNumber = $this->getNextQuoteNumber();

        return view('quotes.create', compact('clients', 'materials', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'rows' => 'required|array|min:1',
        ]);

        $quote = DB::transaction(function () use ($request) {
            $quote = Quote::create([
                'user_id' => Auth::id(),
                'client_id' => $request->client_id,
                'number' => $request->number ?? $this->getNextQuoteNumber(),
                'date' => $request->date,
                'notes' => $request->notes,
                'status' => 'draft',
                'total_net' => 0, 'total_vat' => 0, 'total_gross' => 0 // Inizializzati
            ]);

            $totalNet = 0;
            foreach ($request->rows as $row) {
                $subtotal = $row['quantity'] * $row['price'];
                $quote->rows()->create([
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pz',
                    'price' => $row['price'],
                    'total' => $subtotal,
                    'vat_rate' => 22
                ]);
                $totalNet += $subtotal;
            }

            // AGGIORNA TOTALI TESTATA (Per vederli nell'index)
            $totalVat = $totalNet * 0.22;
            $quote->update([
                'total_net' => $totalNet,
                'total_vat' => $totalVat,
                'total_gross' => $totalNet + $totalVat
            ]);

            return $quote;
        });

        return redirect()->route('quotes.index')->with('success', 'Preventivo creato con successo!');
    }

    public function edit(Quote $quote)
    {
        if ($quote->user_id !== Auth::id()) abort(403);

        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();
        $materials = Material::where('user_id', Auth::id())->orderBy('description')->get();

        $existingRows = $quote->rows->map(function($row) {
            return [
                'id' => $row->id,
                'material_id' => '', 
                'description' => $row->description,
                'quantity' => $row->quantity,
                'unit' => $row->unit,
                'price' => $row->price
            ];
        });

        return view('quotes.edit', compact('quote', 'clients', 'materials', 'existingRows'));
    }

    public function update(Request $request, Quote $quote)
    {
        if ($quote->user_id !== Auth::id()) abort(403);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'rows' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $quote) {
            $quote->update([
                'client_id' => $request->client_id,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            // Cancella vecchie righe e inserisce nuove
            $quote->rows()->delete();

            $totalNet = 0;
            foreach ($request->rows as $row) {
                $subtotal = $row['quantity'] * $row['price'];
                $quote->rows()->create([
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pz',
                    'price' => $row['price'],
                    'total' => $subtotal,
                    'vat_rate' => 22
                ]);
                $totalNet += $subtotal;
            }

            // AGGIORNA TOTALI TESTATA
            $totalVat = $totalNet * 0.22;
            $quote->update([
                'total_net' => $totalNet,
                'total_vat' => $totalVat,
                'total_gross' => $totalNet + $totalVat
            ]);
        });

        return redirect()->route('quotes.index')->with('success', 'Preventivo aggiornato!');
    }

    public function streamPdf(Request $request, Quote $quote)
    {
        if ($quote->user_id !== Auth::id()) abort(403);

        $settings = CompanySetting::where('user_id', Auth::id())->first();
        
        // Recuperiamo l'opzione "Nascondi Prezzi" dalla richiesta
        $hidePrices = $request->has('hide_prices'); 

        $pdf = Pdf::loadView('quotes.pdf', [
            'quote' => $quote,
            'settings' => $settings,
            'hidePrices' => $hidePrices // <--- ECCO QUELLO CHE MANCAVA!
        ]);
        
        return $pdf->stream('preventivo_'.$quote->number.'.pdf');
    }
    public function convertToInvoice(Quote $quote)
{
    if ($quote->user_id !== Auth::id()) abort(403);

    DB::transaction(function () use ($quote) {
        // 1. Numero progressivo
        $currentYear = date('Y');
        $lastInvoice = Invoice::where('user_id', Auth::id())
                        ->whereYear('date', $currentYear)
                        ->orderByRaw('CAST(number AS INTEGER) DESC')
                        ->first();
        $nextNumber = $lastInvoice ? ($lastInvoice->number + 1) : 1;

        // 2. Crea Fattura
        $invoice = Invoice::create([
            'user_id' => Auth::id(),
            'client_id' => $quote->client_id,
            'number' => $nextNumber,
            'date' => now(),
            'status' => 'unpaid',
            'notes' => "Rif. Preventivo n. {$quote->number} del " . \Carbon\Carbon::parse($quote->date)->format('d/m/Y') . "\n\n" . $quote->notes,
        ]);

        $totalNet = 0;
        $totalVat = 0;

        // 3. Copia righe
        foreach ($quote->rows as $row) {
            $vatRate = 22; 
            $rowTotal = $row->total;
            $rowVat = $rowTotal * ($vatRate / 100);

            $invoice->rows()->create([
                'description' => $row->description,
                'quantity' => $row->quantity,
                'unit' => $row->unit,
                'price' => $row->price,
                'vat_rate' => $vatRate,
                'total' => $rowTotal,
            ]);

            $totalNet += $rowTotal;
            $totalVat += $rowVat;
        }

        $invoice->update([
            'total_net' => $totalNet,
            'total_vat' => $totalVat,
            'total_gross' => $totalNet + $totalVat
        ]);

        // Aggiorna stato preventivo in "Accettato"
        $quote->update(['status' => 'accepted']);
    });

    $newInvoice = Invoice::where('user_id', Auth::id())->latest()->first();
    return redirect()->route('invoices.edit', $newInvoice)->with('success', 'Preventivo convertito in Fattura! Stato preventivo aggiornato ad Accettato.');
}

public function destroy(Quote $quote)
    {
        if ($quote->user_id !== Auth::id()) abort(403);
        
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Preventivo eliminato correttamente.');
    }
}