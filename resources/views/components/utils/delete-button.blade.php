@props(['wireClick' => 'delete', 'confirmMessage' => 'Are you sure you want to delete this item?'])

<button wire:click='{{ $wireClick }}' wire:confirm='{{ $confirmMessage }}'
    class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-1 focus:ring-red-500 focus:ring-offset-1 transition-all duration-200 ease-in-out group">
    <svg class="w-3 h-3 mr-1 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor"
        viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
        </path>
    </svg>
    {{ __('Delete') }}
</button>