<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Login Dipendenti</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col justify-center px-6">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold tracking-tight">Portale Staff</h1>
        <p class="text-gray-400">Accedi per iniziare il turno</p>
    </div>

    <form action="{{ route('webapp.login.post') }}" method="POST" class="space-y-6">
        @csrf
        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500 p-3 rounded text-center text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Matricola</label>
            <input type="text" name="registration_number" class="w-full bg-gray-800 border-gray-700 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 text-white text-lg placeholder-gray-600" placeholder="Es. M12345" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Password</label>
            <input type="password" name="password" class="w-full bg-gray-800 border-gray-700 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 text-white text-lg" required>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl text-lg shadow-lg shadow-indigo-500/30 transition">
            Entra
        </button>
    </form>
</body>
</html>