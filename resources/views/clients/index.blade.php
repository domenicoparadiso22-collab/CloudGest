<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Anagrafica Clienti') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('clients.import') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Importa CSV
                </a>
                <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Nuovo Cliente
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
                <form action="{{ route('clients.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-grow w-full">
                        <label class="text-xs font-bold text-gray-500 uppercase">Cerca</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, Email, P.IVA..." class="w-full text-sm rounded border-gray-300 focus:border-indigo-500 h-10">
                    </div>
                    <div class="w-full md:w-48">
                        <label class="text-xs font-bold text-gray-500 uppercase">Ordina per</label>
                        <select name="sort" class="w-full text-sm rounded border-gray-300 h-10">
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nome</option>
                            <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Data Creazione</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-700 text-sm font-bold h-10 w-full md:w-auto flex justify-center items-center gap-2">
                            Cerca
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="selected.length > 0" x-transition style="display: none;" class="bg-indigo-50 border border-indigo-200 p-4 rounded-lg shadow-sm mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="font-bold text-indigo-800 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span x-text="selected.length"></span> Clienti selezionati
                </div>
                <div class="flex flex-wrap gap-2 justify-center">
                    <span class="text-xs font-bold text-gray-500 uppercase self-center mr-2">Crea per selezionati:</span>
                    
                    <button form="bulk-form" type="submit" name="doc_type" value="invoice" formaction="{{ route('clients.bulk-document') }}" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-xs font-bold uppercase tracking-wide">
                        Fattura
                    </button>
                    <button form="bulk-form" type="submit" name="doc_type" value="quote" formaction="{{ route('clients.bulk-document') }}" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 text-xs font-bold uppercase tracking-wide">
                        Preventivo
                    </button>
                    <button form="bulk-form" type="submit" name="doc_type" value="report" formaction="{{ route('clients.bulk-document') }}" class="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600 text-xs font-bold uppercase tracking-wide">
                        Rapporto
                    </button>
                    
                    <div class="w-px bg-gray-300 mx-2 h-6 self-center"></div>

                    <button form="bulk-form" type="submit" formaction="{{ route('clients.bulk-delete') }}" onclick="return confirm('Eliminare definitivamente i clienti selezionati?')" class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 text-xs font-bold uppercase tracking-wide flex items-center gap-1">
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
                                        <input type="checkbox" @click="allSelected = !allSelected; if(allSelected) { selected = {{ json_encode($clients->pluck('id')) }}; } else { selected = []; }" class="rounded border-gray-300 text-indigo-600 shadow-sm cursor-pointer">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ragione Sociale</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email / Telefono</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">P.IVA / C.F.</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($clients as $client)
                                    <tr class="hover:bg-gray-50 transition" :class="selected.includes('{{ $client->id }}') ? 'bg-indigo-50' : ''">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="client_ids[]" value="{{ $client->id }}" x-model="selected" class="rounded border-gray-300 text-indigo-600 shadow-sm cursor-pointer">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-bold">
                                            {{ $client->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="text-gray-900">{{ $client->email }}</div>
                                            <div class="text-xs">{{ $client->phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                            {{ $client->vat_number ?? $client->fiscal_code ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600 hover:text-indigo-900 font-bold mr-3">Modifica</a>
                                            <button type="button" @click.prevent="if(confirm('Eliminare cliente?')) document.getElementById('delete-form-{{ $client->id }}').submit()" class="text-red-600 hover:text-red-900 font-bold">Elimina</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Nessun cliente trovato.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $clients->links() }}
                    </div>
                </div>
            </form>

            @foreach($clients as $client)
                <form id="delete-form-{{ $client->id }}" action="{{ route('clients.destroy', $client) }}" method="POST" style="display: none;">
                    @csrf @method('DELETE')
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>