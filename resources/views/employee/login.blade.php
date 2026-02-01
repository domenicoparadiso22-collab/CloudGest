<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accesso Dipendenti</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Portale Timbrature</h1>
            <p class="text-gray-500 text-sm">Inserisci le tue credenziali</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('employee.login.post') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Matricola</label>
                <input type="text" name="registration_number" class="w-full border rounded p-2 focus:ring focus:ring-indigo-200" placeholder="Es. DIP-XY123" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full border rounded p-2 focus:ring focus:ring-indigo-200" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded hover:bg-indigo-700 transition">
                ACCEDI
            </button>
        </form>
    </div>
</body>
</html>