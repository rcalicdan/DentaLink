@props([
    'title' => 'Nice Smile Dental Clinic',
    'pageTitle' => 'Dashboard',
    'showBranchFilter' => true,
])

<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Responsive admin dashboard for Nice Smile Dental Clinic.">
    <meta name="theme-color" content="#3b82f6">
    <title>{{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
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

    <!-- Custom Styles -->
    <style>
        :root {
            --sidebar-width: 256px;
            --sidebar-width-collapsed: 80px;

            /* Brand & Theme Colors */
            --c-brand: #3b82f6;
            --c-brand-light: #60a5fa;
            --c-brand-dark: #2563eb;

            /* Light Theme */
            --c-bg-light: #f1f5f9;
            --c-surface-light: #ffffff;
            --c-glass-bg-light: rgba(255, 255, 255, 0.65);
            --c-border-light: #e2e8f0;

            /* Dark Theme */
            --c-bg-dark: #0f172a;
            --c-surface-dark: #1e293b;
            --c-glass-bg-dark: rgba(15, 23, 42, 0.65);
            --c-border-dark: #334155;
        }

        body {
            background-color: var(--c-bg-light);
        }

        .dark body {
            background-color: var(--c-bg-dark);
        }

        .sidebar-gradient {
            background: linear-gradient(180deg, var(--c-brand-dark) 0%, var(--c-brand) 100%);
        }

        .card {
            background-color: var(--c-surface-light);
            border: 1px solid var(--c-border-light);
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05);
            border-radius: 0.75rem;
        }

        .dark .card {
            background-color: var(--c-surface-dark);
            border: 1px solid var(--c-border-dark);
        }

        .glass-effect {
            background-color: var(--c-glass-bg-light);
            backdrop-filter: blur(12px) saturate(180%);
            border-bottom: 1px solid var(--c-border-light);
        }

        .dark .glass-effect {
            background-color: var(--c-glass-bg-dark);
            border-bottom: 1px solid var(--c-border-dark);
        }

        .sidebar {
            width: var(--sidebar-width);
            transition: width 0.3s ease, transform 0.3s ease;
        }

        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .sidebar-profile-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-item {
            justify-content: center;
        }

        .main-content {
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 768px) {
            .md\:ml-64 {
                margin-left: var(--sidebar-width);
            }

            .md\:ml-20 {
                margin-left: var(--sidebar-width-collapsed);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>

<body class="font-sans text-slate-800 dark:text-slate-200" x-data="adminLayout()">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <x-partials.sidebar />

        <!-- Sidebar Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" x-show="sidebarOpen"
            @click="toggleSidebarMobile()" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col main-content"
            :class="{
                'md:ml-64': !sidebarCollapsed,
                'md:ml-20': sidebarCollapsed
            }">

            <!-- Header -->
            <x-partials.header :page-title="$pageTitle" :show-branch-filter="$showBranchFilter" />

            <!-- Main Content Area -->
            <main class="p-4 sm:p-6 lg:p-8 flex-1 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Alpine.js Base Script -->
    <script>
        function adminLayout() {
            return {
                sidebarOpen: false,
                sidebarCollapsed: false,

                toggleSidebarDesktop() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                },

                toggleSidebarMobile() {
                    this.sidebarOpen = !this.sidebarOpen;
                },

                init() {
                    // Initialize any setup here
                }
            }
        }
    </script>

    @stack('scripts')
    @livewireScripts
</body>

</html>
