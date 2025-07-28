@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'wire' => null,
])

@php
    $baseClasses = [
        'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]',
    ];

    $variantClasses = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-md hover:shadow-lg focus:ring-blue-500',
        'secondary' => 'bg-slate-600 hover:bg-slate-700 text-white shadow-md hover:shadow-lg focus:ring-slate-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white shadow-md hover:shadow-lg focus:ring-green-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-md hover:shadow-lg focus:ring-red-500',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white shadow-md hover:shadow-lg focus:ring-yellow-500',
        'outline' =>
            'border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-slate-500',
        'ghost' => 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 focus:ring-slate-500',
    ];

    $sizeClasses = [
        'xs' => 'px-3 py-1.5 text-xs',
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-base',
        'xl' => 'px-10 py-5 text-lg',
    ];

    $classes = array_merge(
        $baseClasses,
        [$variantClasses[$variant] ?? $variantClasses['primary']],
        [$sizeClasses[$size] ?? $sizeClasses['md']],
    );

    $wireAttributes = [];
    if ($wire) {
        if (is_string($wire)) {
            $wireAttributes['wire:click'] = $wire;
        } elseif (is_array($wire)) {
            foreach ($wire as $key => $value) {
                $wireAttributes["wire:{$key}"] = $value;
            }
        }
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}
        @if ($disabled) aria-disabled="true" tabindex="-1" @endif>
        @if ($loading)
            <i class="fas fa-spinner fa-spin mr-2"></i>
        @elseif($icon && $iconPosition === 'left')
            <i class="{{ $icon }} mr-2"></i>
        @endif

        {{ $slot }}

        @if ($icon && $iconPosition === 'right')
            <i class="{{ $icon }} ml-2"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}
        @if ($disabled || $loading) disabled @endif
        @foreach ($wireAttributes as $attr => $value)
                {{ $attr }}="{{ $value }}" @endforeach
        @if ($loading) wire:loading.attr="disabled" @endif>

        @if ($loading)
            <i class="fas fa-spinner fa-spin mr-2" wire:loading></i>
        @elseif($icon && $iconPosition === 'left')
            <i class="{{ $icon }} mr-2" wire:loading.remove></i>
        @endif

        <span @if ($loading) wire:loading.remove @endif>{{ $slot }}</span>
        <span wire:loading wire:target="{{ $wire }}">Processing...</span>

        @if ($icon && $iconPosition === 'right')
            <i class="{{ $icon }} ml-2" wire:loading.remove></i>
        @endif
    </button>
@endif
