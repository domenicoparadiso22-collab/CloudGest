<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profilo Aziendale') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <strong class="font-bold">Ottimo!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                    
                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Dati Generali</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ragione Sociale *</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Sottotitolo (es. Impianti Elettrici)</label>
                                <input type="text" name="subtitle" value="{{ old('subtitle', $settings->subtitle) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recapiti e Dati Fiscali</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Partita IVA</label>
                                <input type="text" name="vat_number" value="{{ old('vat_number', $settings->vat_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Codice Fiscale</label>
                                <input type="text" name="fiscal_code" value="{{ old('fiscal_code', $settings->fiscal_code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">Indirizzo Completo</label>
                                <input type="text" name="address" value="{{ old('address', $settings->address) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Aziendale</label>
                                <input type="email" name="email" value="{{ old('email', $settings->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">PEC</label>
                                <input type="email" name="pec" value="{{ old('pec', $settings->pec) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefono</label>
                                <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="border-2 border-dashed border-gray-300 p-6 rounded-lg text-center bg-gray-50">
                            <label class="block text-lg font-medium text-gray-700 mb-2">Logo Applicazione</label>
                            <p class="text-xs text-gray-500 mb-4">Apparirà nell'intestazione e nei documenti</p>
                            
                            @if($settings->logo_path)
                                <div class="mb-4">
                                    <img src="{{ asset('storage/' . $settings->logo_path) }}" class="h-24 mx-auto object-contain bg-white p-2 border rounded">
                                </div>
                            @endif
                            <input type="file" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="border-2 border-dashed border-gray-300 p-6 rounded-lg text-center bg-gray-50">
                            <label class="block text-lg font-medium text-gray-700 mb-2">Timbro Aziendale</label>
                            <p class="text-xs text-gray-500 mb-4">Verrà usato per firmare i PDF</p>
                            
                            @if($settings->stamp_path)
                                <div class="mb-4">
                                    <img src="{{ asset('storage/' . $settings->stamp_path) }}" class="h-24 mx-auto object-contain bg-white p-2 border rounded">
                                </div>
                            @endif
                            <input type="file" name="stamp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 border-t mt-6">
                        <button type="submit" class="bg-indigo-600 text-white font-bold text-lg px-8 py-3 rounded-lg shadow hover:bg-indigo-700 transition duration-150 ease-in-out">
                            SALVA DATI AZIENDALI
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>