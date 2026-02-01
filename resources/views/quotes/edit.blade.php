<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Preventivo #{{ $quote->number }}
                @if($quote->status == 'draft')
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">BOZZA</span>
                @else
                    <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full uppercase">{{ $quote->status }}</span>
                @endif
            </h2>
            
            <div class="flex space-x-2">
                <a href="{{ route('quotes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 text-sm">Esci</a>
                
                <a href="{{ route('quotes.pdf', $quote) }}" target="_blank" class="bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 text-xs shadow flex items-center">
                    📄 PDF Completo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="flex flex-col lg:flex-row h-screen" x-data="quoteForm({{ Js::from($materials) }}, {{ Js::from($existingRows) }})">
        
        <div class="w-full lg:w-1/2 p-6 overflow-y-auto bg-gray-100" style="height: calc(100vh - 65px);">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('quotes.update', $quote) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-gray-700 mb-2">Dati Preventivo</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Cliente</label>
                            <select name="client_id" class="w-full text-sm rounded border-gray-300">
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ $quote->client_id == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Data Emiss.</label>
                            <input type="date" name="date" value="{{ $quote->date }}" class="w-full text-sm rounded border-gray-300">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Valido fino al</label>
                            <input type="date" name="valid_until" value="{{ $quote->valid_until }}" class="w-full text-sm rounded border-gray-300">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="text-xs font-bold text-gray-500 uppercase">Stato Offerta</label>
                        <select name="status" class="w-full text-sm rounded border-gray-300">
                            <option value="draft" {{ $quote->status == 'draft' ? 'selected' : '' }}>Bozza</option>
                            <option value="sent" {{ $quote->status == 'sent' ? 'selected' : '' }}>Inviato</option>
                            <option value="accepted" {{ $quote->status == 'accepted' ? 'selected' : '' }}>Accettato</option>
                            <option value="rejected" {{ $quote->status == 'rejected' ? 'selected' : '' }}>Rifiutato</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-gray-700 mb-2">Articoli e Servizi</h3>
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex flex-wrap gap-2 mb-2 pb-2 border-b border-gray-100 items-end">
                            <div class="w-full">
                                <select x-model="row.material_id" @change="fillRow(index)" class="w-full text-xs border-gray-200 rounded text-gray-500">
                                    <option value="">Seleziona da listino...</option>
                                    <template x-for="mat in materials" :key="mat.id">
                                        <option :value="mat.id" x-text="mat.description"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="flex-grow">
                                <input type="text" :name="'rows['+index+'][description]'" x-model="row.description" placeholder="Descrizione" class="w-full text-sm rounded border-gray-300" required>
                            </div>
                            <div class="w-16">
                                <input type="number" step="0.01" :name="'rows['+index+'][quantity]'" x-model="row.quantity" placeholder="Q.tà" class="w-full text-sm rounded border-gray-300 text-center" required>
                            </div>
                            <div class="w-16">
                                <input type="text" :name="'rows['+index+'][unit]'" x-model="row.unit" placeholder="UM" class="w-full text-sm rounded border-gray-300 text-center">
                            </div>
                            <div class="w-20">
                                <input type="number" step="0.01" :name="'rows['+index+'][price]'" x-model="row.price" placeholder="€" class="w-full text-sm rounded border-gray-300 text-right" required>
                            </div>
                            <button type="button" @click="removeRow(index)" class="text-red-500 font-bold px-2">X</button>
                        </div>
                    </template>
                    
                    <div class="flex justify-between items-center mt-2">
                        <button type="button" @click="addRow()" class="text-green-600 text-sm font-bold hover:underline">+ Aggiungi Riga</button>
                        <span class="text-lg font-bold text-indigo-700">Tot: € <span x-text="totalQuote().toFixed(2)"></span></span>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase">Note per il cliente</label>
                    <textarea name="notes" rows="3" class="w-full text-sm rounded border-gray-300">{{ $quote->notes }}</textarea>
                    
                    <label class="text-xs font-bold text-gray-500 uppercase mt-2 block">Note Private</label>
                    <textarea name="private_notes" rows="2" class="w-full text-sm rounded border-gray-300 bg-yellow-50">{{ $quote->private_notes }}</textarea>
                </div>

                <div class="sticky bottom-0 bg-white p-4 shadow-lg border-t border-gray-200 text-center">
                    <button type="submit" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded hover:bg-indigo-700 w-full shadow-lg">
                        SALVA E AGGIORNA ANTEPRIMA
                    </button>
                </div>
            </form>
        </div>

        <div class="hidden lg:block w-1/2 bg-gray-800 h-full border-l border-gray-300">
            <iframe src="{{ route('quotes.pdf', $quote) }}#toolbar=0&view=FitH" class="w-full h-full" style="height: calc(100vh - 65px);"></iframe>
        </div>

    </div>

    <script>
        function quoteForm(dbMaterials, existingRows) {
            return {
                materials: dbMaterials,
                rows: existingRows.length ? existingRows : [{ material_id: '', description: '', quantity: 1, unit: 'pz', price: 0 }],
                
                addRow() { this.rows.push({ material_id: '', description: '', quantity: 1, unit: 'pz', price: 0 }); },
                removeRow(index) { if(this.rows.length > 1) this.rows.splice(index, 1); },
                
                fillRow(index) {
                    let selectedId = this.rows[index].material_id;
                    let material = this.materials.find(m => m.id == selectedId);
                    if(material) {
                        this.rows[index].description = material.description;
                        this.rows[index].unit = material.unit;
                        this.rows[index].price = material.price;
                    }
                },
                totalQuote() { return this.rows.reduce((sum, row) => sum + (row.quantity * row.price), 0); }
            }
        }
    </script>
</x-app-layout>