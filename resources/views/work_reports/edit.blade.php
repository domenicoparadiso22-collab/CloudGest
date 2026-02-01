<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rapporto #{{ $workReport->number }}
                @if($workReport->status == 'closed')
                    <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">CHIUSO & FIRMATO</span>
                @else
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">BOZZA</span>
                @endif
            </h2>
            
            <div class="flex space-x-2">
                <a href="{{ route('work-reports.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 text-sm">Esci</a>
                
                @if($workReport->status == 'closed')
                    <a href="{{ route('work-reports.pdf', $workReport) }}" target="_blank" class="bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 text-xs shadow flex items-center">
                        📄 PDF Completo
                    </a>
                    <a href="{{ route('work-reports.pdf', ['workReport' => $workReport, 'hide_prices' => 1]) }}" target="_blank" class="bg-teal-600 text-white px-3 py-2 rounded-md hover:bg-teal-700 text-xs shadow flex items-center">
                        🙈 PDF Senza Prezzi
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="flex flex-col lg:flex-row h-screen" x-data="reportForm({{ Js::from($materials) }}, {{ Js::from($existingRows) }})">
        
        <div class="w-full lg:w-1/2 p-6 overflow-y-auto bg-gray-100" style="height: calc(100vh - 65px);">
            
            <form action="{{ route('work-reports.update', $workReport) }}" method="POST" id="mainForm">
                @csrf
                @method('PUT')
                
                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-gray-700 mb-2">Dati Testata</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Cliente</label>
                            <select name="client_id" class="w-full text-sm rounded border-gray-300">
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ $workReport->client_id == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Data</label>
                            <input type="date" name="date" value="{{ $workReport->date }}" class="w-full text-sm rounded border-gray-300">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-gray-700 mb-2">Righe Intervento</h3>
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
                        <span class="text-lg font-bold text-indigo-700">Tot: € <span x-text="totalReport().toFixed(2)"></span></span>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase">Note Visibili</label>
                    <textarea name="notes" rows="3" class="w-full text-sm rounded border-gray-300">{{ $workReport->notes }}</textarea>
                </div>

                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase">Note Private (Interne)</label>
                    <textarea name="private_notes" rows="3" class="w-full text-sm rounded border-gray-300" placeholder="Visibili solo a te...">{{ old('private_notes', $workReport->private_notes) }}</textarea>
                </div>

                <div class="sticky bottom-0 bg-white p-4 shadow-lg border-t border-gray-200 flex flex-col gap-2">
                    <button type="submit" class="bg-gray-800 text-white font-bold py-2 px-8 rounded hover:bg-gray-700 w-full">
                        AGGIORNA ANTEPRIMA
                    </button>
                    
                    @if($workReport->status != 'closed')
                        <button type="button" @click="openSignatureModal()" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded hover:bg-indigo-700 w-full shadow-lg border-2 border-indigo-500 flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            FIRMA E CHIUDI RAPPORTO
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="hidden lg:block w-1/2 bg-gray-800 h-full border-l border-gray-300 relative">
            
            @if($workReport->customer_signature_path)
                <div class="absolute top-4 right-4 z-10 bg-white p-2 rounded shadow opacity-90">
                    <p class="text-xs text-green-600 font-bold mb-1">Firma Acquisita:</p>
                    <img src="{{ asset('storage/' . $workReport->customer_signature_path) }}" class="h-16 border rounded bg-white">
                </div>
            @endif

            <iframe src="{{ route('work-reports.pdf', $workReport) }}#toolbar=0&view=FitH" class="w-full h-full" style="height: calc(100vh - 65px);"></iframe>
        </div>

        <div x-show="showSignatureModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showSignatureModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Firma Cliente</h3>
                        <div class="mt-2">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-1 bg-gray-50">
                                <canvas id="signature-canvas" class="w-full h-48 border border-gray-200 bg-white cursor-crosshair"></canvas>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Firma nel riquadro sopra</p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        
                        <form id="signature-form" action="{{ route('work-reports.sign', $workReport->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="signature_data" id="signature-data">
                        </form>
                        
                        <button type="button" @click="saveSignature()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Conferma Firma
                        </button>
                        
                        <button type="button" @click="clearSignature()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Pulisci
                        </button>
                        
                        <button type="button" @click="showSignatureModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annulla
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div> 
    <script>
        function reportForm(dbMaterials, existingRows) {
            return {
                materials: dbMaterials,
                rows: existingRows.length ? existingRows : [{ material_id: '', description: '', quantity: 1, unit: 'pz', price: 0 }],
                showSignatureModal: false,
                signaturePad: null,

                // --- GESTIONE RIGHE ---
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
                totalReport() { return this.rows.reduce((sum, row) => sum + (row.quantity * row.price), 0); },

                // --- GESTIONE FIRMA ---
                openSignatureModal() {
                    this.showSignatureModal = true;
                    
                    this.$nextTick(() => {
                        const canvas = document.getElementById('signature-canvas');
                        
                        // Fix per schermi ad alta risoluzione (Retina/Mobile)
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext("2d").scale(ratio, ratio);

                        if(!this.signaturePad) {
                            this.signaturePad = new SignaturePad(canvas, {
                                backgroundColor: 'rgba(255, 255, 255, 0)',
                                penColor: 'rgb(0, 0, 0)'
                            });
                        } else {
                            this.signaturePad.clear();
                        }
                    });
                },

                clearSignature() {
                    if(this.signaturePad) this.signaturePad.clear();
                },

                saveSignature() {
                    if (!this.signaturePad || this.signaturePad.isEmpty()) {
                        alert("Per favore, inserisci una firma prima di confermare.");
                        return;
                    }

                    // 1. Estrai immagine in Base64
                    let dataUrl = this.signaturePad.toDataURL("image/png");
                    
                    // 2. Inserisci nel form nascosto HTML
                    // Assicurati che nel tuo HTML ci sia <input type="hidden" name="signature_data" id="signature-data">
                    let hiddenInput = document.getElementById('signature-data');
                    if(hiddenInput) {
                        hiddenInput.value = dataUrl;
                        
                        // 3. Invia il form (Questo ricaricherà la pagina)
                        document.getElementById('signature-form').submit();
                    } else {
                        alert("Errore tecnico: Campo hidden signature-data non trovato.");
                    }
                }
            }
        }
    </script>
</x-app-layout>