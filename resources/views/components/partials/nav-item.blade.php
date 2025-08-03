@props([
    'href' => '#',
    'icon' => 'fas fa-circle',
    'active' => false,
])

<li class="sidebar-item">
    <a wire:navigate href="{{ $href }}"
        class="flex items-center p-3 rounded-lg transition-colors {{ $active ? 'bg-blue-700/80 shadow-lg' : 'hover:bg-blue-700/50' }}">
        <i class="{{ $icon }} w-6 text-center"></i>
        <span class="ml-4 sidebar-text" x-show="!sidebarCollapsed">{{ $slot }}</span>
    </a>
</li>
