<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Panoramica') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-wrap gap-4 mb-8">
                <a href="{{ route('work-reports.create') }}" class="flex items-center gap-2 bg-gray-800 text-white px-5 py-3 rounded-lg shadow hover:bg-gray-700 transition transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuovo Rapporto
                </a>
                <a href="{{ route('quotes.create') }}" class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-5 py-3 rounded-lg shadow-sm hover:bg-gray-50 transition transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Nuovo Preventivo
                </a>
                <a href="{{ route('invoices.create') }}" class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-5 py-3 rounded-lg shadow-sm hover:bg-gray-50 transition transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Emetti Fattura
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-20 transform translate-x-2 -translate-y-2">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Da Incassare</h3>
                    <p class="text-3xl font-bold mt-1">€ {{ number_format($unpaidAmount, 2, ',', '.') }}</p>
                    <div class="mt-4 text-xs bg-white bg-opacity-20 inline-block px-2 py-1 rounded">Attenzione ai ritardi</div>
                </div>

                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-20 transform translate-x-2 -translate-y-2">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Rapporti (Mese)</h3>
                    <p class="text-3xl font-bold mt-1">{{ $reportsThisMonth }}</p>
                    <div class="mt-4 text-xs bg-white bg-opacity-20 inline-block px-2 py-1 rounded">Interventi effettuati</div>
                </div>

                <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-20 transform translate-x-2 -translate-y-2">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Preventivi Aperti</h3>
                    <p class="text-3xl font-bold mt-1">{{ $quotesPending }}</p>
                    <div class="mt-4 text-xs bg-white bg-opacity-20 inline-block px-2 py-1 rounded">In attesa risposta</div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-teal-500 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-20 transform translate-x-2 -translate-y-2">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Clienti Totali</h3>
                    <p class="text-3xl font-bold mt-1">{{ $clientsCount }}</p>
                    <div class="mt-4 text-xs bg-white bg-opacity-20 inline-block px-2 py-1 rounded">Anagrafica attiva</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl mb-8">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Panoramica Dipendenti
                    </h3>
                    <a href="{{ route('employees.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wide">Gestisci Personale &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase bg-white">Dipendente</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase bg-white">Stato Odierno</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase bg-white">Ultima Attività</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase bg-white">Prossime Ferie/Assenze</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($employees as $emp)
                                @php
                                    $lastAtt = $emp->attendances->first();
                                    $nextAbsence = $emp->absences->first();
                                    
                                    $isOnline = false;
                                    $statusLabel = 'ASSENTE / FUORI';
                                    $statusColor = 'bg-gray-100 text-gray-600 border border-gray-200';

                                    // Check Timbratura
                                    if ($lastAtt && $lastAtt->date->isToday()) {
                                        if ($lastAtt->clock_in && !$lastAtt->clock_out) {
                                            $isOnline = true;
                                            $statusLabel = 'IN SEDE';
                                            $statusColor = 'bg-green-100 text-green-700 border border-green-200';
                                        } elseif ($lastAtt->clock_out) {
                                            $statusLabel = 'TURNO FINITO';
                                            $statusColor = 'bg-blue-50 text-blue-700 border border-blue-100';
                                        }
                                    }

                                    // Check Assenza (Sovrascrive timbratura se oggi è ferie)
                                    if ($nextAbsence && $nextAbsence->start_date->lte(\Carbon\Carbon::today()) && $nextAbsence->end_date->gte(\Carbon\Carbon::today())) {
                                            $statusLabel = strtoupper($nextAbsence->type);
                                            $statusColor = 'bg-yellow-100 text-yellow-700 border border-yellow-200';
                                            $isOnline = false;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xs">
                                                {{ substr($emp->name, 0, 2) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-900">{{ $emp->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $emp->registration_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 inline-flex text-xs leading-4 font-bold rounded-full {{ $statusColor }}">
                                            @if($isOnline) <span class="w-2 h-2 bg-green-500 rounded-full mr-2 self-center animate-pulse"></span> @endif
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                        @if($lastAtt)
                                            @if($lastAtt->date->isToday())
                                                <span class="font-bold text-gray-800">Oggi</span>,
                                                @if($lastAtt->clock_out)
                                                    Uscita alle {{ $lastAtt->clock_out->format('H:i') }}
                                                @else
                                                    Ingresso alle {{ $lastAtt->clock_in->format('H:i') }}
                                                @endif
                                            @else
                                                {{ $lastAtt->date->format('d/m') }} - {{ $lastAtt->clock_out ? 'Turno completo' : 'Incompleto' }}
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic text-xs">Mai timbrato</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
                                        @if($nextAbsence && $nextAbsence->start_date->gt(\Carbon\Carbon::today()))
                                            <div class="text-gray-700 font-bold text-xs">{{ ucfirst($nextAbsence->type) }}</div>
                                            <div class="text-xs text-gray-500">Dal {{ $nextAbsence->start_date->format('d/m') }}</div>
                                        @else
                                            <span class="text-gray-300 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-400 text-sm">
                                        Nessun dipendente registrato.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-xl">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-lg">Ultimi Rapporti d'Intervento</h3>
                        <a href="{{ route('work-reports.index') }}" class="text-sm text-indigo-600 hover:underline">Vedi tutti</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-6 py-3 font-semibold text-gray-600">Data</th>
                                    <th class="px-6 py-3 font-semibold text-gray-600">Cliente</th>
                                    <th class="px-6 py-3 font-semibold text-gray-600">Stato</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReports as $report)
                                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                                        <td class="px-6 py-3">{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                                        <td class="px-6 py-3 font-medium">{{ $report->client->name }}</td>
                                        <td class="px-6 py-3">
                                            @if($report->status == 'closed')
                                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Chiuso</span>
                                            @else
                                                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Bozza</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <a href="{{ route('work-reports.edit', $report) }}" class="text-gray-400 hover:text-indigo-600">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-400">Nessun rapporto recente.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                    <div class="p-5 border-b border-gray-100 border-l-4 border-l-red-500">
                        <h3 class="font-bold text-gray-800 text-lg">Scadenziario Fatture</h3>
                        <p class="text-xs text-gray-500 mt-1">Pagamenti in attesa o scaduti</p>
                    </div>
                    <div class="p-4 space-y-4">
                        @forelse($unpaidInvoices as $invoice)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border {{ $invoice->status == 'overdue' ? 'border-red-200 bg-red-50' : 'border-gray-200' }}">
                                <div>
                                    <div class="font-bold text-gray-800">Fatt. #{{ $invoice->number }}</div>
                                    <div class="text-xs text-gray-500">{{ $invoice->client->name }}</div>
                                    <div class="text-xs font-medium {{ $invoice->status == 'overdue' ? 'text-red-600' : 'text-orange-600' }}">
                                        Scad. {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">€ {{ number_format($invoice->total_gross, 2, ',', '.') }}</div>
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-xs text-indigo-600 hover:underline">Vedi</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p>Ottimo! Nessuna fattura in sospeso.</p>
                            </div>
                        @endforelse
                    </div>
                    @if($unpaidInvoices->isNotEmpty())
                        <div class="bg-gray-50 px-4 py-3 text-center border-t border-gray-100">
                            <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase tracking-wide">Vedi tutti i sospesi</a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>