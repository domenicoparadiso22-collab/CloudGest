<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Nuovo Preventivo') }}</h2>
    </x-slot>

    <div class="py-12" x-data="reportForm({{ Js::from($materials) }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('quotes.store') }}" method="POST">
                @csrf
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Dati Principali</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cliente *</label>
                            <select name="client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Seleziona Cliente --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numero Progressivo *</label>
                            <input type="text" name="number" value="{{ $nextNumber }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Dettaglio Intervento / Materiali</h3>
                    
                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrizione</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Q.tà</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">UM</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Prezzo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Totale</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td class="px-4 py-2">
                                        <select x-model="row.material_id" @change="fillRow(index)" class="block w-full text-xs rounded-md border-gray-300 mb-1">
                                            <option value="">- Scegli da listino o scrivi libero -</option>
                                            <template x-for="mat in materials" :key="mat.id">
                                                <option :value="mat.id" x-text="mat.description"></option>
                                            </template>
                                        </select>
                                        <input type="text" :name="'rows['+index+'][description]'" x-model="row.description" placeholder="Descrizione intervento" class="block w-full text-sm rounded-md border-gray-300" required>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" :name="'rows['+index+'][quantity]'" x-model="row.quantity" class="block w-full text-sm rounded-md border-gray-300 text-right" required>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" :name="'rows['+index+'][unit]'" x-model="row.unit" class="block w-full text-sm rounded-md border-gray-300 text-center">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" :name="'rows['+index+'][price]'" x-model="row.price" class="block w-full text-sm rounded-md border-gray-300 text-right" required>
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold text-gray-700">
                                        € <span x-text="(row.quantity * row.price).toFixed(2)"></span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-red-600 hover:text-red-900 font-bold">X</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right font-bold p-4">TOTALE INTERVENTO:</td>
                                <td class="text-right font-bold p-4 text-xl text-indigo-600">
                                    € <span x-text="totalReport().toFixed(2)"></span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <button type="button" @click="addRow()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                        + Aggiungi Riga
                    </button>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note Visibili al Cliente</label>
                    <textarea name="notes" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    
                    <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">Note Interne (Private)</label>
                    <textarea name="private_notes" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-yellow-50"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 text-white font-bold text-lg px-8 py-3 rounded-lg shadow hover:bg-indigo-700">
                        SALVA PREVENTIVO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function reportForm(dbMaterials) {
            return {
                materials: dbMaterials,
                rows: [
                    { material_id: '', description: '', quantity: 1, unit: 'h', price: 0 }
                ],
                addRow() {
                    this.rows.push({ material_id: '', description: '', quantity: 1, unit: 'pz', price: 0 });
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
                    }
                },
                totalReport() {
                    return this.rows.reduce((sum, row) => {
                        return sum + (row.quantity * row.price);
                    }, 0);
                }
            }
        }
    </script>
</x-app-layout>