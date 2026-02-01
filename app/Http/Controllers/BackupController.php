<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    // --- 1. LISTA BACKUP ---
    public function index()
    {
        $diskName = config('backup.backup.destination.disks')[0];
        $disk = Storage::disk($diskName);
        $files = $disk->files(config('backup.backup.name'));
        
        $backups = [];
        
        foreach ($files as $f) {
            if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                $backups[] = [
                    'file_path' => $f,
                    'file_name' => str_replace(config('backup.backup.name') . '/', '', $f),
                    'file_size' => $this->humanFilesize($disk->size($f)),
                    'last_modified' => Carbon::createFromTimestamp($disk->lastModified($f))->format('d/m/Y H:i:s'),
                    'timestamp' => $disk->lastModified($f),
                ];
            }
        }

        $backups = collect($backups)->sortByDesc('timestamp')->values()->all();

        return view('backups.index', compact('backups'));
    }

    // --- 2. CREA BACKUP MANUALE ---
    public function create()
    {
        try {
            Artisan::call('backup:run --only-db'); // Facciamo solo DB per velocità, togli --only-db per tutto
            return redirect()->back()->with('success', 'Backup creato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Errore: ' . $e->getMessage()]);
        }
    }

    // --- 3. SCARICA BACKUP ---
    public function download(Request $request)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $fileName = config('backup.backup.name') . '/' . $request->file_name;

        if ($disk->exists($fileName)) {
            return $disk->download($fileName);
        }
        return redirect()->back()->withErrors(['msg' => 'File non trovato.']);
    }

    // --- 4. ELIMINA BACKUP ---
    public function delete(Request $request)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $fileName = config('backup.backup.name') . '/' . $request->file_name;

        if ($disk->exists($fileName)) {
            $disk->delete($fileName);
            return redirect()->back()->with('success', 'Backup eliminato.');
        }
        return redirect()->back()->withErrors(['msg' => 'File non trovato.']);
    }

    // --- 5. UPLOAD (CARICAMENTO DA PC) ---
    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip',
        ]);

        $file = $request->file('backup_file');
        $filename = $file->getClientOriginalName();
        
        // Salva nella cartella dei backup
        $path = config('backup.backup.name'); 
        
        Storage::disk(config('backup.backup.destination.disks')[0])
                ->putFileAs($path, $file, $filename);

        return redirect()->back()->with('success', 'Backup caricato correttamente in lista!');
    }

    // --- 6. RESTORE (RIPRISTINO DB) ---
    public function restore(Request $request)
    {
        $fileName = $request->file_name;
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $fullPath = config('backup.backup.name') . '/' . $fileName;

        if (!$disk->exists($fullPath)) {
            return redirect()->back()->withErrors(['msg' => 'File di backup non trovato.']);
        }

        // Percorso assoluto file zip
        $zipPath = $disk->path($fullPath);
        
        // Cartella temporanea per estrazione
        $tempPath = storage_path('app/temp_restore');
        if (!is_dir($tempPath)) mkdir($tempPath, 0777, true);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($tempPath);
            $zip->close();
            
            // Cerca il file .sql dentro la cartella db-dumps
            $sqlFile = glob($tempPath . '/db-dumps/*.sql')[0] ?? null;

            if ($sqlFile && file_exists($sqlFile)) {
                try {
                    // Svuota il DB attuale (SQLite) e ripristina
                    // Nota: Per SQLite, cancellare le tabelle o sovrascrivere il file è rischioso mentre è in uso.
                    // Usiamo DB::unprepared per eseguire il dump SQL.
                    
                    DB::disableQueryLog();
                    
                    // 1. Wipe database (elimina tutte le tabelle esistenti)
                    $this->wipeDatabase();

                    // 2. Importa SQL
                    $sql = file_get_contents($sqlFile);
                    DB::unprepared($sql);
                    
                    // Pulizia
                    $this->deleteDirectory($tempPath);

                    return redirect()->back()->with('success', 'Database ripristinato con successo! Fai logout e login per sicurezza.');

                } catch (\Exception $e) {
                    $this->deleteDirectory($tempPath);
                    return redirect()->back()->withErrors(['msg' => 'Errore durante il ripristino SQL: ' . $e->getMessage()]);
                }
            } else {
                $this->deleteDirectory($tempPath);
                return redirect()->back()->withErrors(['msg' => 'Nessun file SQL trovato nel backup (db-dumps).']);
            }
        } else {
            return redirect()->back()->withErrors(['msg' => 'Impossibile aprire il file ZIP.']);
        }
    }

    // Helper per pulire cartelle
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }

    // Helper per svuotare DB SQLite
    private function wipeDatabase() {
        // Disabilita vincoli foreign key per poter cancellare
        DB::statement('PRAGMA foreign_keys = OFF;');
        
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
        foreach($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table->name}");
        }
        
        DB::statement('PRAGMA foreign_keys = ON;');
    }

    private function humanFilesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}