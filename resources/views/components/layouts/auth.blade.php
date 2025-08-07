@props([
    'title' => 'Nice Smile Dental Clinic',
    'pageTitle' => 'Authentication',
])

<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $pageTitle }} - Nice Smile Dental Clinic Admin Dashboard">
    <meta name="theme-color" content="#3b82f6">
    <title>{{ $title }}</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom Styles -->
    <style>
        .dental-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
        }

        .dental-pattern {
            background-image:
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(6, 182, 212, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(99, 102, 241, 0.05) 0%, transparent 50%);
        }

        .card-dental {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .card-dental {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-pulse-soft {
            animation: pulse-soft 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-soft {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }
    </style>

    <!-- Theme Script -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @livewireStyles
    @stack('styles')
</head>

<body class="font-sans bg-slate-50 dark:bg-slate-900 min-h-screen dental-pattern" x-data="authLayout()">
    <!-- Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-32 h-32 bg-blue-500/10 rounded-full blur-xl animate-pulse-soft"></div>
        <div class="absolute bottom-20 right-10 w-40 h-40 bg-cyan-500/10 rounded-full blur-xl animate-pulse-soft"
            style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl animate-pulse-soft"
            style="animation-delay: 4s;"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div
                    class="mx-auto h-20 w-20 flex items-center justify-center rounded-2xl dental-gradient shadow-xl animate-float">
                    <i class="fas fa-tooth text-white text-3xl"></i>
                </div>
                {{ $header ?? '' }}
            </div>

            <!-- Main Content -->
            <div class="card-dental rounded-2xl p-8 shadow-2xl animate-fade-in border">
                {{ $slot }}
            </div>

            {{-- <!-- Theme Toggle -->
            <div class="text-center">
                <button @click="toggleTheme()"
                    class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 backdrop-blur-sm">
                    <i :class="isDark ? 'fas fa-sun' : 'fas fa-moon'" class="mr-2"></i>
                    <span x-text="isDark ? 'Light Mode' : 'Dark Mode'"></span>
                </button>
            </div> --}}

            <!-- Footer Content -->
            {{ $footer ?? '' }}
        </div>
    </div>

    @livewireScripts

    <!-- Alpine.js Base Script -->
    <script>
        function authLayout() {
            return {
                isDark: document.documentElement.classList.contains('dark'),

                toggleTheme() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.theme = 'dark';
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.theme = 'light';
                    }
                }
            }
        }
    </script>

    @stack('scripts')
</body>

</html>
