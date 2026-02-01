<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifica Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Ragione Sociale o Nome Cognome *</label>
                            <input type="text" name="name" value="{{ old('name', $client->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Partita IVA</label>
                            <input type="text" name="vat_number" value="{{ old('vat_number', $client->vat_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Codice Fiscale</label>
                            <input type="text" name="fiscal_code" value="{{ $client->fiscal_code }}" maxlength="16" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Indirizzo</label>
                            <input type="text" name="address" value="{{ old('address', $client->address) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telefono</label>
                            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="col-span-2 border-2 border-dashed border-gray-300 p-4 rounded text-center mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timbro Cliente</label>
                            
                            @if($client->client_stamp_path)
                                <div class="mb-2">
                                    <p class="text-xs text-green-600 font-bold mb-1">Timbro Attuale:</p>
                                    <img src="{{ asset('storage/' . $client->client_stamp_path) }}" class="h-20 mx-auto border rounded p-1">
                                </div>
                            @endif

                            <input type="file" name="client_stamp" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <a href="{{ route('clients.index') }}" class="mr-4 text-gray-600 underline py-2">Annulla</a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Aggiorna Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>