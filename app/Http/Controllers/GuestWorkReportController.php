<?php

namespace App\Http\Controllers;

use App\Models\WorkReport;
use App\Models\CompanySetting; // <--- Importante per l'intestazione PDF
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; // <--- Importante per il PDF

class GuestWorkReportController extends Controller
{
    // 1. Cerca il rapporto dal codice inserito nella Welcome Page
    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Cerca per codice univoco (o numero, se preferisci, ma univoco è più sicuro)
        $report = WorkReport::where('unique_code', $request->code)->first();

        if (!$report) {
            return back()->withErrors(['code' => 'Nessun rapporto trovato con questo codice.']);
        }

        return redirect()->route('guest.report.show', $report->unique_code);
    }

    // 2. Mostra il rapporto al cliente
    public function show($code)
    {
        $report = WorkReport::where('unique_code', $code)->with('client', 'rows')->firstOrFail();
        return view('guest.work_report', compact('report'));
    }

    // 3. Salva la firma del cliente
    public function sign(Request $request, $code)
    {
        $report = WorkReport::where('unique_code', $code)->firstOrFail();

        // Validazione: Accettiamo o check "acceptance" o firma disegnata "signature"
        $request->validate([
            'signer_name' => 'required|string|max:255',
        ]);

        // CASO A: Firma Disegnata (Canvas)
        if ($request->filled('signature')) {
            try {
                $image_parts = explode(";base64,", $request->signature);
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'signatures/sign_' . $report->id . '_' . time() . '.png';
                Storage::disk('public')->put($fileName, $image_base64);
                
                $report->customer_signature_path = $fileName;
            } catch (\Exception $e) {
                return back()->withErrors(['msg' => 'Errore nel salvataggio della firma.']);
            }
        } 
        // CASO B: Checkbox Semplice (se non disegnano)
        elseif (!$request->has('acceptance')) {
            return back()->withErrors(['msg' => 'Devi firmare o accettare il rapporto.']);
        }

        $report->update([
            'status' => 'closed',
            'notes' => $report->notes . "\n\n[FIRMATO DIGITALMENTE DA: " . $request->signer_name . " il " . now()->format('d/m/Y H:i') . "]",
        ]);

        return back()->with('success', 'Rapporto firmato correttamente! Puoi chiudere questa pagina.');
    }

    // 4. Scarica PDF (Pubblico, Senza Prezzi)
    public function downloadPdf($code)
    {
        $report = WorkReport::where('unique_code', $code)->firstOrFail();

        // Recuperiamo i settings dell'azienda (l'utente proprietario del rapporto)
        $settings = CompanySetting::where('user_id', $report->user_id)->first();

        // Forziamo il nascondere i prezzi
        $hidePrices = true; 

        // Riutilizziamo la stessa vista PDF dell'area amministrativa
        $pdf = Pdf::loadView('work_reports.pdf', [
            'report' => $report,
            'settings' => $settings,
            'hidePrices' => $hidePrices
        ]);
        
        return $pdf->download('Rapporto_' . $report->number . '_Firmato.pdf');
    }
}
