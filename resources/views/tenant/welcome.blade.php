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
    
    <!-- PWA Setup -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#991b1b">
    <link rel="apple-touch-icon" href="/images/icon.svg">
    
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
                        primary: '#991b1b',
                        secondary: '#7f1d1d',
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                        'stagger-1': 'fadeInUp 0.8s ease-out 0.1s forwards',
                        'stagger-2': 'fadeInUp 0.8s ease-out 0.2s forwards',
                        'stagger-3': 'fadeInUp 0.8s ease-out 0.3s forwards',
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
            background-color: #fffafa;
            color: #0f172a;
        }

        /* Glassmorphism utility */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(153, 27, 27, 0.1);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(153, 27, 27, 0.05);
        }

        .portal-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .portal-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -12px rgba(153, 27, 27, 0.15);
            border-color: rgba(153, 27, 27, 0.3);
        }

        .portal-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(153, 27, 27, 0.05) 0%, rgba(127, 29, 29, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .portal-card:hover::after {
            opacity: 1;
        }
    </style>
</head>
<body class="antialiased min-h-screen relative overflow-x-hidden flex flex-col selection:bg-primary selection:text-white">
    
    <!-- Animated Background Blobs -->
    <div class="absolute top-0 -left-4 w-96 h-96 bg-red-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute top-0 -right-4 w-96 h-96 bg-rose-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-96 h-96 bg-orange-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

    <!-- Minimal Navigation -->
    <nav class="glass fixed w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 md:py-4 flex flex-wrap justify-between items-center gap-y-3">
            <div class="flex items-center gap-3">
                <span class="text-lg md:text-xl font-bold tracking-tight text-slate-900">{{ Str::title($tenant->id) }}</span>
            </div>
            
            <!-- Mobile Menu Items (Left aligned on small screens) -->
            <div class="flex items-center gap-4 md:gap-6 w-full sm:w-auto order-last sm:order-none justify-start sm:justify-end overflow-x-auto pb-1 sm:pb-0">
                <a href="/shop" class="text-sm font-medium text-slate-600 hover:text-primary transition-colors whitespace-nowrap">Admin</a>
                <a href="/reception" class="text-sm font-medium text-slate-600 hover:text-primary transition-colors whitespace-nowrap">Reception</a>
            </div>
        </div>
    </nav>

    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-4 md:px-6 pt-24 md:pt-32 pb-12 md:pb-20">
        <div class="max-w-5xl w-full">
            
            <!-- Header Section -->
            <div class="text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold tracking-tight mb-6 text-slate-900">
                    Welcome to <br class="hidden sm:block"/> <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">{{ Str::title($tenant->id) }}</span>
                </h1>
                <p class="text-base md:text-lg text-slate-500 font-body max-w-2xl mx-auto leading-relaxed px-2 mb-10">
                    Streamline your operations, manage walk-ins, and track staff performance from one central hub. Select the portal you want to visit.
                </p>
                <div class="flex flex-col sm:flex-row flex-wrap justify-center items-center gap-4">
                    <a href="/reception" class="px-8 py-4 w-full sm:w-auto rounded-xl bg-secondary hover:bg-secondary/90 text-white font-semibold text-lg transition-all duration-300 shadow-lg shadow-secondary/30 flex items-center justify-center gap-2 transform hover:-translate-y-1">
                        Open Reception
                    </a>
                    <a href="/staff" class="px-8 py-4 w-full sm:w-auto rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-lg transition-all duration-300 shadow-lg shadow-primary/30 flex items-center justify-center gap-2 transform hover:-translate-y-1">
                        Staff Portal
                    </a>
                    <a href="/shop" class="px-8 py-4 w-full sm:w-auto rounded-xl glass hover:bg-primary/5 text-slate-700 font-semibold text-lg transition-all duration-300 flex items-center justify-center gap-2">
                        Admin Dashboard
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-20 py-8 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium text-slate-400">
            <p>&copy; {{ date('Y') }} {{ Str::title($tenant->id) }}. Powered by Malyn POS.</p>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-primary transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-primary transition-colors">Support Center</a>
            </div>
        </div>
    </footer>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
</body>
</html>
