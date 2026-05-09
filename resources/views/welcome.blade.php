<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Malyn POS | Central Management</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- PWA Setup -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#991b1b">
    <link rel="apple-touch-icon" href="/images/icon.svg">
    
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
                        primary: '#991b1b',
                        secondary: '#7f1d1d',
                        dark: '#450a0a',
                        darker: '#180000',
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
            background-color: #fffafa; /* Very light maroon/white */
            color: #180000;
        }
        
        /* Glassmorphism utility */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(153, 27, 27, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(153, 27, 27, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(153, 27, 27, 0.3);
            box-shadow: 0 10px 30px -10px rgba(153, 27, 27, 0.2);
        }

        /* Ambient glowing orbs */
        .orb-1 {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(153,27,27,0.1) 0%, rgba(255,255,255,0) 70%);
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
            background: radial-gradient(circle, rgba(127,29,29,0.08) 0%, rgba(255,255,255,0) 70%);
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
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 md:py-4 flex flex-wrap justify-between items-center gap-y-3">
            <div class="flex items-center gap-2 md:gap-3">
                <span class="text-xl font-bold tracking-tight text-slate-900">Malyn<span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">POS</span></span>
            </div>
            
            <!-- Mobile Menu Items (Left aligned on small screens) -->
            <div class="flex items-center gap-4 md:gap-6 w-full sm:w-auto order-last sm:order-none justify-start sm:justify-end overflow-x-auto pb-1 sm:pb-0">
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col justify-center relative pt-24 md:pt-32 pb-12 md:pb-20">
        <div class="max-w-7xl mx-auto px-4 md:px-6 w-full">
            
            <!-- Hero Section -->
            <div class="flex flex-col items-center text-center max-w-4xl mx-auto mt-8">
                <div class="animate-fade-in-up w-full">
                    
                    <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold leading-tight mb-4 md:mb-6 tracking-tight text-slate-900">
                        Manage Your <br class="hidden sm:block">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-purple-500 to-secondary animate-gradient-x">Shops</span><br class="hidden sm:block">
                        In One Dashboard.
                    </h1>
                    
                    <p class="text-base md:text-lg font-body text-slate-600 mb-8 md:mb-10 leading-relaxed max-w-2xl mx-auto">
                        Simplify the operations of your entire barbershop network with our powerful, centralized management system.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center">
                        <a href="/admin" class="px-6 md:px-8 py-3 md:py-4 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-base md:text-lg transition-all duration-300 shadow-[0_0_40px_-10px_rgba(153,27,27,0.4)] hover:shadow-[0_0_60px_-15px_rgba(153,27,27,0.6)] flex items-center justify-center gap-2 md:gap-3 transform hover:-translate-y-1">
                            Access Admin Dashboard
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>


        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm font-body text-slate-400">
            <p>&copy; {{ date('Y') }} Malyn POS Multi-Tenant System. All rights reserved.</p>
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
