<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ Str::title($tenant->id) }} | Portal</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0ea5e9',
                        secondary: '#8b5cf6',
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #fafafa;
            color: #0f172a;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
        }

        .action-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center relative overflow-hidden selection:bg-primary selection:text-white">
    
    <!-- Animated Background Blobs -->
    <div class="absolute top-0 -left-4 w-72 h-72 bg-sky-300 rounded-full mix-blend-multiply filter blur-2xl opacity-40 animate-blob"></div>
    <div class="absolute top-0 -right-4 w-72 h-72 bg-violet-300 rounded-full mix-blend-multiply filter blur-2xl opacity-40 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-emerald-300 rounded-full mix-blend-multiply filter blur-2xl opacity-40 animate-blob animation-delay-4000"></div>

    <div class="max-w-4xl w-full px-6 relative z-10 animate-fade-in-up">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-sky-400 to-violet-500 text-white shadow-xl shadow-sky-500/20 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-slate-900">
                Welcome to <br/> <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-500 to-violet-500">{{ Str::title($tenant->id) }}</span>
            </h1>
            <p class="text-lg text-slate-500 font-body max-w-xl mx-auto">
                Select your designated portal below to securely access the daily operations, reporting, and staff management dashboards.
            </p>
        </div>

        <!-- Portals Grid -->
        <div class="grid md:grid-cols-3 gap-6">
            
            <!-- Shop Dashboard (Admin) -->
            <a href="/shop" class="action-card glass-panel rounded-3xl p-8 flex flex-col items-center text-center group border-t-4 border-t-violet-500">
                <div class="w-14 h-14 bg-violet-100 rounded-full flex items-center justify-center text-violet-600 mb-6 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Shop Owner</h3>
                <p class="text-sm text-slate-500 font-body mb-6 flex-grow">Complete oversight of analytics, staff commissions, and business settings.</p>
                <div class="inline-flex items-center justify-center w-full py-3 px-4 rounded-xl bg-slate-900 text-white font-medium text-sm group-hover:bg-violet-600 transition-colors">
                    Access Portal &rarr;
                </div>
            </a>

            <!-- Reception Dashboard -->
            <a href="/reception" class="action-card glass-panel rounded-3xl p-8 flex flex-col items-center text-center group border-t-4 border-t-sky-500">
                <div class="w-14 h-14 bg-sky-100 rounded-full flex items-center justify-center text-sky-600 mb-6 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Reception</h3>
                <p class="text-sm text-slate-500 font-body mb-6 flex-grow">Manage walk-ins, process checkouts, and print customer receipts.</p>
                <div class="inline-flex items-center justify-center w-full py-3 px-4 rounded-xl bg-slate-900 text-white font-medium text-sm group-hover:bg-sky-600 transition-colors">
                    Access Portal &rarr;
                </div>
            </a>

            <!-- Staff Dashboard -->
            <a href="/staff" class="action-card glass-panel rounded-3xl p-8 flex flex-col items-center text-center group border-t-4 border-t-emerald-500">
                <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Staff View</h3>
                <p class="text-sm text-slate-500 font-body mb-6 flex-grow">Check daily earnings, active commission rates, and assigned tasks.</p>
                <div class="inline-flex items-center justify-center w-full py-3 px-4 rounded-xl bg-slate-900 text-white font-medium text-sm group-hover:bg-emerald-600 transition-colors">
                    Access Portal &rarr;
                </div>
            </a>

        </div>

        <div class="mt-12 text-center">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-500 text-sm font-medium border border-slate-200">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                Secure Tenant Domain
            </span>
        </div>
    </div>
</body>
</html>
