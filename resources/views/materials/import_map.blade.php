<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mappatura Colonne') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <h3 class="text-lg font-bold mb-2">Associa i campi</h3>
                <p class="text-gray-600 mb-6 text-sm">Abbiamo letto le intestazioni del tuo file. Per ogni campo del nostro software (a sinistra), seleziona la colonna corrispondente del tuo file CSV (a destra).</p>

                <form action="{{ route('materials.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="csv_path" value="{{ $path }}">

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        @foreach($db_fields as $fieldKey => $fieldLabel)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 items-center border-b border-gray-200 pb-4 last:border-0">
                                
                                <div>
                                    <label class="font-bold text-gray-700">{{ $fieldLabel }}</label>
                                    <p class="text-xs text-gray-400">Campo Database: {{ $fieldKey }}</p>
                                </div>

                                <div>
                                    <select name="fields[{{ $fieldKey }}]" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Ignora questo campo --</option>
                                        @foreach($headers as $index => $header)
                                            @php
                                                $selected = '';
                                                // Logica semplice per indovinare: se l'intestazione contiene parole chiave
                                                if ($fieldKey == 'description' && stripos($header, 'desc') !== false) $selected = 'selected';
                                                if ($fieldKey == 'price' && (stripos($header, 'prezzo') !== false || stripos($header, 'listino') !== false)) $selected = 'selected';
                                                if ($fieldKey == 'code' && (stripos($header, 'cod') !== false || stripos($header, 'art') !== false)) $selected = 'selected';
                                            @endphp
                                            <option value="{{ $index }}" {{ $selected }}>{{ $header }} (Colonna {{ $index + 1 }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <a href="{{ route('materials.import') }}" class="text-gray-500 underline">Indietro</a>
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 font-bold shadow-lg">
                            Conferma e Importa Materiali
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>