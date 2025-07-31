<svg class="w-4 h-4 flex-shrink-0 {{ $sortColumn === $header['key'] ? 'text-blue-600' : 'text-gray-400' }}"
    fill="none" stroke="currentColor" viewBox="0 0 24 24">
    @if ($sortColumn === $header['key'])
        @if ($sortDirection === 'asc')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
        @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        @endif
    @else
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
    @endif
</svg>