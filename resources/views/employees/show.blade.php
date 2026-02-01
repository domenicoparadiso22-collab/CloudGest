<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione: {{ $employee->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="md:col-span-2 space-y-6">
                
                <div class="bg-white shadow rounded-lg p-6 border-l-4 border-indigo-500">
                    <h3 class="font-bold text-gray-700 mb-4">Invia Comunicazione / Azione</h3>
                    <form action="{{ route('employees.message.store', $employee) }}" method="POST">
                        @csrf
                        
                        <textarea name="message" rows="2" class="w-full rounded-lg border-gray-300 mb-3 text-sm" placeholder="Messaggio opzionale..."></textarea>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <input type="text" name="target_location" placeholder="📍 Indirizzo o Coordinate GPS" class="w-full rounded-lg border-gray-300 text-sm">
                            
                            <div class="flex gap-2">
                                <input type="email" name="target_email" placeholder="📧 Email" class="w-1/2 rounded-lg border-gray-300 text-sm">
                                <input type="text" name="target_phone" placeholder="📞 Telefono" class="w-1/2 rounded-lg border-gray-300 text-sm">
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="is_urgent" class="rounded text-red-600 focus:ring-red-500">
                                <span class="text-sm font-bold text-red-600">Segna come Urgente</span>
                            </label>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-bold text-sm transition">Invia</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-bold text-gray-700 mb-4">Cronologia Comunicazioni</h3>
                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                        @forelse($messages as $msg)
                            <div class="p-4 rounded-lg relative group {{ $msg->is_urgent ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200' }}">
                                
                                <div class="absolute top-2 right-2">
                                    <form action="{{ route('employees.message.destroy', $msg->id) }}" method="POST" onsubmit="return confirm('Vuoi davvero eliminare questo messaggio?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                @if($msg->message)
                                    <p class="text-gray-800 font-medium mb-2 pr-6">{{ $msg->message }}</p>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    @if($msg->target_location)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            📍 {{ Str::limit($msg->target_location, 25) }}
                                        </span>
                                    @endif
                                    @if($msg->target_email)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            📧 {{ $msg->target_email }}
                                        </span>
                                    @endif
                                    @if($msg->target_phone)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            📞 {{ $msg->target_phone }}
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-[10px] text-gray-400 text-right mt-2 border-t border-gray-200 pt-1">
                                    Inviato il {{ $msg->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <p>Nessuna comunicazione registrata.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto text-2xl font-bold text-indigo-600 mb-2">
                        {{ substr($employee->name, 0, 2) }}
                    </div>
                    <h2 class="text-xl font-bold">{{ $employee->name }}</h2>
                    <p class="text-gray-500 font-mono text-sm mb-4">{{ $employee->registration_number }}</p>
                    
                    <div class="space-y-2">
                        <a href="{{ route('employees.history', $employee) }}" class="block w-full bg-white border border-gray-300 text-gray-700 font-bold py-2 rounded-lg hover:bg-gray-50 text-sm">
                            Vedi Presenze
                        </a>
                        <a href="{{ route('employees.edit', $employee) }}" class="block w-full bg-white border border-gray-300 text-gray-700 font-bold py-2 rounded-lg hover:bg-gray-50 text-sm">
                            Modifica Dati
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>