<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>CloudGest Staff</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
        .tab-active { color: #4f46e5; transform: translateY(-4px); }
        .notification-bounce { animation: bounce 0.5s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
    </style>

    <template x-if="'Notification' in window && Notification.permission !== 'granted'">
    <div class="bg-indigo-600 p-4 m-4 rounded-2xl shadow-lg flex items-center justify-between animate-pulse">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🔔</span>
            <p class="text-white text-xs font-bold leading-tight">Attiva le notifiche per ricevere aggiornamenti in tempo reale.</p>
        </div>
        <button @click="requestPermission()" class="bg-white text-indigo-600 px-4 py-2 rounded-xl text-xs font-black uppercase">Attiva</button>
    </div>
    </template>
</head>
<body x-data="appLogic()" x-init="init()" class="bg-slate-50 text-slate-900 h-screen flex flex-col overflow-hidden">

    <header class="glass sticky top-0 z-50 px-6 py-4 flex justify-between items-center border-b border-slate-200/50">
        <div>
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-[0.2em] mb-0.5">Area Staff</p>
            <h1 class="font-extrabold text-2xl bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent leading-none" 
                x-text="tab === 'home' ? 'Bacheca' : (tab === 'clock' ? 'Presenze' : (tab === 'reports' ? 'Firme' : 'Ferie'))"></h1>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-bold">{{ $employee->name }}</p>
            </div>
            <form action="{{ route('webapp.logout') }}" method="POST">
                @csrf
                <button class="bg-slate-100 p-2.5 rounded-full text-slate-500 hover:bg-red-50 hover:text-red-600 transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </header>

    <main class="flex-grow overflow-y-auto no-scrollbar pb-36">

        <template x-if="successMsg">
            <div class="p-4 mx-4 mt-4 bg-green-500 text-white rounded-2xl font-bold text-center shadow-lg shadow-green-200" x-text="successMsg"></div>
        </template>

        <section x-show="tab === 'home'" x-transition class="p-4 space-y-4">
            <div class="flex justify-between items-center px-2">
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest">Comunicazioni</h2>
                <span class="text-[10px] bg-slate-200 px-2 py-0.5 rounded-full font-bold uppercase">{{ count($notices) }} messaggi</span>
            </div>
            
            @forelse($notices as $notice)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 relative overflow-hidden group">
                    @if($notice->is_urgent)
                        <div class="absolute top-0 left-0 h-full w-1.5 bg-red-500"></div>
                    @endif
                    
                    <p class="text-slate-700 leading-relaxed font-medium mb-4">{{ $notice->message }}</p>
                    
                    <div class="grid grid-cols-1 gap-2">
                        @if($notice->target_location)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($notice->target_location) }}" target="_blank" class="flex items-center justify-center gap-2 bg-indigo-50 text-indigo-700 rounded-2xl py-3 text-xs font-extrabold hover:bg-indigo-100 transition">📍 Apri Destinazione</a>
                        @endif
                        <div class="grid grid-cols-2 gap-2">
                            @if($notice->target_phone)
                                <a href="tel:{{ $notice->target_phone }}" class="flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 rounded-2xl py-3 text-xs font-extrabold">📞 Chiama</a>
                            @endif
                            @if($notice->target_email)
                                <a href="mailto:{{ $notice->target_email }}" class="flex items-center justify-center gap-2 bg-amber-50 text-amber-700 rounded-2xl py-3 text-xs font-extrabold">📧 Email</a>
                            @endif
                        </div>
                    </div>
                    <p class="text-[9px] text-slate-400 mt-4 text-right font-bold uppercase tracking-tighter">{{ $notice->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 opacity-20">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="font-bold">Bacheca vuota</p>
                </div>
            @endforelse
        </section>

        <section x-show="tab === 'clock'" x-transition class="p-4 space-y-6">
            <div class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 text-center border border-white relative overflow-hidden">
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-slate-50 rounded-full"></div>
                
                <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-8">Rilevazione Presenza</h3>
                
                <form id="clockForm" action="{{ route('webapp.clock') }}" method="POST">
                    @csrf
                    <input type="hidden" name="coords" id="coordsInput">
                    <button type="button" 
                        @click="handleClock()"
                        :disabled="loading"
                        :class="status === 'enter' ? 'bg-emerald-500 shadow-emerald-200' : 'bg-rose-500 shadow-rose-200'"
                        class="w-52 h-52 rounded-full mx-auto flex flex-col items-center justify-center shadow-[0_20px_50px_rgba(0,0,0,0.1)] transition-all active:scale-90 relative group">
                        
                        <div class="absolute inset-0 rounded-full border-4 border-white/20 scale-110 group-active:scale-100 transition-transform"></div>

                        <template x-if="!loading">
                            <div class="text-white">
                                <span class="block font-black text-3xl uppercase tracking-tighter" x-text="status === 'enter' ? 'Entra' : 'Esci'"></span>
                                <span class="text-[10px] font-bold opacity-60 uppercase tracking-[0.2em] mt-1">Touch ID / GPS</span>
                            </div>
                        </template>
                        <template x-if="loading">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="animate-spin h-10 w-10 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span class="text-white text-[10px] font-bold">POSIZIONE...</span>
                            </div>
                        </template>
                    </button>
                </form>
            </div>

            <div class="space-y-3 px-2">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Attività Recente</h3>
                @foreach($history as $h)
                    <div class="bg-white p-5 rounded-[1.5rem] flex justify-between items-center shadow-sm border border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="bg-slate-50 p-3 rounded-2xl text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="font-extrabold text-slate-800 text-sm">{{ $h->date->format('d M') }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $h->date->translatedFormat('l') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-emerald-600">IN {{ $h->clock_in->format('H:i') }}</p>
                            <p class="text-xs font-black text-rose-500">OUT {{ $h->clock_out ? $h->clock_out->format('H:i') : '--:--' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section x-show="tab === 'reports'" x-transition class="p-4 space-y-4">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest px-2">Documenti da Firmare</h2>
            @forelse($pendingReports as $report)
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 group active:scale-[0.98] transition">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="font-black text-lg text-slate-900 leading-tight">{{ $report->client->name }}</h3>
                            <p class="text-xs font-bold text-slate-400 mt-1">Rapporto Intervento #{{ $report->number }}</p>
                        </div>
                        <div class="bg-indigo-50 text-indigo-700 p-2 rounded-2xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-grow bg-slate-50 rounded-2xl px-5 py-4 text-sm font-mono font-black text-slate-500 border border-slate-100">
                            {{ $report->unique_code }}
                        </div>
                        <a href="{{ route('guest.report.show', $report->unique_code) }}" target="_blank" class="bg-indigo-600 text-white p-4 rounded-2xl shadow-lg shadow-indigo-100 active:bg-indigo-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 opacity-20 italic">Tutti i documenti sono stati firmati.</div>
            @endforelse
        </section>

        <section x-show="tab === 'leave'" x-transition class="p-4 space-y-6">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                <h3 class="font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="text-xl">✈️</span> Nuova Richiesta
                </h3>
                <form action="{{ route('webapp.leave') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Data Inizio</label>
                            <input type="date" name="start_date" class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-2xl p-4 text-sm font-bold transition" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nr. Giorni</label>
                            <input type="number" name="days" value="1" min="1" class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-2xl p-4 text-sm font-bold transition" required>
                        </div>
                    </div>
                    <button class="w-full bg-gradient-to-r from-orange-500 to-amber-500 text-white font-black py-4 rounded-2xl shadow-xl shadow-orange-100 transition active:scale-95 uppercase tracking-widest text-xs">Invia al datore</button>
                </form>
            </div>

            <div class="space-y-3 px-2">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Cronologia Richieste</h3>
                @foreach($leaves as $leave)
                    <div class="bg-white p-5 rounded-[1.5rem] flex justify-between items-center shadow-sm border border-slate-100">
                        <div>
                            <p class="text-xs font-black text-slate-800">{{ $leave->start_date->format('d/m/Y') }}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $leave->start_date->diffInDays($leave->end_date) + 1 }} giorni</p>
                        </div>
                        <div class="text-right">
                            @if($leave->status == 'pending')
                                <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-xl bg-amber-50 text-amber-600 border border-amber-100">In attesa</span>
                            @elseif($leave->status == 'approved')
                                <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100">Approvata</span>
                            @else
                                <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 border border-rose-100">Rifiutata</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <nav class="glass border-t border-slate-200/50 fixed bottom-0 w-full h-24 flex justify-around items-center px-6 pb-safe z-[100]">
        <button @click="tab = 'home'; hasNewNotice = false" class="flex flex-col items-center relative transition-all duration-300" :class="tab === 'home' ? 'tab-active' : 'text-slate-300'">
            <div x-show="hasNewNotice" class="absolute -top-1 -right-1 h-3 w-3 bg-rose-500 rounded-full border-2 border-white notification-bounce"></div>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 2v4h4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12h10m-10 4h10"></path></svg>
            <span class="text-[9px] font-black mt-1.5 uppercase tracking-tighter">Bacheca</span>
        </button>
        
        <button @click="tab = 'clock'" class="flex flex-col items-center transition-all duration-300" :class="tab === 'clock' ? 'tab-active' : 'text-slate-300'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-[9px] font-black mt-1.5 uppercase tracking-tighter">Presenze</span>
        </button>

        <button @click="tab = 'reports'; hasNewReport = false" class="flex flex-col items-center relative transition-all duration-300" :class="tab === 'reports' ? 'tab-active' : 'text-slate-300'">
            <div x-show="hasNewReport" class="absolute -top-1 -right-1 h-3 w-3 bg-rose-500 rounded-full border-2 border-white notification-bounce"></div>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            <span class="text-[9px] font-black mt-1.5 uppercase tracking-tighter">Firme</span>
        </button>

        <button @click="tab = 'leave'" class="flex flex-col items-center transition-all duration-300" :class="tab === 'leave' ? 'tab-active' : 'text-slate-300'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-[9px] font-black mt-1.5 uppercase tracking-tighter">Ferie</span>
        </button>
    </nav>

    <script>
        function appLogic() {
            return {
                tab: 'home',
                loading: false,
                status: '{{ $status }}',
                hasNewNotice: false,
                hasNewReport: false,
                successMsg: '{{ session("success") }}',

                init() {
                    // Richiesta permessi notifiche all'avvio
                    if ("Notification" in window && Notification.permission !== "granted") {
                        Notification.requestPermission();
                    }

                    // Polling ogni 30 secondi
                    setInterval(() => this.checkUpdates(), 30000);
                    
                    if(this.successMsg) {
                        setTimeout(() => this.successMsg = '', 4000);
                    }
                },

                handleClock() {
                    this.loading = true;
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(
                            (p) => {
                                document.getElementById('coordsInput').value = p.coords.latitude + ',' + p.coords.longitude;
                                document.getElementById('clockForm').submit();
                            },
                            (e) => {
                                this.loading = false;
                                alert('Errore GPS: Attiva la geolocalizzazione per timbrare.');
                            },
                            { enableHighAccuracy: true, timeout: 10000 }
                        );
                    } else {
                        alert('Il tuo dispositivo non supporta il GPS.');
                        this.loading = false;
                    }
                },

                async checkUpdates() {
                    try {
                        let response = await fetch('{{ route("webapp.updates.check") }}');
                        let data = await response.json();
                        
                        if (data.new_notices > 0) {
                            this.hasNewNotice = true;
                            this.notifyUser("CloudGest Staff", "Hai un nuovo messaggio in bacheca!");
                        }
                        
                        if (data.new_reports > 0) {
                            this.hasNewReport = true;
                            this.notifyUser("CloudGest Staff", "C'è un nuovo rapporto da far firmare!");
                        }
                    } catch (e) { console.warn("Polling fallito"); }
                },

                requestPermission() {
    Notification.requestPermission().then(permission => {
        if (permission === "granted") {
            this.successMsg = "Notifiche attivate con successo!";
            setTimeout(() => this.successMsg = '', 3000);
        }
    });
},

notifyUser(title, body) {
    // 1. BANNER DI SISTEMA
    if ("Notification" in window && Notification.permission === "granted") {
        new Notification(title, { 
            body: body, 
            icon: '/logo.svg',
            silent: false // Chiede al sistema di non essere silenzioso
        });
    }

    // 2. SUONO SINTETIZZATO (Più robusto)
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        // Se il contesto è sospeso (comune su mobile), non facciamo nulla o proviamo a riprenderlo
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        // Suono tipo "Ding-Dong" (due note)
        oscillator.type = 'sine';
        
        // Prima nota
        oscillator.frequency.setValueAtTime(523.25, audioCtx.currentTime); // C5
        gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + 0.1);
        gainNode.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.3);

        // Seconda nota
        oscillator.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.3); // E5
        gainNode.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + 0.4);
        gainNode.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.7);

        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.8);
    } catch (e) {
        console.error("Audio fallito:", e);
    }
}
            }
        }
    </script>
</body>
</html>