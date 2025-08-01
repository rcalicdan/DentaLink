@props([
    'wireTarget' => null,
    'buttonText' => 'Submit',
    'bgColor' => 'bg-indigo-600',
    'hoverColor' => 'hover:bg-indigo-700',
    'focusRing' => 'focus:ring-indigo-500',
    'minWidth' => 'min-w-[120px]',
    'type' => 'submit',
])

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => "px-4 py-2 {$bgColor} text-white rounded-md {$hoverColor} focus:outline-none focus:ring-2 focus:ring-offset-2 {$focusRing} {$minWidth} flex items-center justify-center"]) }}
    wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled"
    @if ($wireTarget) wire:target="{{ $wireTarget }}" @endif>

    <span wire:loading.remove>{{ __($buttonText) }}</span>
    <svg wire:loading class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
        </circle>
        <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
    </svg>
</button>
