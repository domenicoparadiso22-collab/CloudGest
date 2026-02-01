<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuova Fattura') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="invoiceForm({{ Js::from($materials) }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Dati Fattura</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cliente *</label>
                            <select name="client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Seleziona --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data Emissione *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numero *</label>
                            <input type="text" name="number" value="{{ $nextNumber }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Metodo Pagamento</label>
                            <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Bonifico Bancario">Bonifico Bancario</option>
                                <option value="Rimessa Diretta">Rimessa Diretta</option>
                                <option value="Ri.Ba.">Ri.Ba.</option>
                                <option value="Assegno">Assegno</option>
                                <option value="Contanti">Contanti</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scadenza Pagamento</label>
                            <input type="date" name="due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Articoli e Servizi</h3>
                    
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex flex-wrap gap-2 mb-2 pb-2 border-b border-gray-100 items-end">
                            <div class="w-full md:w-auto flex-grow">
                                <select x-model="row.material_id" @change="fillRow(index)" class="block w-full text-xs rounded-md border-gray-300 text-gray-500 mb-1">
                                    <option value="">Seleziona da listino...</option>
                                    <template x-for="mat in materials" :key="mat.id">
                                        <option :value="mat.id" x-text="mat.description"></option>
                                    </template>
                                </select>
                                <input type="text" :name="'rows['+index+'][description]'" x-model="row.description" placeholder="Descrizione" class="block w-full text-sm rounded-md border-gray-300" required>
                            </div>

                            <div class="w-20">
                                <label class="text-[10px] text-gray-500">Q.tà</label>
                                <input type="number" step="0.01" :name="'rows['+index+'][quantity]'" x-model="row.quantity" class="block w-full text-sm rounded-md border-gray-300 text-center" required>
                            </div>

                            <div class="w-16">
                                <label class="text-[10px] text-gray-500">UM</label>
                                <input type="text" :name="'rows['+index+'][unit]'" x-model="row.unit" class="block w-full text-sm rounded-md border-gray-300 text-center">
                            </div>

                            <div class="w-24">
                                <label class="text-[10px] text-gray-500">Prezzo (€)</label>
                                <input type="number" step="0.01" :name="'rows['+index+'][price]'" x-model="row.price" class="block w-full text-sm rounded-md border-gray-300 text-right" required>
                            </div>

                            <div class="w-20">
                                <label class="text-[10px] text-gray-500">% IVA</label>
                                <select :name="'rows['+index+'][vat_rate]'" x-model="row.vat_rate" class="block w-full text-sm rounded-md border-gray-300 text-center">
                                    <option value="22">22%</option>
                                    <option value="10">10%</option>
                                    <option value="4">4%</option>
                                    <option value="0">0%</option>
                                </select>
                            </div>

                            <button type="button" @click="removeRow(index)" class="text-red-600 hover:text-red-900 font-bold px-2 mb-1">X</button>
                        </div>
                    </template>

                    <div class="flex justify-between items-center mt-4">
                        <button type="button" @click="addRow()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                            + Aggiungi Riga
                        </button>
                        
                        <div class="text-right">
                            <div class="text-sm text-gray-600">Imponibile: € <span x-text="calculateNet().toFixed(2)"></span></div>
                            <div class="text-xl font-bold text-indigo-700">TOTALE LORDO: € <span x-text="calculateGross().toFixed(2)"></span></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note Fattura</label>
                    <textarea name="notes" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    
                    <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">Note Private (Visibili solo a te)</label>
                    <textarea name="private_notes" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm bg-yellow-50"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 text-white font-bold text-lg px-8 py-3 rounded-lg shadow hover:bg-indigo-700">
                        EMETTI FATTURA
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function invoiceForm(dbMaterials) {
            return {
                materials: dbMaterials,
                rows: [
                    { material_id: '', description: '', quantity: 1, unit: 'pz', price: 0, vat_rate: 22 }
                ],
                addRow() {
                    this.rows.push({ material_id: '', description: '', quantity: 1, unit: 'pz', price: 0, vat_rate: 22 });
                },
                removeRow(index) {
                    if(this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },
                fillRow(index) {
                    let selectedId = this.rows[index].material_id;
                    let material = this.materials.find(m => m.id == selectedId);
                    if(material) {
                        this.rows[index].description = material.description;
                        this.rows[index].unit = material.unit;
                        this.rows[index].price = material.price;
                        // Il DB materiali non ha IVA salvata, assumiamo 22 o lascia precedente
                    }
                },
                calculateNet() {
                    return this.rows.reduce((sum, row) => sum + (row.quantity * row.price), 0);
                },
                calculateGross() {
                    return this.rows.reduce((sum, row) => {
                        let net = row.quantity * row.price;
                        let vat = net * (row.vat_rate / 100);
                        return sum + net + vat;
                    }, 0);
                }
            }
        }
    </script>
</x-app-layout>