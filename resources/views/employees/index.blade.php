<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestione Dipendenti</h2>
            <a href="{{ route('employees.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold text-xs uppercase shadow hover:bg-indigo-700">
                + Nuovo Dipendente
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Matricola</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Credenziali</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nome Dipendente</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contatti</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($employees as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4" class="font-mono text-indigo-600 font-bold text-lg">{{ $employee->registration_number }}</td>
                                <td>
                                    <div class="font-mono bg-gray-100 px-2 py-1 rounded border border-gray-200 inline-block text-gray-800">
                                    {{ $employee->password }}
                                    </div> 
                               </td>
                                <td class="px-6 py-4 font-bold">{{ $employee->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->email }}<br>{{ $employee->phone }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('employees.show', $employee) }}" class="text-indigo-600 hover:underline font-bold mr-3">APRI</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>