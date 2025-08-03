@props([
    'href' => '#',
    'buttonText' => 'Cancel',
    'bgColor' => 'bg-gray-300',
    'textColor' => 'text-gray-700',
    'hoverColor' => 'hover:bg-gray-400',
    'focusRing' => 'focus:ring-gray-500',
    'spacing' => 'ml-2',
])

<a wire:navigate href="{{ $href }}"
    {{ $attributes->merge(['class' => "inline-flex items-center px-4 py-2 {$bgColor} {$textColor} rounded-md {$hoverColor} focus:outline-none focus:ring-2 focus:ring-offset-2 {$focusRing} transition {$spacing}"]) }}>
    {{ __($buttonText) }}
</a>
