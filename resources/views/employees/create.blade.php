<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuovo Dipendente</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="mb-4 bg-blue-50 p-4 rounded border border-blue-200 text-sm text-blue-800">
                    <strong class="block mb-1">Nota Importante:</strong>
                    Matricola e Password verranno generate automaticamente dal sistema al salvataggio.
                </div>

                <form action="{{ route('employees.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block font-bold text-gray-700">Nome e Cognome *</label>
                        <input type="text" name="name" class="w-full rounded border-gray-300" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-bold text-gray-700">Email (Opzionale)</label>
                        <input type="email" name="email" class="w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-bold text-gray-700">Telefono (Opzionale)</label>
                        <input type="text" name="phone" class="w-full rounded border-gray-300">
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('employees.index') }}" class="bg-gray-200 px-4 py-2 rounded text-gray-700">Annulla</a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 font-bold">Crea Dipendente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>