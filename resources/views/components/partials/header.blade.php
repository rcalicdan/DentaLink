@php
    if(auth()->user()->isAdmin()) {
        $pageTitle = 'Admin Dashboard';
    } elseif(auth()->user()->isEmployee()) {
        $pageTitle = 'Employee Dashboard';
    } else {
        $pageTitle = 'Superadmin Dashboard';
    }
@endphp

@props([
    'pageTitle' => $pageTitle,
    'showBranchFilter' => true,
])

<header class="glass-effect p-4 sticky top-0 z-30 min-h-16">
    <!-- Main header row -->
    <div class="flex items-center justify-between gap-4 h-8">
        <div class="flex items-center">
            <button @click="toggleSidebarMobile()"
                class="md:hidden p-2 rounded-sm text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
                <i class="fas fa-bars"></i>
            </button>
            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200 ml-2">{{ $pageTitle }}</h2>
        </div>

        <!-- Desktop controls -->
        <div class="hidden sm:flex items-center space-x-4">
            @if ($showBranchFilter)
                <x-partials.branch-selector />
            @endif

            <x-partials.profile-dropdown />
        </div>

        <!-- Mobile profile dropdown only -->
        <div class="sm:hidden">
            <x-partials.profile-dropdown />
        </div>
    </div>

    <!-- Mobile branch selector row (when enabled) -->
    @if ($showBranchFilter)
        <div class="sm:hidden mt-3 pt-3 border-t border-slate-200 dark:border-slate-600">
            <x-partials.branch-selector />
        </div>
    @endif
</header>
