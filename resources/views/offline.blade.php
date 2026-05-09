<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline | Malyn POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #fffafa; }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center text-slate-800">
    <div class="text-center px-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-[#991b1b] mb-6 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <h1 class="text-3xl font-bold mb-4">You're Offline</h1>
        <p class="text-slate-500 mb-8 max-w-md mx-auto">
            It looks like you've lost your internet connection. Malyn POS requires an active connection to sync transactions.
        </p>
        <button onclick="window.location.reload()" class="px-6 py-3 bg-[#991b1b] text-white font-semibold rounded-xl hover:bg-[#7f1d1d] transition">
            Try Again
        </button>
    </div>
</body>
</html>
