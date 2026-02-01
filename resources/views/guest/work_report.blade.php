<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapporto #{{ $report->number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">
    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Rapporto d'Intervento</h1>
            <p class="text-gray-500">Rif. #{{ $report->number }} del {{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</p>
            <p class="font-mono text-sm text-indigo-600 mt-2 bg-indigo-50 inline-block px-3 py-1 rounded">Codice: {{ $report->unique_code }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 text-center shadow">
                <strong class="font-bold text-xl block mb-2">✅ Grazie!</strong>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Dettagli Cliente</h3>
            </div>
            <div class="border-t border-gray-200 p-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900">{{ $report->client->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Indirizzo</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $report->client->address ?? '-' }}</dd>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Note Tecnico</dt>
                        <dd class="mt-1 text-sm text-gray-900 bg-yellow-50 p-2 rounded border border-yellow-100">{{ $report->notes ?? 'Nessuna nota.' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Interventi e Materiali</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrizione</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Q.tà</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($report->rows as $row)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $row->description }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right font-bold">{{ $row->quantity }} {{ $row->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($report->status != 'closed' && !session('success'))
            <div class="bg-white shadow sm:rounded-lg p-6 border-l-4 border-indigo-500">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Firma per accettazione</h3>
                
                <form action="{{ route('guest.report.sign', $report->unique_code) }}" method="POST" id="signForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nome e Cognome Firmatario</label>
                        <input type="text" name="signer_name" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Firma qui sotto:</label>
                        <div class="border-2 border-dashed border-gray-400 rounded-lg bg-gray-50 touch-none">
                            <canvas id="signature-pad" class="w-full h-48"></canvas>
                        </div>
                        <input type="hidden" name="signature" id="signature-input">
                        <button type="button" id="clear-pad" class="text-xs text-red-600 underline mt-1">Cancella e ridisegna</button>
                    </div>

                    <div class="mb-6">
                         <label class="inline-flex items-center">
                            <input type="checkbox" name="acceptance" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">In alternativa alla firma grafica, spunta qui per accettare i lavori eseguiti.</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition shadow-lg">
                        CONFERMA E FIRMA
                    </button>
                </form>
            </div>

            <script>
                var canvas = document.getElementById('signature-pad');
                
                // Adatta canvas al container
                function resizeCanvas() {
                    var ratio =  Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                }
                window.onresize = resizeCanvas;
                resizeCanvas();

                var signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)'
                });

                document.getElementById('clear-pad').addEventListener('click', function () {
                    signaturePad.clear();
                });

                document.getElementById('signForm').addEventListener('submit', function(e) {
                    if (!signaturePad.isEmpty()) {
                        document.getElementById('signature-input').value = signaturePad.toDataURL();
                    }
                });
            </script>
        @elseif($report->status == 'closed')
            <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-600 shadow-inner">
                <div class="mb-4">
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">Rapporto Firmato</h3>
                    <p class="text-sm">Il documento è stato chiuso correttamente il {{ $report->updated_at->format('d/m/Y') }} alle {{ $report->updated_at->format('H:i') }}.</p>
                </div>
                
                @if($report->customer_signature_path)
                    <div class="mb-6 p-4 bg-white rounded border border-gray-300 inline-block">
                        <p class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-widest">Firma Digitale</p>
                        <img src="{{ asset('storage/'.$report->customer_signature_path) }}" class="h-12 mx-auto">
                    </div>
                @endif

                <div class="mt-2">
                    <a href="{{ route('guest.report.download', $report->unique_code) }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Scarica Copia PDF
                    </a>
                    <p class="text-xs text-gray-500 mt-2">Versione cliente (senza dettagli economici)</p>
                </div>
            </div>
        @endif

        <div class="text-center mt-12">
            <a href="{{ url('/') }}" class="text-indigo-600 hover:underline text-sm font-bold">&larr; Torna alla Home</a>
        </div>
    </div>
</body>
</html>