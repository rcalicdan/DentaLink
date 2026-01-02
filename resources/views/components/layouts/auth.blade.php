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
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">
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

            <!-- Flash Message Box -->
            @if (session('flash_message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-90"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-90" @class([
                        'border px-4 py-3 rounded-lg text-center relative',
                        'bg-red-100 border-red-400 text-red-700 dark:bg-red-900/20 dark:border-red-600 dark:text-red-300' =>
                            session('flash_type') === 'error',
                        'bg-green-100 border-green-400 text-green-700 dark:bg-green-900/20 dark:border-green-600 dark:text-green-300' =>
                            session('flash_type') === 'success',
                        'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900/20 dark:border-blue-600 dark:text-blue-300' =>
                            session('flash_type') === 'info' || !session('flash_type'),
                    ])>

                    {{ session('flash_message') }}
                    <button @click="show = false"
                        class="absolute top-2 right-3 text-xl leading-none hover:opacity-75">&times;</button>
                </div>
            @endif

            <!-- Main Content -->
            <div class="card-dental rounded-2xl p-8 shadow-2xl animate-fade-in border">
                {{ $slot }}
            </div>

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
