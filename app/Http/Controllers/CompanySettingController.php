<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CompanySettingController extends Controller
{
    public function edit()
    {
        // Trova i settaggi dell'utente o ne crea uno vuoto in memoria
        $settings = CompanySetting::firstOrCreate(['user_id' => Auth::id()]);
        return view('company.edit', compact('settings'));
    }

    public function update(Request $request)
{
    // 1. Validazione
    $request->validate([
        'company_name' => 'required|string|max:255',
        'subtitle'     => 'nullable|string|max:255',
        'email'        => 'nullable|email',
        'logo'         => 'nullable|image|max:2048', // Max 2MB
        'stamp'        => 'nullable|image|max:2048',
    ]);

    $user = Auth::user();
    
    // 2. Trova o crea il record
    $settings = CompanySetting::firstOrCreate(['user_id' => $user->id]);

    // 3. Prepara i dati (escludiamo i file per gestirli a parte)
    $data = $request->except(['logo', 'stamp', '_token']);

    // 4. Gestione upload Logo
    if ($request->hasFile('logo')) {
        // Cancella vecchio logo se esiste
        if ($settings->logo_path) Storage::disk('public')->delete($settings->logo_path);
        $data['logo_path'] = $request->file('logo')->store('logos', 'public');
    }

    // 5. Gestione upload Timbro
    if ($request->hasFile('stamp')) {
        if ($settings->stamp_path) Storage::disk('public')->delete($settings->stamp_path);
        $data['stamp_path'] = $request->file('stamp')->store('stamps', 'public');
    }

    // 6. Salvataggio effettivo
    $settings->update($data);

    return redirect()->back()->with('success', 'Dati salvati correttamente!');
}
}