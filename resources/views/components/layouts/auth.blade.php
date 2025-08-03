@props([
    'title' => 'Nice Smile Dental Clinic',
    'pageTitle' => 'Authentication'
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

<body class="font-sans bg-slate-50 dark:bg-slate-900 min-h-screen" x-data="authLayout()">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full sidebar-gradient">
                    <i class="fas fa-tooth text-white text-2xl"></i>
                </div>
                {{ $header ?? '' }}
            </div>

            <!-- Main Content -->
            <div class="card p-8 animate-fade-in">
                {{ $slot }}
            </div>

            <!-- Theme Toggle -->
            <div class="text-center">
                <button @click="toggleTheme()"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors">
                    <i :class="isDark ? 'fas fa-sun' : 'fas fa-moon'" class="mr-2"></i>
                    <span x-text="isDark ? 'Light Mode' : 'Dark Mode'"></span>
                </button>
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