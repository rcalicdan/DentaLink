@props([
    'pageTitle' => 'Dashboard',
    'showBranchFilter' => true
])

<header class="glass-effect p-4 flex flex-wrap items-center justify-between gap-4 sticky top-0 z-30 h-16">
    <div class="flex items-center">
        <button @click="toggleSidebarMobile()" class="md:hidden p-2 rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200 ml-2">{{ $pageTitle }}</h2>
    </div>
    
    <div class="flex items-center space-x-4">
        @if($showBranchFilter)
            <x-partials.branch-selector />
        @endif
        
        <x-partials.profile-dropdown />
    </div>
</header>