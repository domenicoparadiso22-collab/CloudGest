<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    // Mostra la lista dei clienti
    public function index(Request $request)
    {
        $query = \App\Models\Client::where('user_id', Auth::id());

        // RICERCA
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        // ORDINAMENTO
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $clients = $query->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    // Mostra il form di creazione
    public function create()
    {
        return view('clients.create');
    }

    // Salva il nuovo cliente
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'client_stamp' => 'nullable|image|max:2048', // Validazione Timbro
            'fiscal_code' => 'nullable|string|max:16',
        ]);

        $data = $request->except('client_stamp');
        $data['user_id'] = Auth::id(); // Associa al Master loggato

        // Caricamento Timbro
        if ($request->hasFile('client_stamp')) {
            $data['client_stamp_path'] = $request->file('client_stamp')->store('client_stamps', 'public');
        }

        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Cliente creato con successo!');
    }

    // Mostra il form di modifica
    public function edit(Client $client)
    {
        // Sicurezza: controlla che il cliente appartenga all'utente
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }
        return view('clients.edit', compact('client'));
    }

    // Aggiorna il cliente
    public function update(Request $request, Client $client)
    {
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'client_stamp' => 'nullable|image|max:2048',
            'fiscal_code' => 'nullable|string|max:16',
        ]);

        $data = $request->except('client_stamp');

        // Aggiornamento Timbro
        if ($request->hasFile('client_stamp')) {
            // Cancella vecchio timbro se esiste
            if ($client->client_stamp_path) {
                Storage::disk('public')->delete($client->client_stamp_path);
            }
            $data['client_stamp_path'] = $request->file('client_stamp')->store('client_stamps', 'public');
        }

        $client->update($data);

        return redirect()->route('clients.index')->with('success', 'Cliente aggiornato!');
    }

    // Cancella il cliente
    public function destroy(Client $client)
    {
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }

        // Cancella anche il file del timbro se esiste
        if ($client->client_stamp_path) {
            Storage::disk('public')->delete($client->client_stamp_path);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente eliminato.');
    }

    // --- 1. MOSTRA FORM IMPORTAZIONE ---
    public function showImportForm()
    {
        return view('clients.import');
    }

    // --- 2. ANALIZZA CSV E MOSTRA MAPPATURA ---
    public function parseImport(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);
        
        $file = $request->file('csv_file');
        $relativePath = $file->store('temp');
        $absolutePath = Storage::path($relativePath);

        $headers = [];
        if (($handle = fopen($absolutePath, "r")) !== FALSE) {
            if (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if(count($data) == 1) { 
                    rewind($handle);
                    $data = fgetcsv($handle, 1000, ",");
                }
                $headers = $data;
            }
            fclose($handle);
        }

        // Campi del DB Clienti
        $db_fields = [
            'name' => 'Ragione Sociale / Nome (Obbligatorio)',
            'vat_number' => 'Partita IVA',
            'fiscal_code' => 'Codice Fiscale',
            'email' => 'Email',
            'phone' => 'Telefono',
            'address' => 'Indirizzo Completo',
            'city' => 'Città',
            'postal_code' => 'CAP',
            'sdi_code' => 'Codice SDI/Univoco'
        ];

        return view('clients.import_map', [
            'headers' => $headers,
            'path' => $relativePath,
            'db_fields' => $db_fields
        ]);
    }

    // --- 3. PROCESSA IMPORTAZIONE ---
    public function processImport(Request $request)
    {
        $request->validate(['csv_path' => 'required', 'fields' => 'required|array']);

        $absolutePath = Storage::path($request->csv_path);
        $mapping = $request->fields;
        $count = 0;

        if (file_exists($absolutePath) && ($handle = fopen($absolutePath, "r")) !== FALSE) {
            // Rileva delimitatore
            $delimiter = ";";
            $firstLine = fgets($handle);
            if ($firstLine && substr_count($firstLine, ',') > substr_count($firstLine, ';')) $delimiter = ",";
            rewind($handle);

            fgetcsv($handle, 1000, $delimiter); // Salta header

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                // Recupera dati tramite mappatura
                $data = [];
                foreach($mapping as $dbField => $csvIndex) {
                    $data[$dbField] = (isset($csvIndex) && isset($row[$csvIndex])) ? trim($row[$csvIndex]) : null;
                }

                if (!empty($data['name'])) {
                    \App\Models\Client::updateOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'vat_number' => $data['vat_number'] // Usa P.IVA per evitare duplicati (o usa 'name' se preferisci)
                        ],
                        array_merge($data, ['user_id' => Auth::id()])
                    );
                    $count++;
                }
            }
            fclose($handle);
            Storage::delete($request->csv_path);
        }

        return redirect()->route('clients.index')->with('success', "Importazione completata! Caricati $count clienti.");
    }

    // --- 4. ELIMINAZIONE DI MASSA ---
    public function bulkDestroy(Request $request)
    {
        $request->validate(['client_ids' => 'required|array']);
        
        \App\Models\Client::whereIn('id', $request->client_ids)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Clienti eliminati correttamente.');
    }

    // --- 5. GENERA DOCUMENTI PER CLIENTI SELEZIONATI ---
    public function bulkToDocument(Request $request)
{
    $request->validate([
        'client_ids' => 'required|array',
        'doc_type' => 'required|in:report,quote,invoice',
    ]);

    $clients = \App\Models\Client::whereIn('id', $request->client_ids)->where('user_id', Auth::id())->get();
    
    // 1. Recuperiamo il punto di partenza matematico (il bug del 10 risolto col CAST)
    $year = date('Y');
    $modelClass = $this->getDocModel($request->doc_type); // Funzione di supporto sotto
    
    $lastNumber = $modelClass::where('user_id', Auth::id())
        ->whereYear('date', $year)
        ->selectRaw('MAX(CAST(number AS INTEGER)) as max_num')
        ->value('max_num') ?? 0;

    // 2. Inizializziamo un contatore che incrementeremo manualmente nel ciclo
    $nextNumCounter = $lastNumber + 1;

    $count = 0;
    $route = '';

    foreach ($clients as $client) {
        if ($request->doc_type === 'invoice') {
            $doc = \App\Models\Invoice::create([
                'user_id' => Auth::id(),
                'client_id' => $client->id,
                'number' => $nextNumCounter, // Usiamo il contatore aggiornato
                'date' => now(),
                'status' => 'draft',
                'total_net' => 0, 'total_vat' => 0, 'total_gross' => 0
            ]);
            $route = 'invoices.index';
        } elseif ($request->doc_type === 'quote') {
            $doc = \App\Models\Quote::create([
                'user_id' => Auth::id(),
                'client_id' => $client->id,
                'number' => $nextNumCounter, // Usiamo il contatore aggiornato
                'date' => now(),
                'status' => 'draft'
            ]);
            $route = 'quotes.index';
        }
        // ... aggiungi report se serve ...

        // 3. INCREMENTIAMO IL CONTATORE PER IL PROSSIMO DOCUMENTO
        $nextNumCounter++; 
        $count++;
    }

    return redirect()->route($route)->with('success', "Generati $count documenti con numerazione progressiva.");
}

// Funzione di supporto per identificare il modello
private function getDocModel($type) {
    return match($type) {
        'invoice' => \App\Models\Invoice::class,
        'quote' => \App\Models\Quote::class,
        'report' => \App\Models\WorkReport::class,
    };
}
}