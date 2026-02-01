<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>WebApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body x-data="appLogic()" class="bg-gray-100 text-gray-800 font-sans h-screen flex flex-col overflow-hidden">

    <div class="bg-white shadow-sm z-30 px-4 py-3 flex justify-between items-center shrink-0 relative">
        <div>
            <h1 class="font-bold text-lg leading-none">{{ explode(' ', $employee->name)[0] }}</h1>
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full {{ $status == 'exit' ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                <p class="text-xs text-gray-500">{{ $status == 'exit' ? 'In Servizio' : 'Fuori Servizio' }}</p>
            </div>
        </div>
        <form action="{{ route('webapp.logout') }}" method="POST">
            @csrf
            <button class="bg-gray-50 p-2 rounded-full text-gray-500 hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
        </form>
    </div>

    <div class="flex-grow overflow-y-auto no-scrollbar relative flex flex-col z-0 pb-24"> 
        
        @if(session('success'))
            <div class="fixed top-16 left-0 w-full px-4 z-50 pointer-events-none">
                <div class="bg-green-500 text-white text-center py-2 rounded-lg shadow-lg text-sm font-bold" 
                     x-init="setTimeout(() => show = false, 3000)" x-data="{show: true}" x-show="show">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if($errors->any())
             <div class="fixed top-16 left-0 w-full px-4 z-50 pointer-events-none">
                <div class="bg-red-500 text-white text-center py-2 rounded-lg shadow-lg text-sm font-bold">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif

        <div x-show="tab === 'home'" class="flex-grow flex flex-col">
            
            @if($status === 'enter')
                <div class="flex-grow flex flex-col items-center justify-center space-y-8 p-4">
                    
                    @if($notices->count() > 0)
                        <div class="w-full max-w-sm space-y-2 opacity-80">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest text-center mb-2">Bacheca Recente</p>
                            @foreach($notices->take(2) as $notice)
                                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 {{ $notice->is_urgent ? 'border-red-500' : 'border-indigo-500' }}">
                                    @if($notice->message)<p class="text-sm truncate">{{ $notice->message }}</p>@endif
                                    @if(!$notice->message)<p class="text-sm italic text-gray-400">Nuova comunicazione</p>@endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form id="clockFormIn" action="{{ route('webapp.clock') }}" method="POST">
                        @csrf
                        <input type="hidden" name="coords" class="coordsInput">
                        <button type="button" @click="timbra('clockFormIn')" :disabled="loading"
                                class="w-64 h-64 bg-green-500 rounded-full flex flex-col items-center justify-center shadow-xl shadow-green-500/30 transition transform active:scale-95 relative overflow-hidden">
                             <div x-show="loading" class="absolute inset-0 bg-black/20 flex items-center justify-center z-10"><svg class="animate-spin h-10 w-10 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                             <span class="text-white font-bold text-3xl drop-shadow-md">INGRESSO</span>
                             <span class="text-white/80 text-sm mt-1">Nuovo Turno</span>
                        </button>
                    </form>
                </div>

            @elseif($status === 'exit')
                
                <div class="flex-grow space-y-4 p-4 pb-32"> 
                    <div class="text-center py-2">
                        <span class="bg-gray-200 text-gray-500 text-[10px] px-2 py-1 rounded-full uppercase tracking-wider">Stream Attività</span>
                    </div>

                    @forelse($notices as $notice)
                        <div class="w-full bg-white rounded-xl p-4 shadow-sm border border-gray-100 {{ $notice->is_urgent ? 'border-l-4 border-l-red-500' : '' }}">
                            
                            @if($notice->message)
                                <p class="text-sm text-gray-800 leading-relaxed font-medium mb-3">{{ $notice->message }}</p>
                            @endif
                            
                            <div class="grid grid-cols-2 gap-2 {{ $notice->message ? 'border-t border-gray-100 pt-3' : '' }}">
                                
                                @if($notice->target_location)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($notice->target_location) }}" target="_blank" class="flex items-center justify-center gap-2 bg-blue-50 border border-blue-100 rounded-lg py-2.5 active:bg-blue-100 transition">
                                        <span class="text-lg">📍</span>
                                        <span class="text-xs font-bold text-blue-700">Naviga</span>
                                    </a>
                                @endif

                                @if($notice->target_email)
                                    <a href="mailto:{{ $notice->target_email }}" class="flex items-center justify-center gap-2 bg-orange-50 border border-orange-100 rounded-lg py-2.5 active:bg-orange-100 transition">
                                        <span class="text-lg">📧</span>
                                        <span class="text-xs font-bold text-orange-700">Email</span>
                                    </a>
                                @endif

                                @if($notice->target_phone)
                                    <a href="tel:{{ $notice->target_phone }}" class="flex items-center justify-center gap-2 bg-green-50 border border-green-100 rounded-lg py-2.5 active:bg-green-100 transition">
                                        <span class="text-lg">📞</span>
                                        <span class="text-xs font-bold text-green-700">Chiama</span>
                                    </a>
                                @endif
                            </div>
                            
                            <p class="text-[10px] text-gray-400 mt-2 text-right">Ricevuto alle {{ $notice->created_at->format('H:i') }}</p>
                        </div>
                    @empty
                        <div class="text-center py-10 opacity-50">
                            <p class="text-sm">Nessuna attività recente.</p>
                        </div>
                    @endforelse
                </div>

                <div class="fixed bottom-20 left-0 w-full px-4 pb-4 pt-6 bg-gradient-to-t from-gray-100 via-gray-100 to-transparent z-40">
                    <form id="clockFormOut" action="{{ route('webapp.clock') }}" method="POST">
                        @csrf
                        <input type="hidden" name="coords" class="coordsInput">
                        <button type="button" @click="timbra('clockFormOut')" :disabled="loading"
                                class="w-full bg-red-500 text-white font-bold py-3 rounded-xl shadow-lg shadow-red-500/20 flex items-center justify-between px-6 active:scale-95 transition transform">
                            <span class="text-sm opacity-80 font-medium">Turno in corso</span>
                            <div class="flex items-center gap-2">
                                <span x-show="!loading" class="tracking-wide">TIMBRA USCITA</span>
                                <span x-show="loading">Attendi...</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </div>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div x-show="tab === 'reports'" class="p-4 space-y-4">
            <h2 class="font-bold text-gray-800 text-lg mb-4">Rapporti da far firmare</h2>
            @forelse($pendingReports as $report)
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-xs font-bold bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">BOZZA</span>
                            <h3 class="font-bold text-gray-900 mt-1">{{ $report->client->name }}</h3>
                            <p class="text-xs text-gray-500">#{{ $report->number }} - {{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-50 flex justify-between items-center">
                        <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded select-all">{{ $report->unique_code }}</span>
                        <a href="{{ route('guest.report.show', $report->unique_code) }}" target="_blank" class="bg-indigo-600 text-white text-xs font-bold px-4 py-2 rounded-lg shadow hover:bg-indigo-700">Apri per Firma →</a>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-400"><p>Nessun rapporto in attesa.</p></div>
            @endforelse
        </div>

        <div x-show="tab === 'leave'" class="p-4 space-y-4">
            <h2 class="font-bold text-gray-800 text-lg mb-4">Richiedi Ferie</h2>
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <form action="{{ route('webapp.leave') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Inizio Ferie</label>
                        <input type="date" name="start_date" class="w-full rounded-lg border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Giorni totali</label>
                        <input type="number" name="days" value="1" min="1" class="w-full rounded-lg border-gray-300" required>
                    </div>
                    <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded-xl shadow-md">Invia Richiesta</button>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white border-t border-gray-200 h-20 shrink-0 flex justify-between items-center px-8 z-50 relative pb-safe">
        <button @click="tab = 'home'" :class="tab === 'home' ? 'text-indigo-600' : 'text-gray-400'" class="flex flex-col items-center w-1/3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-bold mt-1">Home</span>
        </button>
        <button @click="tab = 'reports'" :class="tab === 'reports' ? 'text-indigo-600' : 'text-gray-400'" class="flex flex-col items-center w-1/3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span class="text-[10px] font-bold mt-1">Rapporti</span>
        </button>
        <button @click="tab = 'leave'" :class="tab === 'leave' ? 'text-indigo-600' : 'text-gray-400'" class="flex flex-col items-center w-1/3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-[10px] font-bold mt-1">Ferie</span>
        </button>
    </div>

    <script>
        function appLogic() {
            return {
                tab: 'home',
                loading: false,
                timbra(formId) {
                    this.loading = true;
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                let coords = `${position.coords.latitude},${position.coords.longitude}`;
                                document.querySelector('#' + formId + ' .coordsInput').value = coords;
                                document.getElementById(formId).submit();
                            }, 
                            (error) => {
                                this.loading = false;
                                alert("Errore GPS: " + error.message);
                            },
                            { enableHighAccuracy: true, timeout: 10000 }
                        );
                    } else {
                        alert("GPS non supportato.");
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>