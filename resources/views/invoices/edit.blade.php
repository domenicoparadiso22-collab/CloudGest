<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Fattura #{{ $invoice->number }}
                @if($invoice->status == 'unpaid')
                    <span class="ml-2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">NON PAGATA</span>
                @elseif($invoice->status == 'paid')
                    <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">PAGATA</span>
                @else
                    <span class="ml-2 bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full uppercase">{{ $invoice->status }}</span>
                @endif
            </h2>
            
            <div class="flex space-x-2">
                <a href="{{ route('invoices.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 text-sm">Esci</a>
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 text-xs shadow flex items-center">
                    📄 PDF
                </a>
            </div>
        </div>
    </x-slot>

    @if($errors->any())
        <script>
            let errorMsg = "Si sono verificati i seguenti errori:\n";
            @foreach ($errors->all() as $error)
                errorMsg += "- {{ $error }}\n";
            @endforeach
            alert(errorMsg);
        </script>
    @endif

    <div class="flex flex-col lg:flex-row h-screen" x-data="invoiceForm({{ Js::from($materials) }}, {{ Js::from($existingRows) }})">
        
        <div class="w-full lg:w-1/2 p-6 overflow-y-auto bg-gray-100" style="height: calc(100vh - 65px);">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm">
                @csrf
                @method('PUT')
                
                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-gray-700 mb-2">Dati Fattura</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Cliente *</label>
                            <select name="client_id" x-ref="client_id" class="w-full text-sm rounded border-gray-300">
                                <option value="">-- Seleziona Cliente --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ $invoice->client_id == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Data Emissione *</label>
                            <input type="date" name="date" x-ref="date" value="{{ $invoice->date }}" class="w-full text-sm rounded border-gray-300">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Metodo Pagamento</label>
                            <select name="payment_method" class="w-full text-sm rounded border-gray-300">
                                <option value="Bonifico Bancario" {{ $invoice->payment_method == 'Bonifico Bancario' ? 'selected' : '' }}>Bonifico Bancario</option>
                                <option value="Rimessa Diretta" {{ $invoice->payment_method == 'Rimessa Diretta' ? 'selected' : '' }}>Rimessa Diretta</option>
                                <option value="Ri.Ba." {{ $invoice->payment_method == 'Ri.Ba.' ? 'selected' : '' }}>Ri.Ba.</option>
                                <option value="Assegno" {{ $invoice->payment_method == 'Assegno' ? 'selected' : '' }}>Assegno</option>
                                <option value="Contanti" {{ $invoice->payment_method == 'Contanti' ? 'selected' : '' }}>Contanti</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Scadenza</label>
                            <input type="date" name="due_date" value="{{ $invoice->due_date }}" class="w-full text-sm rounded border-gray-300">
                        </div>
                    </div>

                    <div class="mt-4 border-t pt-4">
                        <label class="text-xs font-bold text-gray-500 uppercase">Stato Incasso</label>
                        <select name="status" class="w-full text-sm rounded border-gray-300 font-bold">
                            <option value="unpaid" class="text-red-600" {{ $invoice->status == 'unpaid' ? 'selected' : '' }}>🔴 NON PAGATA</option>
                            <option value="paid" class="text-green-600" {{ $invoice->status == 'paid' ? 'selected' : '' }}>🟢 PAGATA</option>
                            <option value="overdue" class="text-orange-600" {{ $invoice->status == 'overdue' ? 'selected' : '' }}>🟠 SCADUTA</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-gray-700 mb-2">Righe</h3>
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex flex-wrap gap-2 mb-2 pb-2 border-b border-gray-100 items-end">
                            <div class="w-full">
                                <select x-model="row.material_id" @change="fillRow(index)" class="w-full text-xs border-gray-200 rounded text-gray-500">
                                    <option value="">Seleziona...</option>
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
                            <div class="w-20">
                                <input type="number" step="0.01" :name="'rows['+index+'][price]'" x-model="row.price" placeholder="€" class="w-full text-sm rounded border-gray-300 text-right" required>
                            </div>
                            <div class="w-16">
                                <select :name="'rows['+index+'][vat_rate]'" x-model="row.vat_rate" class="w-full text-sm rounded border-gray-300 text-center">
                                    <option value="22">22%</option>
                                    <option value="10">10%</option>
                                    <option value="4">4%</option>
                                    <option value="0">0%</option>
                                </select>
                            </div>
                            <button type="button" @click="removeRow(index)" class="text-red-500 font-bold px-2">X</button>
                        </div>
                    </template>
                    
                    <div class="flex justify-between items-center mt-2">
                        <button type="button" @click="addRow()" class="text-green-600 text-sm font-bold hover:underline">+ Riga</button>
                        <span class="text-lg font-bold text-indigo-700">Lordo: € <span x-text="calculateGross().toFixed(2)"></span></span>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase">Note</label>
                    <textarea name="notes" rows="3" class="w-full text-sm rounded border-gray-300">{{ $invoice->notes }}</textarea>
                </div>

                <div class="sticky bottom-0 bg-white p-4 shadow-lg border-t border-gray-200 text-center">
                    <button type="button" @click="validateAndSubmit()" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded hover:bg-indigo-700 w-full shadow-lg">
                        AGGIORNA FATTURA
                    </button>
                </div>
            </form>
        </div>

        <div class="hidden lg:block w-1/2 bg-gray-800 h-full border-l border-gray-300">
            <iframe src="{{ route('invoices.pdf', $invoice) }}#toolbar=0&view=FitH" class="w-full h-full" style="height: calc(100vh - 65px);"></iframe>
        </div>
    </div>

    <script>
        function invoiceForm(dbMaterials, existingRows) {
            return {
                materials: dbMaterials,
                rows: existingRows.length ? existingRows : [{ material_id: '', description: '', quantity: 1, unit: 'pz', price: 0, vat_rate: 22 }],
                
                addRow() { this.rows.push({ material_id: '', description: '', quantity: 1, unit: 'pz', price: 0, vat_rate: 22 }); },
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
                calculateGross() {
                    return this.rows.reduce((sum, row) => {
                        let net = row.quantity * row.price;
                        let vat = net * (row.vat_rate / 100);
                        return sum + net + vat;
                    }, 0);
                },

                // NUOVA FUNZIONE DI VALIDAZIONE POPUP
                validateAndSubmit() {
                    let missingFields = [];

                    // 1. Controllo Cliente
                    let client = this.$refs.client_id.value;
                    if (!client) missingFields.push("- Cliente mancante");

                    // 2. Controllo Data
                    let date = this.$refs.date.value;
                    if (!date) missingFields.push("- Data Emissione mancante");

                    // 3. Controllo Righe (descrizione e prezzo)
                    let invalidRows = false;
                    this.rows.forEach((row, index) => {
                        if(!row.description || row.price === '' || row.quantity === '') {
                            invalidRows = true;
                        }
                    });
                    if (invalidRows) missingFields.push("- Una o più righe sono incomplete (manca descrizione, quantità o prezzo)");

                    // MOSTRA IL POPUP SE CI SONO ERRORI
                    if (missingFields.length > 0) {
                        alert("IMPOSSIBILE SALVARE!\n\nCorreggi i seguenti errori:\n" + missingFields.join("\n"));
                        return; // Blocca il salvataggio
                    }

                    // Se tutto ok, invia il form
                    document.getElementById('invoiceForm').submit();
                }
            }
        }
    </script>
</x-app-layout>