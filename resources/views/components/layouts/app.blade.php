@php
    $pageTitle = '';
    if (auth()->user()->isAdmin()) {
        $pageTitle = 'Admin Dashboard';
    } elseif (auth()->user()->isEmployee()) {
        $pageTitle = 'Employee Dashboard';
    } elseif (auth()->user()->isSuperadmin()) {
        $pageTitle = 'Superadmin Dashboard';
    }
@endphp

@props([
    'title' => 'Nice Smile Dental Clinic',
    'pageTitle' => $pageTitle,
    'showBranchFilter' => false,
])

<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Responsive admin dashboard for Nice Smile Dental Clinic.">
    <meta name="theme-color" content="#3b82f6">
    <title>{{ $title }}</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

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

<body class="font-sans text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-900" x-data="adminLayout()">
    <div class="flex h-screen overflow-hidden">
        <x-partials.sidebar />

        <div class="fixed inset-0 bg-transparent z-40 md:hidden" x-show="sidebarOpen" @click="toggleSidebarMobile()"
            x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="flex-1 flex flex-col main-content overflow-y-auto"
            :class="{
                'md:ml-64': !sidebarCollapsed,
                'md:ml-20': sidebarCollapsed
            }">

            <x-partials.header :page-title="$pageTitle" :show-branch-filter="$showBranchFilter" />

            <main class="p-4 sm:p-6 lg:p-8 flex-grow">
                {{ $slot }}
            </main>
        </div>
    </div>

    <livewire:auth.logout />
    <livewire:placeholder />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @livewireScripts

    <script>
        function adminLayout() {
            return {
                sidebarOpen: false,
                sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed')) || false,
                toggleSidebarDesktop() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('sidebarCollapsed', JSON.stringify(this.sidebarCollapsed));
                },
                toggleSidebarMobile() {
                    this.sidebarOpen = !this.sidebarOpen;
                },
                init() {},
            }
        }
    </script>

    @stack('scripts')
</body>

</html>
