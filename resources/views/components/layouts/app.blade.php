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

    <!-- Livewire Scripts - Move before Alpine.js -->
    @livewireScripts

    <!-- Alpine.js Base Script -->
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

                init() {
                    this.initLivewireNavigation();
                },

                initLivewireNavigation() {
                    document.addEventListener('livewire:navigated', () => {
                        console.log('Page navigated');
                    });
                }
            }
        }
    </script>

    @stack('scripts')
</body>

</html>
