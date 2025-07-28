@props([
    'label' => null,
    'name' => '',
    'options' => [],
    'value' => null,
    'disabled' => false,
    'wire' => null,
    'help' => null,
    'inline' => false,
])

@php
    $wireAttribute = $wire ? "wire:model{$wire}" : null;
    $hasError = $errors->has($name);
@endphp

<div class="form-group">
    @if ($label)
        <legend class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            {{ $label }}
        </legend>
    @endif

    <div class="{{ $inline ? 'flex flex-wrap gap-6' : 'space-y-3' }}">
        @foreach ($options as $optionValue => $optionLabel)
            @php $radioId = $name . '_' . $loop->index; @endphp

            <div class="flex items-center">
                <input type="radio" id="{{ $radioId }}" name="{{ $name }}" value="{{ $optionValue }}"
                    @if ($wireAttribute) {{ $wireAttribute }}="{{ $name }}" @endif
                    @if (!$wire && $value == $optionValue) checked @endif @if ($disabled) disabled @endif
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 transition-colors duration-200{{ $hasError ? ' border-red-500' : '' }}" />
                <label for="{{ $radioId }}"
                    class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                    {{ $optionLabel }}
                </label>
            </div>
        @endforeach
    </div>

    @if ($help && !$hasError)
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>
            {{ $message }}
        </p>
    @enderror
</div>
