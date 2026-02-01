<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Modifica Articolo') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('materials.update', $material) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Codice</label>
                            <input type="text" name="code" value="{{ old('code', $material->code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unità di Misura</label>
                            <input type="text" name="unit" value="{{ old('unit', $material->unit) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Descrizione *</label>
                            <input type="text" name="description" value="{{ old('description', $material->description) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prezzo Unitario (€) *</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $material->price) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <a href="{{ route('materials.index') }}" class="mr-4 text-gray-600 underline py-2">Annulla</a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Aggiorna Articolo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>