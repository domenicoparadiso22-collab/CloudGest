<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Seleziona Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <h3 class="text-lg font-bold mb-4 text-indigo-700">Passaggio Finale</h3>
                <p class="mb-6 text-gray-600">Stai per creare un <strong>{{ strtoupper($doc_type) }}</strong> con <strong>{{ count($material_ids) }}</strong> articoli selezionati.</p>

                <form action="{{ route('materials.bulk-create-confirm') }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="doc_type" value="{{ $doc_type }}">
                    @foreach($material_ids as $id)
                        <input type="hidden" name="material_ids[]" value="{{ $id }}">
                    @endforeach

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Assegna al Cliente:</label>
                        <select name="client_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg p-3" required>
                            <option value="">-- Seleziona un Cliente --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-gray-50 p-4 rounded mb-6 border border-gray-200">
                        <h4 class="text-sm font-bold text-gray-500 uppercase mb-2">Articoli inclusi:</h4>
                        <ul class="list-disc list-inside text-sm text-gray-600 max-h-32 overflow-y-auto">
                            @foreach($materials as $mat)
                                <li>{{ $mat->description }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('materials.index') }}" class="text-gray-500 underline">Annulla</a>
                        
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 font-bold shadow-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Conferma e Crea Documento
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>