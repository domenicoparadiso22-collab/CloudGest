<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifica Dipendente</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="mb-6 border-b pb-4">
                    <p class="text-xs text-gray-500 uppercase font-bold">Matricola Assegnata</p>
                    <p class="text-xl font-mono font-bold text-indigo-600">{{ $employee->registration_number }}</p>
                </div>

                <form action="{{ route('employees.update', $employee) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block font-bold text-gray-700">Nome e Cognome *</label>
                        <input type="text" name="name" value="{{ $employee->name }}" class="w-full rounded border-gray-300" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-bold text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ $employee->email }}" class="w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-bold text-gray-700">Telefono</label>
                        <input type="text" name="phone" value="{{ $employee->phone }}" class="w-full rounded border-gray-300">
                    </div>

                    <div class="my-6 border-t border-gray-200 pt-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Credenziali di Accesso</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-gray-700">Matricola (Non modificabile)</label>
                                <input type="text" value="{{ $employee->registration_number }}" class="w-full rounded border-gray-300 bg-gray-100 text-gray-500" disabled>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700">Password</label>
                                <input type="text" name="password" value="{{ $employee->password }}" class="w-full rounded border-gray-300 font-mono text-indigo-600 font-bold">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <button type="button" 
                                onclick="if(confirm('Sei sicuro di voler eliminare questo dipendente? Verrà cancellato anche tutto lo storico timbrature.')) document.getElementById('delete-emp-form').submit()" 
                                class="text-red-600 hover:underline font-bold text-sm">
                            Elimina Dipendente
                        </button>

                        <div class="flex gap-2">
                            <a href="{{ route('employees.index') }}" class="bg-gray-200 px-4 py-2 rounded text-gray-700">Annulla</a>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 font-bold">Salva Modifiche</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<form id="delete-emp-form" action="{{ route('employees.destroy', $employee) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>