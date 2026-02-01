<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Material::where('user_id', \Illuminate\Support\Facades\Auth::id());

        // 1. LOGICA DI RICERCA
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // 2. ORDINAMENTO (Opzionale, ma utile)
        $sort = $request->get('sort', 'description'); // Default: descrizione
        $direction = $request->get('direction', 'asc'); // Default: A-Z
        $query->orderBy($sort, $direction);

        // 3. PAGINAZIONE
        $materials = $query->paginate(15)->withQueryString(); // Mantiene i filtri tra le pagine

        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        return view('materials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'unit' => 'required|string|max:10', // es. pz, h, kg
            'price' => 'required|numeric|min:0',
        ]);

        Material::create([
            'user_id' => Auth::id(),
            'code' => $request->code,
            'description' => $request->description,
            'unit' => $request->unit,
            'price' => $request->price,
        ]);

        return redirect()->route('materials.index')->with('success', 'Materiale aggiunto!');
    }

    public function edit(Material $material)
    {
        if ($material->user_id !== Auth::id()) abort(403);
        return view('materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        if ($material->user_id !== Auth::id()) abort(403);

        $request->validate([
            'description' => 'required|string|max:255',
            'unit' => 'required|string|max:10',
            'price' => 'required|numeric|min:0',
        ]);

        $material->update($request->all());

        return redirect()->route('materials.index')->with('success', 'Materiale aggiornato!');
    }

    public function destroy(Material $material)
    {
        if ($material->user_id !== Auth::id()) abort(403);
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Materiale eliminato.');
    }

    // 1. MOSTRA FORM DI UPLOAD
    public function showImportForm()
    {
        return view('materials.import');
    }

    // 2. ANALIZZA CSV E MOSTRA PAGINA DI MAPPATURA
    public function parseImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        
        // Salva il file nella cartella 'temp' dello storage predefinito
        $relativePath = $file->store('temp');
        
        // CORREZIONE: Recuperiamo il percorso assoluto usando la Facade Storage
        $absolutePath = \Illuminate\Support\Facades\Storage::path($relativePath);

        $headers = [];
        
        // Usiamo il percorso assoluto sicuro
        if (($handle = fopen($absolutePath, "r")) !== FALSE) {
            if (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // Se c'è solo una colonna, riprova con la virgola
                if(count($data) == 1) { 
                    rewind($handle);
                    $data = fgetcsv($handle, 1000, ",");
                }
                $headers = $data;
            }
            fclose($handle);
        }

        $db_fields = [
            'description' => 'Descrizione Materiale (Obbligatorio)',
            'price' => 'Prezzo Unitario',
            'unit' => 'Unità di Misura (pz, h, kg)',
            'code' => 'Codice Articolo (Opzionale)',
        ];

        // Passiamo il path relativo alla vista (perché Storage::path funziona col relativo)
        return view('materials.import_map', [
            'headers' => $headers,
            'path' => $relativePath, // Passiamo il path "temp/nomefile.csv"
            'db_fields' => $db_fields
        ]);
    }

    // 3. ESEGUE L'IMPORTAZIONE REALE
    public function processImport(Request $request)
    {
        $request->validate([
            'csv_path' => 'required',
            'fields' => 'required|array',
        ]);

        // CORREZIONE: Ottieni il percorso assoluto sicuro dal path relativo
        $absolutePath = \Illuminate\Support\Facades\Storage::path($request->csv_path);
        
        $mapping = $request->fields;

        if (file_exists($absolutePath) && ($handle = fopen($absolutePath, "r")) !== FALSE) {
            
            // Determina il delimitatore
            $delimiter = ";";
            $firstLine = fgets($handle);
            if ($firstLine && substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
                $delimiter = ",";
            }
            rewind($handle);

            $header = fgetcsv($handle, 1000, $delimiter); // Salta intestazione

            $count = 0;
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                
                $descIndex = $mapping['description'] ?? null;
                $priceIndex = $mapping['price'] ?? null;
                $unitIndex = $mapping['unit'] ?? null;
                $codeIndex = $mapping['code'] ?? null;

                $description = isset($row[$descIndex]) ? $row[$descIndex] : null;
                
                if ($description) {
                    $price = 0;
                    if(isset($row[$priceIndex])) {
                        $priceStr = str_replace('€', '', $row[$priceIndex]);
                        $priceStr = str_replace('.', '', $priceStr);
                        $priceStr = str_replace(',', '.', $priceStr);
                        $price = floatval($priceStr);
                    }

                    \App\Models\Material::updateOrCreate(
                        [
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                            'description' => $description
                        ],
                        [
                            'price' => $price,
                            'unit' => isset($row[$unitIndex]) ? $row[$unitIndex] : 'pz',
                            'code' => isset($row[$codeIndex]) ? $row[$codeIndex] : null,
                        ]
                    );
                    $count++;
                }
            }
            fclose($handle);
            
            // Cancella file temp usando il path relativo
            \Illuminate\Support\Facades\Storage::delete($request->csv_path);
        }

        return redirect()->route('materials.index')->with('success', "Importazione completata! Caricati $count materiali.");
    }

    // --- AZIONE 1: ELIMINAZIONE DI MASSA ---
    public function bulkDestroy(Request $request)
    {
        $request->validate(['material_ids' => 'required|array']);
        
        // Elimina i materiali selezionati
        \App\Models\Material::whereIn('id', $request->material_ids)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->delete();

        return back()->with('success', 'Materiali eliminati correttamente.');
    }

    // --- AZIONE 2: GENERA DOCUMENTO DA MATERIALI ---
    // --- STEP 1: MOSTRA PAGINA SCELTA CLIENTE ---
    public function showClientSelection(Request $request)
    {
        $request->validate([
            'material_ids' => 'required|array',
            'doc_type' => 'required|in:report,quote,invoice',
        ]);

        // Recupera i materiali per mostrarli nel riepilogo (opzionale, ma bello)
        $materials = \App\Models\Material::whereIn('id', $request->material_ids)->get();
        
        // Recupera la lista clienti ordinata
        $clients = \App\Models\Client::where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->orderBy('name')
                    ->get();

        if ($clients->isEmpty()) {
            return back()->withErrors(['msg' => 'Non hai ancora creato nessun cliente. Creane uno prima di generare documenti.']);
        }

        // Passiamo i dati alla vista intermedia
        return view('materials.select_client', [
            'materials' => $materials,
            'clients' => $clients,
            'doc_type' => $request->doc_type,
            'material_ids' => $request->material_ids // Passiamo gli ID per reinviarli dopo
        ]);
    }

    // --- STEP 2: CREA IL DOCUMENTO FINALE ---
    /**
     * Crea effettivamente il documento (Fattura, Preventivo o Rapporto)
     * dopo che l'utente ha selezionato il cliente.
     */
    public function createDocumentFromMaterials(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'material_ids' => 'required|array',
            'doc_type' => 'required|in:report,quote,invoice',
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        $materials = \App\Models\Material::whereIn('id', $request->material_ids)->get();
        $client = \App\Models\Client::find($request->client_id);
        $year = date('Y');

        // Determina il Modello e calcola il numero corretto (Bug del 10 risolto)
        $modelClass = match($request->doc_type) {
            'invoice' => \App\Models\Invoice::class,
            'quote' => \App\Models\Quote::class,
            'report' => \App\Models\WorkReport::class,
        };

        // Calcolo numero matematico massimo
        $lastNumber = $modelClass::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num')
            ->value('max_num') ?? 0;

        $nextNumber = $lastNumber + 1;

        // Esecuzione in transazione per sicurezza
        $newDoc = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user, $client, $nextNumber, $materials) {
            
            $doc = null;
            $rowModel = '';
            $fk = '';

            if ($request->doc_type === 'invoice') {
                $doc = \App\Models\Invoice::create([
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'number' => $nextNumber,
                    'date' => now(),
                    'status' => 'draft',
                    'notes' => 'Generata da selezione materiali listino.',
                    'total_net' => 0, 'total_vat' => 0, 'total_gross' => 0
                ]);
                $rowModel = \App\Models\InvoiceRow::class;
                $fk = 'invoice_id';

            } elseif ($request->doc_type === 'quote') {
                $doc = \App\Models\Quote::create([
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'number' => $nextNumber,
                    'date' => now(),
                    'status' => 'draft',
                    'notes' => 'Generato da selezione materiali listino.',
                    'total_net' => 0, 'total_vat' => 0, 'total_gross' => 0
                ]);
                $rowModel = \App\Models\QuoteRow::class;
                $fk = 'quote_id';

            } elseif ($request->doc_type === 'report') {
                $doc = \App\Models\WorkReport::create([
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'number' => $nextNumber,
                    'date' => now(),
                    'status' => 'draft',
                    'unique_code' => strtoupper(\Illuminate\Support\Str::random(8)),
                    'notes' => 'Generato da selezione materiali listino.',
                ]);
                $rowModel = \App\Models\WorkReportRow::class;
                $fk = 'work_report_id';
            }

            // Inserimento delle righe
            $totalNet = 0;
            foreach ($materials as $mat) {
                $rowModel::create([
                    $fk => $doc->id,
                    'description' => $mat->description,
                    'quantity' => 1,
                    'unit' => $mat->unit ?? 'pz',
                    'price' => $mat->price,
                    'total' => $mat->price,
                    'vat_rate' => 22,
                ]);
                $totalNet += $mat->price;
            }

            // Aggiornamento totali
            if (in_array($request->doc_type, ['invoice', 'quote'])) {
                $totalVat = $totalNet * 0.22;
                $doc->update([
                    'total_net' => $totalNet,
                    'total_vat' => $totalVat,
                    'total_gross' => $totalNet + $totalVat
                ]);
            }

            return $doc;
        });

        $editRoute = match($request->doc_type) {
            'invoice' => 'invoices.edit',
            'quote' => 'quotes.edit',
            'report' => 'work-reports.edit',
        };

        return redirect()->route($editRoute, $newDoc->id)->with('success', 'Documento #' . $nextNumber . ' creato con successo per ' . $client->name);
    }
}