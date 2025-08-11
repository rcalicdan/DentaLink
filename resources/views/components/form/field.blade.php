@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'value' => '',
    'wire' => null,
    'help' => null,
    'icon' => null,
    'prefix' => null,
    'suffix' => null,
    'rows' => 3,
    'options' => [],
    'multiple' => false,
    'accept' => null,
    'checked' => false,
])

@php
    $wireAttribute = $wire ? "wire:model{$wire}" : null;
    $inputId = $name ?: 'field_' . uniqid();
    $hasError = $errors->has($name);

    $checkboxClasses = [
        'w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600',
        $hasError ? 'border-red-500 dark:border-red-500' : '',
        $disabled ? 'opacity-60 cursor-not-allowed' : '',
    ];

    $inputClasses = [
        'w-full px-4 py-3 border rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500',
        'bg-white dark:bg-slate-700',
        'border-slate-300 dark:border-slate-600',
        'text-slate-900 dark:text-slate-100',
        'placeholder-slate-400 dark:placeholder-slate-500',
        $hasError ? 'border-red-500 dark:border-red-500 focus:ring-red-500/50 focus:border-red-500' : '',
        $disabled ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : '',
        $readonly ? 'bg-slate-50 dark:bg-slate-800' : '',
        $icon || $prefix ? 'pl-10' : '',
        $suffix ? 'pr-10' : '',
    ];
@endphp

<div class="form-group">
    @if ($type === 'checkbox')
        {{-- Checkbox layout --}}
        <div class="flex items-start space-x-3">
            <div class="flex items-center h-5 mt-1">
                <input type="checkbox" id="{{ $inputId }}" name="{{ $name }}" value="{{ $value ?: '1' }}"
                    @if ($wireAttribute) {{ $wireAttribute }}="{{ $name }}" @endif
                    @if ($checked || ($wire && old($name, $value))) checked @endif @if ($required) required @endif
                    @if ($disabled) disabled @endif
                    @if ($readonly) readonly @endif
                    {{ $attributes->merge(['class' => implode(' ', $checkboxClasses)]) }} />
            </div>
            @if ($label)
                <div class="text-sm">
                    <label for="{{ $inputId }}"
                        class="font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        {{ $label }}
                        @if ($required)
                            <span class="text-red-500 ml-1">*</span>
                        @endif
                    </label>
                    @if ($help && !$hasError)
                        <p class="text-slate-600 dark:text-slate-400 mt-1">{{ $help }}</p>
                    @endif
                </div>
            @endif
        </div>
    @else
        {{-- Regular field layout --}}
        @if ($label)
            <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                {{ $label }}
                @if ($required)
                    <span class="text-red-500 ml-1">*</span>
                @endif
            </label>
        @endif

        <div class="relative">
            @if ($icon || $prefix)
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    @if ($icon)
                        <i class="{{ $icon }} text-slate-400 dark:text-slate-500"></i>
                    @else
                        <span class="text-slate-500 dark:text-slate-400 text-sm">{{ $prefix }}</span>
                    @endif
                </div>
            @endif

            @if ($type === 'textarea')
                <textarea id="{{ $inputId }}" name="{{ $name }}" rows="{{ $rows }}"
                    placeholder="{{ $placeholder }}"
                    @if ($wireAttribute) {{ $wireAttribute }}="{{ $name }}" @endif
                    @if ($required) required @endif @if ($disabled) disabled @endif
                    @if ($readonly) readonly @endif
                    {{ $attributes->merge(['class' => implode(' ', $inputClasses)]) }}>{{ $wire ? '' : old($name, $value) }}</textarea>
            @elseif($type === 'select')
                <select id="{{ $inputId }}" name="{{ $multiple ? $name . '[]' : $name }}"
                    @if ($wireAttribute) {{ $wireAttribute }}="{{ $name }}" @endif
                    @if ($required) required @endif
                    @if ($disabled || $readonly) disabled @endif
                    @if ($multiple) multiple @endif
                    {{ $attributes->merge(['class' => implode(' ', $inputClasses)]) }}>

                    @if ($slot->isNotEmpty())
                        {{ $slot }}
                    @else
                        @foreach ($options as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}"
                                @if ($wire) @selected($optionValue == $value) @else @selected(old($name, $value) == $optionValue) @endif>
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    @endif
                </select>
            @elseif($type === 'file')
                <input type="file" id="{{ $inputId }}" name="{{ $multiple ? $name . '[]' : $name }}"
                    @if ($wireAttribute) {{ $wireAttribute }}="{{ $name }}" @endif
                    @if ($required) required @endif
                    @if ($disabled) disabled @endif
                    @if ($multiple) multiple @endif
                    @if ($accept) accept="{{ $accept }}" @endif
                    {{ $attributes->merge(['class' => implode(' ', $inputClasses)]) }} />
            @else
                <input type="{{ $type }}" id="{{ $inputId }}" name="{{ $name }}"
                    placeholder="{{ $placeholder }}" value="{{ $wire ? '' : old($name, $value) }}"
                    @if ($wireAttribute) {{ $wireAttribute }}="{{ $name }}" @endif
                    @if ($required) required @endif
                    @if ($disabled) disabled @endif
                    @if ($readonly) readonly @endif
                    {{ $attributes->merge(['class' => implode(' ', $inputClasses)]) }} />
            @endif

            @if ($suffix)
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-slate-500 dark:text-slate-400 text-sm">{{ $suffix }}</span>
                </div>
            @endif
        </div>

        @if ($help && !$hasError && $type !== 'checkbox')
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $help }}</p>
        @endif
    @endif

    @error($name)
        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>
            {{ $message }}
        </p>
    @enderror
</div>
