<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestione Backup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg mb-2">Nuovo Backup</h3>
                        <p class="text-sm text-gray-500 mb-4">Genera un'istantanea del database attuale.</p>
                    </div>
                    <form action="{{ route('backups.create') }}" method="POST" onsubmit="return confirm('Confermi la creazione?');">
                        @csrf
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-bold">
                            + Crea Backup
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm md:col-span-2">
                    <h3 class="font-bold text-lg mb-2">Carica Backup Esterno</h3>
                    <p class="text-sm text-gray-500 mb-4">Hai un file .zip salvato sul PC? Caricalo qui per ripristinarlo.</p>
                    
                    <form action="{{ route('backups.upload') }}" method="POST" enctype="multipart/form-data" class="flex gap-4 items-center">
                        @csrf
                        <input type="file" name="backup_file" accept=".zip" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100
                        " required />
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-md hover:bg-gray-700 font-bold whitespace-nowrap">
                            Carica File
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="font-bold text-gray-800">Archivio Disponibile</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dimensione</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($backups as $backup)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $backup['file_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $backup['file_size'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $backup['last_modified'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                        
                                        <form action="{{ route('backups.restore') }}" method="POST" onsubmit="return confirm('ATTENZIONE: Questo sovrascriverà TUTTI i dati attuali con quelli del backup. Sei sicuro?');">
                                            @csrf
                                            <input type="hidden" name="file_name" value="{{ $backup['file_name'] }}">
                                            <button type="submit" class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200 font-bold border border-yellow-300">
                                                Ripristina
                                            </button>
                                        </form>

                                        <a href="{{ route('backups.download', ['file_name' => $backup['file_name']]) }}" class="bg-gray-100 text-gray-700 px-3 py-1 rounded hover:bg-gray-200 border border-gray-300">Scarica</a>
                                        
                                        <form action="{{ route('backups.delete', ['file_name' => $backup['file_name']]) }}" method="POST" onsubmit="return confirm('Eliminare definitivamente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 border border-red-300">X</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nessun backup trovato.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>