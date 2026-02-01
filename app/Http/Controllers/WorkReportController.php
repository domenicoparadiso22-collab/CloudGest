<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use App\Models\Client;
use App\Models\Material;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // <--- AGGIUNTO: Serve per il codice univoco
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use App\Models\InvoiceRow;

class WorkReportController extends Controller
{
    // Funzione privata per calcolare il numero progressivo corretto
    private function getNextReportNumber()
    {
        $year = date('Y');
        $lastNumber = \App\Models\WorkReport::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num') // Usa INTEGER se sei su SQLite
            ->value('max_num');

        return $lastNumber ? ($lastNumber + 1) : 1;
    }

    // Lista dei rapporti
    public function index(Request $request)
    {
        $query = WorkReport::where('user_id', Auth::id())->with('client');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                ->orWhere('private_notes', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $sort = $request->get('sort', 'date'); 
        $direction = $request->get('direction', 'desc'); 

        if ($sort === 'client') {
            $query->join('clients', 'work_reports.client_id', '=', 'clients.id')
                  ->orderBy('clients.name', $direction)
                  ->select('work_reports.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $reports = $query->paginate(10)->withQueryString();

        return view('work_reports.index', compact('reports'));
    }

    // Form di creazione
    public function create()
    {
        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();
        $materials = Material::where('user_id', Auth::id())->orderBy('description')->get();
        
        $nextNumber = $this->getNextReportNumber();

        return view('work_reports.create', compact('clients', 'materials', 'nextNumber'));
    }

    // Salvataggio nuovo rapporto
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'number' => 'required',
            'rows' => 'required|array|min:1',
            'rows.*.description' => 'required|string',
            'rows.*.quantity' => 'required|numeric',
            'rows.*.price' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request) {
            $report = WorkReport::create([
                'user_id' => Auth::id(),
                'client_id' => $request->client_id,
                'number' => $request->number,
                'date' => $request->date,
                'notes' => $request->notes,
                'private_notes' => $request->private_notes,
                'status' => 'draft',
                'unique_code' => Str::uuid(), // <--- FONDAMENTALE PER FIRMA ESTERNA
            ]);

            foreach ($request->rows as $row) {
                $report->rows()->create([
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pz',
                    'price' => $row['price'],
                    'total' => $row['quantity'] * $row['price'],
                ]);
            }
        });

        return redirect()->route('work-reports.index')->with('success', 'Rapporto creato!');
    }

