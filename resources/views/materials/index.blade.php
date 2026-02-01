<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listino Materiali/Servizi') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('materials.import') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Carica CSV
                </a>
                <a href="{{ route('materials.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuovo Articolo
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

            <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                <form action="{{ route('materials.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    
                    <div class="flex-grow w-full">
                        <label class="text-xs font-bold text-gray-500 uppercase">Cerca</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Descrizione o Codice..." class="w-full text-sm rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 h-10">
                    </div>

                    <div class="w-full md:w-48">
                        <label class="text-xs font-bold text-gray-500 uppercase">Ordina per</label>
                        <select name="sort" class="w-full text-sm rounded border-gray-300 h-10">
                            <option value="description" {{ request('sort') == 'description' ? 'selected' : '' }}>Descrizione</option>
                            <option value="code" {{ request('sort') == 'code' ? 'selected' : '' }}>Codice</option>
                            <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Prezzo</option>
                        </select>
                    </div>

                    <div class="w-full md:w-32">
                        <label class="text-xs font-bold text-gray-500 uppercase">Ordine</label>
                        <select name="direction" class="w-full text-sm rounded border-gray-300 h-10">
                            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>A-Z (Cresc)</option>
                            <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Z-A (Decr)</option>
                        </select>
                    </div>

                    <div class="w-full md:w-auto">
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-700 text-sm font-bold h-10 w-full md:w-auto flex justify-center items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Cerca
                        </button>
                    </div>
                    
                    @if(request('search'))
                        <div class="w-full md:w-auto">
                            <a href="{{ route('materials.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm font-bold h-10 w-full md:w-auto flex justify-center items-center">
                                Reset
                            </a>
                        </div>
                    @endif
                </form>
            </div>


            <div x-show="selected.length > 0" 
                 x-transition
                 style="display: none;"
                 class="bg-indigo-50 border border-indigo-200 p-4 rounded-lg shadow-sm mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="font-bold text-indigo-800 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span x-text="selected.length"></span> Elementi selezionati
                </div>

                <div class="flex flex-wrap gap-2 justify-center">
                    <span class="text-xs font-bold text-gray-500 uppercase self-center mr-2">Crea da selezione:</span>
                    
                    <button form="bulk-form" type="submit" name="doc_type" value="invoice" formaction="{{ route('materials.bulk-document') }}" 
                            class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-xs font-bold uppercase tracking-wide">
                        Fattura
                    </button>
                    <button form="bulk-form" type="submit" name="doc_type" value="quote" formaction="{{ route('materials.bulk-document') }}" 
                            class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 text-xs font-bold uppercase tracking-wide">
                        Preventivo
                    </button>
                    <button form="bulk-form" type="submit" name="doc_type" value="report" formaction="{{ route('materials.bulk-document') }}" 
                            class="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600 text-xs font-bold uppercase tracking-wide">
                        Rapporto
                    </button>
                    
                    <div class="w-px bg-gray-300 mx-2 h-6 self-center"></div>

                    <button form="bulk-form" type="submit" formaction="{{ route('materials.bulk-delete') }}" 
                            onclick="return confirm('Sei sicuro di voler eliminare definitivamente i materiali selezionati?')"
                            class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 text-xs font-bold uppercase tracking-wide flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Elimina
                    </button>
                </div>
            </div>

            <form id="bulk-form" method="POST">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left w-10">
                                        <input type="checkbox" 
                                               @click="allSelected = !allSelected; if(allSelected) { selected = {{ json_encode($materials->pluck('id')) }}; } else { selected = []; }"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Codice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrizione</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unità</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Prezzo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($materials as $material)
                                    <tr class="hover:bg-gray-50 transition" :class="selected.includes('{{ $material->id }}') ? 'bg-indigo-50' : ''">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="material_ids[]" value="{{ $material->id }}" x-model="selected"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                            {{ $material->code ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-bold">
                                            {{ $material->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $material->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            € {{ number_format($material->price, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('materials.edit', $material) }}" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold">Modifica</a>
                                            
                                            <button type="button" @click.prevent="if(confirm('Eliminare questo articolo?')) document.getElementById('delete-form-{{ $material->id }}').submit()" class="text-red-600 hover:text-red-900 font-bold">
                                                Elimina
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nessun materiale trovato.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $materials->links() }}
                    </div>
                </div>
            </form>

            @foreach($materials as $material)
                <form id="delete-form-{{ $material->id }}" action="{{ route('materials.destroy', $material) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

        </div>
    </div>
</x-app-layout>