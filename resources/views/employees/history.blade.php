<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestione Presenze: <span class="text-indigo-600">{{ $employee->name }}</span>
            </h2>
            <a href="{{ route('employees.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md font-bold text-sm">Indietro</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-orange-400">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Registra Assenza / Ferie</h3>
                <form action="{{ route('employees.absence.store', $employee) }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                    @csrf
                    
                    <div class="w-full md:w-1/5">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Tipo</label>
                        <select name="type" class="w-full rounded border-gray-300 text-sm">
                            <option value="ferie">🏖️ Ferie</option>
                            <option value="malattia">🤒 Malattia</option>
                            <option value="ingiustificata">⚠️ Assenza Ingiustificata</option>
                        </select>
                    </div>

                    <div class="w-full md:w-1/5">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Dal</label>
                        <input type="date" name="start_date" class="w-full rounded border-gray-300 text-sm" required>
                    </div>

                    <div class="w-full md:w-1/5">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Al</label>
                        <input type="date" name="end_date" class="w-full rounded border-gray-300 text-sm" required>
                    </div>

                    <div class="w-full md:w-1/5">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Stato Iniziale</label>
                        <select name="status" class="w-full rounded border-gray-300 text-sm">
                            <option value="approved">✅ Approvata</option>
                            <option value="pending">⏳ In Attesa</option>
                        </select>
                    </div>

                    <div class="w-full md:w-1/5">
                        <button type="submit" class="w-full bg-orange-500 text-white font-bold py-2 rounded hover:bg-orange-600 text-sm">
                            Salva Assenza
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-gray-50 border-b border-gray-200 font-bold text-gray-700 flex justify-between">
                        <span>Storico Assenze & Ferie</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Tipo</th>
                                    <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Periodo</th>
                                    <th class="px-4 py-2 text-center text-[10px] font-bold text-gray-500 uppercase">Stato Decisione</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($absences as $absence)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            @if($absence->type == 'ferie')
                                                <span class="px-2 text-[10px] font-bold rounded-full bg-green-100 text-green-800">FERIE</span>
                                            @elseif($absence->type == 'malattia')
                                                <span class="px-2 text-[10px] font-bold rounded-full bg-red-100 text-red-800">MALATTIA</span>
                                            @else
                                                <span class="px-2 text-[10px] font-bold rounded-full bg-gray-100 text-gray-800">ASSENZA</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="font-bold text-gray-700">{{ $absence->start_date->format('d/m') }} - {{ $absence->end_date->format('d/m/y') }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $absence->start_date->diffInDays($absence->end_date) + 1 }} giorni</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <form action="{{ route('employees.absence.update', $absence->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="text-[10px] font-bold rounded-lg border-gray-200 w-full py-1 {{ $absence->status == 'approved' ? 'text-green-700 bg-green-50' : ($absence->status == 'rejected' ? 'text-red-700 bg-red-50' : 'text-yellow-700 bg-yellow-50') }}">
                                                    <option value="pending" {{ $absence->status == 'pending' ? 'selected' : '' }}>⏳ IN ATTESA</option>
                                                    <option value="approved" {{ $absence->status == 'approved' ? 'selected' : '' }}>✅ APPROVATA</option>
                                                    <option value="rejected" {{ $absence->status == 'rejected' ? 'selected' : '' }}>❌ RIFIUTATA</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <form action="{{ route('absences.destroy', $absence->id) }}" method="POST" onsubmit="return confirm('Eliminare questa assenza?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-300 hover:text-red-600 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="p-4 text-sm text-gray-500 text-center italic">Nessuna richiesta o assenza registrata.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-gray-50 border-b border-gray-200 font-bold text-gray-700">
                        Storico Timbrature
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ingresso</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Uscita</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Totale</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($attendances as $att)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                            {{ $att->date->format('d/m/Y') }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="text-green-700 font-bold">
                                                    {{ $att->clock_in ? $att->clock_in->format('H:i') : '--:--' }}
                                                </span>
                                                @if($att->location_in)
                                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $att->location_in }}" target="_blank" class="text-gray-400 hover:text-indigo-600 transition" title="Vedi posizione ingresso">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center gap-2">
                                                @if($att->clock_out)
                                                    <span class="text-red-700 font-bold">
                                                        {{ $att->clock_out->format('H:i') }}
                                                    </span>
                                                    @if($att->location_out)
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $att->location_out }}" target="_blank" class="text-gray-400 hover:text-indigo-600 transition" title="Vedi posizione uscita">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-xs font-semibold text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full animate-pulse">In Corso</span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-600">
                                            @if($att->clock_in && $att->clock_out)
                                                {{ $att->clock_in->diff($att->clock_out)->format('%H:%I') }} h
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="p-6 text-sm text-gray-500 text-center">Nessuna timbratura registrata.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-200">
                        {{ $attendances->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>