    // Conversione Massiva in Fattura (Bulk)
    public function bulkConvert(Request $request)
    {
        $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:work_reports,id',
        ]);

        $reportIds = $request->report_ids;
        
        $reports = WorkReport::whereIn('id', $reportIds)
                    ->where('user_id', Auth::id())
                    ->with('rows', 'client')
                    ->orderBy('date', 'asc') 
                    ->get();

        if ($reports->isEmpty()) {
            return back()->withErrors(['msg' => 'Nessun rapporto selezionato.']);
        }

        $firstClientId = $reports->first()->client_id;
        foreach ($reports as $report) {
            if ($report->client_id !== $firstClientId) {
                return back()->withErrors(['msg' => 'Errore: Seleziona solo rapporti dello stesso cliente.']);
            }
        }

        // Calcolo numero fattura corretto
        $year = date('Y');
        $lastInvoiceNumber = Invoice::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num')
            ->value('max_num') ?? 0;

        $nextNumber = $lastInvoiceNumber + 1;

        $invoice = DB::transaction(function () use ($reports, $firstClientId, $nextNumber) {
            
            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'client_id' => $firstClientId,
                'number' => $nextNumber,
                'date' => now(),
                'status' => 'unpaid',
                'notes' => "Fattura riepilogativa rapporti d'intervento.",
                'total_net' => 0, 'total_vat' => 0, 'total_gross' => 0,
            ]);

            $totalNet = 0;
            $globalVatRate = 22;

            foreach ($reports as $index => $report) {
                $reportTotal = $report->rows->sum('total');

                $descriptionText = "Rif. Rapporto n. {$report->number} del " . \Carbon\Carbon::parse($report->date)->format('d/m/Y');
                
                if($report->rows->count() > 0) {
                    $descriptionText .= "\n\nInterventi eseguiti:"; 
                    foreach($report->rows as $row) {
                        $descriptionText .= "\n- " . $row->description . " (" . number_format($row->quantity, 0) . " " . $row->unit . ")";
                    }
                }

                InvoiceRow::create([
                    'invoice_id' => $invoice->id,
                    'description' => $descriptionText, 
                    'quantity' => 1,
                    'price' => $reportTotal,
                    'total' => $reportTotal,
                ]);

                $totalNet += $reportTotal;

                if ($index < count($reports) - 1) {
                    InvoiceRow::create([
                        'invoice_id' => $invoice->id, 'description' => '', 'quantity' => 0, 'price' => 0, 'total' => 0,
                    ]);
                }

                $report->update(['status' => 'closed']);
            }

            $totalVat = $totalNet * ($globalVatRate / 100);
            $totalGross = $totalNet + $totalVat;

            $invoice->update([
                'total_net' => $totalNet,
                'total_vat' => $totalVat,
                'total_gross' => $totalGross,
            ]);

            return $invoice;
        });

        return redirect()->route('invoices.edit', $invoice->id)->with('success', "Fattura n. {$nextNumber} generata correttamente!");
    }

    // Genera PDF Riepilogo Documenti (Elenco)
    public function bulkPdfList(Request $request)
    {
        $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:work_reports,id',
        ]);

        $reports = WorkReport::whereIn('id', $request->report_ids)
                    ->where('user_id', Auth::id())
                    ->with('client', 'rows')
                    ->orderBy('date', 'asc')
                    ->get();

        if ($reports->isEmpty()) {
            return back()->withErrors(['msg' => 'Nessun rapporto selezionato.']);
        }

        $settings = \App\Models\CompanySetting::where('user_id', Auth::id())->first();

        $pdf = Pdf::loadView('work_reports.pdf_list', compact('reports', 'settings'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Elenco_Documenti.pdf');
    }


    // Form di modifica (Edit)
    public function edit(WorkReport $workReport)
    {
        if ($workReport->user_id !== Auth::id()) abort(403);

        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();
        $materials = Material::where('user_id', Auth::id())->orderBy('description')->get();

        $existingRows = $workReport->rows->map(function($row) {
            return [
                'id' => $row->id,
                'material_id' => '', 
                'description' => $row->description,
                'quantity' => $row->quantity,
                'unit' => $row->unit,
                'price' => $row->price
            ];
        });

        return view('work_reports.edit', compact('workReport', 'clients', 'materials', 'existingRows'));
    }

    // Aggiornamento (Update)
    public function update(Request $request, WorkReport $workReport)
    {
        if ($workReport->user_id !== Auth::id()) abort(403);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'rows' => 'required|array|min:1',
            'rows.*.description' => 'required|string',
            'rows.*.quantity' => 'required|numeric',
            'rows.*.price' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $workReport) {
            $workReport->update([
                'client_id' => $request->client_id,
                'date' => $request->date,
                'notes' => $request->notes,
                'private_notes' => $request->private_notes,
            ]);

            $workReport->rows()->delete();

            foreach ($request->rows as $row) {
                $workReport->rows()->create([
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pz',
                    'price' => $row['price'],
                    'total' => $row['quantity'] * $row['price'],
                ]);
            }
        });

        return redirect()->route('work-reports.edit', $workReport)->with('success', 'Rapporto aggiornato! Controlla l\'anteprima.');
    }

    // --- FIRMA DIGITALE INTERNA (Pad su schermo Master) ---
    public function sign(Request $request, WorkReport $workReport)
    {
        $signatureBlob = $request->input('signature_data') ?? $request->input('signature');

        if (!$signatureBlob) {
            return back()->withErrors(['msg' => 'Errore: Nessuna firma ricevuta.']);
        }

        try {
            $image_parts = explode(";base64,", $signatureBlob);
            
            if(count($image_parts) < 2) {
                return back()->withErrors(['msg' => 'Formato firma non valido.']);
            }

            $image_base64 = base64_decode($image_parts[1]);
            $fileName = 'signatures/sign_' . $workReport->id . '_' . time() . '.png';
            
            Storage::disk('public')->put($fileName, $image_base64);

            $workReport->update([
                'customer_signature_path' => $fileName,
                'status' => 'closed'
            ]);

            return back()->with('success', 'Rapporto firmato e chiuso correttamente!');

        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Errore salvataggio: ' . $e->getMessage()]);
        }
    }

    // Generazione PDF Singolo
    public function streamPdf(Request $request, WorkReport $workReport)
    {
        if ($workReport->user_id !== Auth::id()) abort(403);

        $settings = CompanySetting::where('user_id', Auth::id())->first();
        $hidePrices = $request->has('hide_prices'); 

        $pdf = Pdf::loadView('work_reports.pdf', [
            'report' => $workReport,
            'settings' => $settings,
            'hidePrices' => $hidePrices
        ]);
        
        return $pdf->stream('rapporto_'.$workReport->number. ($hidePrices ? '_noprezzi' : '') .'.pdf');
    }

    // Conversione Singola in Fattura
    public function convertToInvoice(WorkReport $workReport)
    {
        if ($workReport->user_id !== Auth::id()) abort(403);

        DB::transaction(function () use ($workReport) {
            
            $year = date('Y');
            $lastInvoiceNumber = Invoice::where('user_id', Auth::id())
                ->whereYear('date', $year)
                ->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num')
                ->value('max_num') ?? 0;

            $nextNumber = $lastInvoiceNumber + 1;

            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'client_id' => $workReport->client_id,
                'number' => $nextNumber,
                'date' => now(),
                'status' => 'unpaid',
                'notes' => "Rif. Rapporto n. {$workReport->number} del " . \Carbon\Carbon::parse($workReport->date)->format('d/m/Y') . "\n\n" . $workReport->notes,
                'total_net' => 0, 'total_vat' => 0, 'total_gross' => 0
            ]);

            $totalNet = 0;

            foreach ($workReport->rows as $row) {
                $vatRate = 22; 
                $rowTotal = $row->total;
                
                $invoice->rows()->create([
                    'description' => $row->description,
                    'quantity' => $row->quantity,
                    'unit' => $row->unit,
                    'price' => $row->price,
                    'vat_rate' => $vatRate,
                    'total' => $rowTotal,
                ]);

                $totalNet += $rowTotal;
            }

            $totalVat = $totalNet * 0.22;
            $invoice->update([
                'total_net' => $totalNet,
                'total_vat' => $totalVat,
                'total_gross' => $totalNet + $totalVat
            ]);
        });

        $newInvoice = Invoice::where('user_id', Auth::id())->latest()->first();
        return redirect()->route('invoices.edit', $newInvoice)->with('success', 'Rapporto convertito in Fattura!');
    }

    public function destroy(WorkReport $workReport)
    {
        if ($workReport->user_id !== Auth::id()) abort(403);
        $workReport->delete();
        return redirect()->route('work-reports.index')->with('success', 'Rapporto eliminato correttamente.');
    }

    // --- METODI PER LA FIRMA ESTERNA (AGGIUNTI) ---

    // 1. Mostra il rapporto al cliente (Pubblico con codice)
    public function showExternal($code)
    {
        $report = WorkReport::where('unique_code', $code)->with('client', 'rows')->firstOrFail();

        // Se vuoi impedire la firma se è già chiuso, puoi aggiungere un controllo qui
        // Ma di solito lasciamo vedere il riepilogo anche se firmato.
        return view('work_reports.external_sign', compact('report'));
    }

    // 2. Salva la firma del cliente (Pubblico con codice)
    public function signExternal(Request $request, $code)
    {
        $report = WorkReport::where('unique_code', $code)->firstOrFail();

        $request->validate([
            'signer_name' => 'required|string|max:255',
            'acceptance' => 'accepted', // Checkbox obbligatoria
        ]);

        $report->update([
            'status' => 'closed',
            'notes' => $report->notes . "\n\n[FIRMATO DIGITALMENTE DA: " . $request->signer_name . " il " . now()->format('d/m/Y H:i') . "]",
        ]);

        return back()->with('success', 'Rapporto firmato correttamente. Grazie!');
    }
}