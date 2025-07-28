@props([
    'label' => null,
    'name' => '',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'wire' => null,
    'help' => null,
])

@php
    $wireAttribute = $wire ? "wire:model{$wire}" : null;
    $inputId = $name ?: 'checkbox_' . uniqid();
    $hasError = $errors->has($name);
@endphp

<div class="form-group">
    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input type="checkbox" id="{{ $inputId }}" name="{{ $name }}" value="{{ $value }}"
                @if ($wireAttribute) {{ $wireAttribute }}="{{ $name }}" @endif
                @if (!$wire && $checked) checked @endif @if ($disabled) disabled @endif
                {{ $attributes->merge([
                    'class' =>
                        'w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500
                            dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600
                            transition-colors duration-200' . ($hasError ? ' border-red-500' : ''),
                ]) }} />
        </div>

        @if ($label)
            <div class="ml-3 text-sm">
                <label for="{{ $inputId }}" class="font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                    {{ $label }}
                </label>
                @if ($help)
                    <p class="text-slate-600 dark:text-slate-400 mt-1">{{ $help }}</p>
                @endif
            </div>
        @endif
    </div>

    @error($name)
        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>
            {{ $message }}
        </p>
    @enderror
</div>
