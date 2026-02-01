<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CloudGest 2.0</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="icon" href="{{ asset('logo.svg') }}" type="image/svg">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans text-gray-900 bg-gray-50">

        <nav class="fixed w-full z-50 top-0 bg-white/80 backdrop-blur-md border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <x-application-logo class="block h-10 w-auto fill-current text-indigo-600" />
                        <span class="font-bold text-xl tracking-tight text-gray-800">CloudGest <span class="text-indigo-600">2.0</span></span>
                    </div>

                    <div class="flex items-center gap-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-indigo-600 transition">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-indigo-600 transition">Accedi</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-5 py-2.5 rounded-full font-medium hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition transform hover:-translate-y-0.5">
                                        Inizia Gratis
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>




        <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
            <div class="absolute top-0 left-1/2 w-full -translate-x-1/2 h-full z-0 pointer-events-none">
                <div class="absolute top-20 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
                <div class="absolute top-20 right-10 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-gray-900 mb-6">
                    Gestisci il tuo lavoro con <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">CloudGest 2.0</span>
                </h1>
                <p class="mt-4 text-xl text-gray-600 max-w-2xl mx-auto mb-10">
                    Dal preventivo alla fattura in pochi click. Rapporti d'intervento digitali, firma su tablet e gestione clienti semplificata. Tutto in un unico posto.
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-8 py-4 bg-indigo-600 text-white rounded-xl font-bold text-lg shadow-xl hover:bg-indigo-700 transition transform hover:-translate-y-1">
                            Vai alla Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-8 py-4 bg-indigo-600 text-white rounded-xl font-bold text-lg shadow-xl hover:bg-indigo-700 transition transform hover:-translate-y-1">
                            Registrati Ora
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-lg shadow-sm hover:bg-gray-50 transition">
                            Accedi
                        </a>
                    @endauth
                </div>

                <div class="mt-16 relative mx-auto max-w-5xl rounded-2xl shadow-2xl border border-gray-200 bg-white overflow-hidden transform rotate-1 hover:rotate-0 transition duration-500">
                    <div class="bg-gray-100 border-b border-gray-200 h-8 flex items-center px-4 space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="p-4 bg-gray-50">
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 h-24 rounded-lg shadow-md"></div>
                            <div class="bg-white h-24 rounded-lg shadow-sm border border-gray-100"></div>
                            <div class="bg-white h-24 rounded-lg shadow-sm border border-gray-100"></div>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div class="col-span-1 bg-white h-48 rounded-lg shadow-sm border border-gray-100"></div>
                            <div class="col-span-3 bg-white h-48 rounded-lg shadow-sm border border-gray-100"></div>
                        </div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent h-1/3 bottom-0"></div>
                </div>
            </div>
        </div>

     

        <div class=" relative z-20 -mt-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 margin-top:20px">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 flex flex-col md:flex-row items-center gap-6">
                <div class="flex-shrink-0 bg-indigo-100 p-4 rounded-full">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div class="flex-grow text-center md:text-left">
                    <h3 class="text-xl font-bold text-gray-900">Hai un codice intervento?</h3>
                    <p class="text-gray-500 text-sm">Inserisci il codice ricevuto dal tecnico per firmare e scaricare il rapporto.</p>
                </div>
                <div class="w-full md:w-auto">
                    <form action="{{ route('guest.search') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="Es. AB12XY99" class="uppercase w-full md:w-48 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-indigo-700 transition">
                            Vai
                        </button>
                    </form>
                    @error('code')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Funzionalità</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Tutto ciò che serve alla tua attività
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div class="group bg-gray-50 rounded-2xl p-8 hover:bg-white hover:shadow-xl transition duration-300 border border-transparent hover:border-gray-100">
                        <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Preventivi Veloci</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Crea preventivi professionali in pochi secondi. Seleziona il cliente, aggiungi gli articoli e invia il PDF.
                        </p>
                    </div>

                    <div class="group bg-gray-50 rounded-2xl p-8 hover:bg-white hover:shadow-xl transition duration-300 border border-transparent hover:border-gray-100">
                        <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Rapporti Digitali</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Compila i rapporti di lavoro sul posto. Firma digitale del cliente direttamente su tablet o smartphone.
                        </p>
                    </div>

                    <div class="group bg-gray-50 rounded-2xl p-8 hover:bg-white hover:shadow-xl transition duration-300 border border-transparent hover:border-gray-100">
                        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Fatturazione Automatica</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Converti preventivi e rapporti in fatture con un solo click. Tieni traccia dei pagamenti e delle scadenze.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-20 bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-extrabold mb-6">Un flusso di lavoro <br><span class="text-indigo-400">senza interruzioni</span></h2>
                        <ul class="space-y-6">
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-sm">1</div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-bold">Crea Preventivo</h4>
                                    <p class="text-gray-400 text-sm mt-1">Invia una proposta chiara e professionale al cliente.</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-sm">2</div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-bold">Esegui il Lavoro</h4>
                                    <p class="text-gray-400 text-sm mt-1">Compila il rapporto d'intervento e fai firmare il cliente sul posto.</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center font-bold text-sm">3</div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-bold">Fattura e Incassa</h4>
                                    <p class="text-gray-400 text-sm mt-1">Converti tutto in fattura e invia. Ciclo chiuso in pochi secondi.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 shadow-2xl relative">
                        <div class="space-y-3">
                            <div class="h-4 bg-gray-600 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-600 rounded w-1/2"></div>
                            <div class="h-24 bg-gray-700 rounded mt-4"></div>
                            <div class="flex justify-between mt-4">
                                <div class="h-8 w-24 bg-indigo-600 rounded"></div>
                                <div class="h-8 w-24 bg-green-600 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="bg-gray-50 border-t border-gray-200 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center gap-2 mb-4 md:mb-0">
                    <x-application-logo class="block h-8 w-auto fill-current text-gray-400" />
                    <span class="font-bold text-gray-500">CloudGest 2.0</span>
                </div>
                <div class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} CloudGest 2.0. Tutti i diritti riservati. Software Creato da Domenico Paradiso.
                </div>
            </div>
        </footer>

        <style>
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(30px, -50px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            .animate-blob {
                animation: blob 7s infinite;
            }
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            .animation-delay-4000 {
                animation-delay: 4s;
            }
        </style>
    </body>
</html>