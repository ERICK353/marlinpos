<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marlin POS | Central Management</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via CDN for standalone page, assuming Tailwind isn't fully compiled for the frontend yet) -->
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
                        primary: '#4f46e5',
                        secondary: '#db2777',
                        dark: '#0f172a',
                        darker: '#020617',
                    },
                    animation: {
                        'gradient-x': 'gradient-x 15s ease infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'fade-in-up': 'fadeInUp 1s ease-out forwards',
                    },
                    keyframes: {
                        'gradient-x': {
                            '0%, 100%': {
                                'background-size': '200% 200%',
                                'background-position': 'left center'
                            },
                            '50%': {
                                'background-size': '200% 200%',
                                'background-position': 'right center'
                            },
                        },
                        'float': {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
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
            background-color: #020617; /* Darkest slate */
            color: #f8fafc;
        }
        
        /* Glassmorphism utility */
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.3);
        }

        /* Ambient glowing orbs */
        .orb-1 {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(79,70,229,0.4) 0%, rgba(0,0,0,0) 70%);
            filter: blur(60px);
            z-index: -1;
            animation: float 8s ease-in-out infinite alternate;
        }
        
        .orb-2 {
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(219,39,119,0.3) 0%, rgba(0,0,0,0) 70%);
            filter: blur(80px);
            z-index: -1;
            animation: float 10s ease-in-out infinite alternate-reverse;
        }
    </style>
</head>
<body class="antialiased min-h-screen relative overflow-x-hidden selection:bg-primary selection:text-white flex flex-col">
    
    <!-- Background Ambient Effects -->
    <div class="orb-1 pointer-events-none"></div>
    <div class="orb-2 pointer-events-none"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] opacity-50 z-[-1] pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="glass fixed w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-pink-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-white">Marlin<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-pink-400">POS</span></span>
            </div>
            <div class="flex items-center gap-6">
                <a href="#features" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Features</a>
                <a href="/admin" class="group relative px-5 py-2.5 font-semibold text-white rounded-full bg-white/10 hover:bg-white/20 border border-white/10 transition-all duration-300 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-2">
                        Admin Portal
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col justify-center relative pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-6 w-full">
            
            <!-- Hero Section -->
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                <div class="max-w-2xl animate-fade-in-up">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-sm font-medium mb-6">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        Central Landlord System Active
                    </div>
                    
                    <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight mb-6 tracking-tight">
                        Next-Gen <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 animate-gradient-x">Multi-Tenant</span><br>
                        Architecture.
                    </h1>
                    
                    <p class="text-lg font-body text-slate-400 mb-10 leading-relaxed max-w-lg">
                        Manage thousands of independent barbershops and salons from a single, powerful landlord portal. Seamlessly provision databases, domains, and subscriptions instantly.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/admin" class="px-8 py-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-lg transition-all duration-300 shadow-[0_0_40px_-10px_rgba(79,70,229,0.6)] hover:shadow-[0_0_60px_-15px_rgba(79,70,229,0.8)] flex items-center justify-center gap-3 transform hover:-translate-y-1">
                            Access Admin Dashboard
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#features" class="px-8 py-4 rounded-xl glass hover:bg-white/5 text-slate-200 font-semibold text-lg transition-all duration-300 flex items-center justify-center gap-2">
                            Explore Features
                        </a>
                    </div>
                </div>
                
                <!-- Right Side Visual / Stats -->
                <div class="relative animate-fade-in-up" style="animation-delay: 0.2s;">
                    <!-- Dashboard Mockup/Visual -->
                    <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                        <!-- Decorative top bar -->
                        <div class="flex gap-2 mb-6 border-b border-white/10 pb-4">
                            <div class="w-3 h-3 rounded-full bg-rose-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                        </div>
                        
                        <!-- Mock stats -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-white/5 group-hover:border-indigo-500/30 transition-colors">
                                <p class="text-sm text-slate-400 mb-1 font-body">Active Tenants</p>
                                <p class="text-3xl font-bold text-white flex items-baseline gap-2">
                                    {{ \App\Models\Tenant::count() ?? '0' }}
                                    <span class="text-xs font-medium text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded-full">+12%</span>
                                </p>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-white/5 group-hover:border-pink-500/30 transition-colors">
                                <p class="text-sm text-slate-400 mb-1 font-body">System Health</p>
                                <p class="text-3xl font-bold text-white flex items-center gap-2">
                                    99.9%
                                    <span class="relative flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Mock chart area -->
                        <div class="h-32 bg-gradient-to-t from-indigo-500/20 to-transparent rounded-xl border border-indigo-500/20 relative flex items-end px-4 pb-4 gap-2">
                            <div class="w-full bg-indigo-500/40 rounded-t-sm h-[40%] hover:h-[45%] transition-all"></div>
                            <div class="w-full bg-indigo-500/60 rounded-t-sm h-[60%] hover:h-[65%] transition-all"></div>
                            <div class="w-full bg-indigo-500/80 rounded-t-sm h-[80%] hover:h-[85%] transition-all"></div>
                            <div class="w-full bg-indigo-400 rounded-t-sm h-[100%]"></div>
                            <div class="w-full bg-indigo-500/70 rounded-t-sm h-[75%] hover:h-[80%] transition-all"></div>
                        </div>
                    </div>
                    
                    <!-- Floating badges -->
                    <div class="absolute -right-6 top-1/4 glass-card px-4 py-3 rounded-2xl flex items-center gap-3 animate-float" style="animation-delay: 1s;">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-body">New Tenant</p>
                            <p class="text-sm font-semibold text-white">Database Provisioned</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div id="features" class="mt-40 grid md:grid-cols-3 gap-6 opacity-0 animate-fade-in-up" style="animation-delay: 0.4s; animation-fill-mode: forwards;">
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-2xl flex items-center justify-center text-indigo-400 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Database Isolation</h3>
                    <p class="text-slate-400 font-body text-sm leading-relaxed">
                        Every tenant gets a dedicated, secure database schema automatically provisioned upon registration. Complete data sovereignty.
                    </p>
                </div>
                
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 bg-pink-500/20 rounded-2xl flex items-center justify-center text-pink-400 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Custom Domains</h3>
                    <p class="text-slate-400 font-body text-sm leading-relaxed">
                        Effortlessly map custom domains or subdomains to individual tenant applications with dynamic routing.
                    </p>
                </div>

                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-2xl flex items-center justify-center text-amber-400 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Centralized Control</h3>
                    <p class="text-slate-400 font-body text-sm leading-relaxed">
                        Monitor health, manage subscriptions, and push updates to all shops seamlessly from your Filament landlord dashboard.
                    </p>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm font-body text-slate-500">
            <p>&copy; {{ date('Y') }} Marlin POS Multi-Tenant System. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
