<div x-data="attendanceSystem()" class="p-6 text-center">
    <h2 class="text-2xl font-bold mb-4">Ciao, {{ session('employee_name') }}</h2>
    <p class="text-gray-500 mb-6" id="current-time"></p>

    <button @click="handleClock()" 
            :class="isClockedIn ? 'bg-red-600' : 'bg-green-600'"
            class="w-48 h-48 rounded-full text-white font-bold text-xl shadow-2xl hover:scale-105 transition transform">
        <span x-text="isClockedIn ? 'FINE TURNO' : 'INIZIA TURNO'"></span>
    </button>

    <form id="clock-form" action="{{ route('employee.clock') }}" method="POST" style="display:none">
        @csrf
        <input type="hidden" name="coords" id="coords">
    </form>
</div>

<script>
    function attendanceSystem() {
        return {
            isClockedIn: {{ $isClockedIn ? 'true' : 'false' }},
            handleClock() {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition((position) => {
                        const coords = `${position.coords.latitude},${position.coords.longitude}`;
                        document.getElementById('coords').value = coords;
                        document.getElementById('clock-form').submit();
                    }, () => {
                        alert("Devi attivare il GPS per timbrare!");
                    });
                } else {
                    alert("Geolocalizzazione non supportata.");
                }
            }
        }
    }
</script>