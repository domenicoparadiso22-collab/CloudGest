<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Archivio Rapporti d\'Intervento') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('work-reports.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuovo Rapporto
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ selected: [], allSelected: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                <form action="{{ route('work-reports.index') }}" method="GET" class="flex flex-col xl:flex-row gap-4 items-end">
                    
                    <div class="flex-grow w-full">
                        <label class="text-xs font-bold text-gray-500 uppercase">Cerca</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cliente, Numero, Note..." class="w-full text-sm rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 h-10">
                    </div>

                    <div class="w-full md:w-40">
                        <label class="text-xs font-bold text-gray-500 uppercase">Stato</label>
                        <select name="status" class="w-full text-sm rounded border-gray-300 focus:border-indigo-500 h-10">
                            <option value="">Tutti</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bozza</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Chiuso</option>
                        </select>
                    </div>

                    <div class="w-full md:w-40">
                        <label class="text-xs font-bold text-gray-500 uppercase">Ordina per</label>
                        <select name="sort" class="w-full text-sm rounded border-gray-300 focus:border-indigo-500 h-10">
                            <option value="date" {{ request('sort') == 'date' ? 'selected' : '' }}>Data</option>
                            <option value="number" {{ request('sort') == 'number' ? 'selected' : '' }}>Numero</option>
                            <option value="client" {{ request('sort') == 'client' ? 'selected' : '' }}>Cliente</option>
                        </select>
                    </div>

                    <div class="w-full md:w-32">
                        <label class="text-xs font-bold text-gray-500 uppercase">Ordine</label>
                        <select name="direction" class="w-full text-sm rounded border-gray-300 focus:border-indigo-500 h-10">
                            <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Decrescente</option>
                            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Crescente</option>
                        </select>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-700 text-sm font-bold h-10 w-full md:w-auto flex justify-center items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Cerca
                        </button>

                        @if(request('search') || request('status'))
                            <a href="{{ route('work-reports.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm font-bold h-10 flex justify-center items-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div x-show="selected.length > 0" 
                 x-transition
                 style="display: none;"
                 class="bg-indigo-50 border border-indigo-200 p-4 rounded-lg shadow-sm mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="font-bold text-indigo-800 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span x-text="selected.length"></span> Rapporti selezionati
                </div>

                <div class="flex flex-wrap gap-2 justify-center">
                    <span class="text-xs font-bold text-gray-500 uppercase self-center mr-2">Genera Documenti:</span>
                    
                    <button form="bulk-form" 
                            type="submit"
                            formaction="{{ route('work-reports.bulk-convert') }}" 
                            class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-xs font-bold uppercase tracking-wide flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Fattura
                    </button>

                    <button form="bulk-form" 
                            type="submit"
                            formaction="{{ route('work-reports.bulk-pdf-list') }}"
                            class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 text-xs font-bold uppercase tracking-wide flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Elenco PDF
                    </button>
                    
                    </div>
            </div>

            <form id="bulk-form" method="POST" onsubmit="return confirm('Procedere con l\'azione selezionata per i rapporti scelti?');">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left w-10">
                                        <input type="checkbox" 
                                               @click="allSelected = !allSelected; if(allSelected) { selected = {{ json_encode($reports->pluck('id')) }}; } else { selected = []; }"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Codice Cliente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numero</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note Int.</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($reports as $report)
                                    <tr class="hover:bg-gray-50 transition duration-150" :class="selected.includes('{{ $report->id }}') ? 'bg-indigo-50' : ''">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="report_ids[]" value="{{ $report->id }}" x-model="selected"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($report->unique_code)
                                                <div class="flex flex-col">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded bg-purple-50 text-purple-700 font-mono border border-purple-100 select-all cursor-text" title="Comunica questo codice al cliente">
                                                        {{ $report->unique_code }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-400 mt-1">Per firma esterna</span>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Non generato</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                            #{{ $report->number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                            {{ $report->client->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $report->private_notes }}">
                                            {{ \Illuminate\Support\Str::limit($report->private_notes, 20, '...') ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($report->status == 'draft')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Bozza</span>
                                            @elseif($report->status == 'closed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Chiuso</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('work-reports.edit', $report) }}" class="text-indigo-600 hover:text-indigo-900 font-bold mr-3">Apri</a>
                                            <a href="{{ route('work-reports.pdf', $report) }}" target="_blank" class="text-gray-500 hover:text-gray-900 mr-3">PDF</a>
                                            <button type="button" @click.prevent="if(confirm('Eliminare questo rapporto?')) document.getElementById('delete-form-{{ $report->id }}').submit()" class="text-red-600 hover:text-red-900 font-bold">X</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Nessun rapporto trovato.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $reports->links() }}
                    </div>
                </div>
            </form>

            @foreach($reports as $report)
                <form id="delete-form-{{ $report->id }}" action="{{ route('work-reports.destroy', $report) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

        </div>
    </div>
</x-app-layout>