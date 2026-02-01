<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Timbratura</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col">

    <div class="bg-white shadow p-4 flex justify-between items-center">
        <div>
            <span class="text-xs text-gray-500 block">Dipendente</span>
            <span class="font-bold text-gray-800">{{ session('employee_name') }}</span>
        </div>
        <form action="{{ route('employee.logout') }}" method="POST">
            @csrf
            <button class="text-red-600 text-sm font-bold border border-red-200 px-3 py-1 rounded hover:bg-red-50">Esci</button>
        </form>
    </div>

    <div class="flex-grow flex flex-col items-center justify-center p-6 text-center" x-data="clockSystem()">
        
        <div class="mb-8">
            <p class="text-gray-500 text-sm uppercase tracking-widest">{{ \Carbon\Carbon::now()->format('l, d F Y') }}</p>
            <h1 class="text-5xl font-mono font-bold text-gray-800 mt-2" x-text="time"></h1>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 w-full max-w-sm shadow">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6 w-full max-w-sm shadow">
                {{ $errors->first() }}
            </div>
        @endif

        @if($status === 'finished')
            <div class="bg-gray-200 p-8 rounded-full h-48 w-48 flex items-center justify-center shadow-inner">
                <span class="font-bold text-gray-500">TURNO<br>COMPLETATO</span>
            </div>
        @else
            <button @click="attemptClock()" 
                    class="h-56 w-56 rounded-full shadow-2xl flex flex-col items-center justify-center transition transform active:scale-95 duration-200 relative overflow-hidden group"
                    :class="'{{ $status }}' === 'enter' ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600'">
                
                <div x-show="loading" class="absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>

                <span class="text-white font-bold text-2xl drop-shadow-md">
                    {{ $status === 'enter' ? 'ENTRATA' : 'USCITA' }}
                </span>
                <span class="text-white text-xs mt-1 opacity-80">Premi per registrare</span>
            </button>
        @endif

        <form id="clock-form" action="{{ route('employee.clock') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="coords" id="coords">
        </form>

        <p class="mt-8 text-xs text-gray-400 max-w-xs">
            La posizione GPS verrà registrata per verificare la presenza in sede.
        </p>
    </div>

    <script>
        function clockSystem() {
            return {
                time: new Date().toLocaleTimeString('it-IT', {hour: '2-digit', minute:'2-digit'}),
                loading: false,
                init() {
                    setInterval(() => {
                        this.time = new Date().toLocaleTimeString('it-IT', {hour: '2-digit', minute:'2-digit'});
                    }, 1000);
                },
                attemptClock() {
                    this.loading = true;
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const coords = `${position.coords.latitude},${position.coords.longitude}`;
                                document.getElementById('coords').value = coords;
                                document.getElementById('clock-form').submit();
                            }, 
                            (error) => {
                                this.loading = false;
                                alert("Errore GPS: " + error.message + ". Assicurati di aver dato i permessi e riprova.");
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    } else {
                        this.loading = false;
                        alert("Il tuo browser non supporta la geolocalizzazione.");
                    }
                }
            }
        }
    </script>
</body>
</html>