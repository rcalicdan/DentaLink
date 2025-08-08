@if (isset($header['type']) && $header['type'] === 'enum_badge')
    {{-- New enum badge handling --}}
    @php
        $badgeClass = $this->getEnumBadgeClass($value);
        $displayText = $this->getEnumDisplayName($value);
        $icon = $this->getEnumIcon($value);
    @endphp
    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
        <i class="fas fa-{{ $icon }} mr-1"></i>
        {{ __($displayText) }}
    </span>
@elseif (isset($header['type']) && $header['type'] === 'badge')
    {{-- Existing badge handling with enum compatibility --}}
    @php
        if (is_array($value)) {
            // Array format: ['text' => 'Display Text', 'class' => 'css-classes']
            $displayText = $value['text'] ?? ($value['label'] ?? '');
            $badgeClass = $value['class'] ?? $this->getBadgeClass($value);
            $showIcon = isset($value['icon']) ? $value['icon'] : null;
        } elseif (is_object($value) && method_exists($value, 'getDisplayName')) {
            // Enum object - use enum methods but in regular badge style (no icon)
            $displayText = $value->getDisplayName();
            $badgeClass = $value->getBadgeClass();
            $showIcon = null;
        } else {
            // Regular string/value
            $displayText = $this->getBadgeDisplayText($value);
            $badgeClass = $this->getBadgeClass($value);
            $showIcon = null;
        }
    @endphp
    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
        @if ($showIcon)
            <i class="fas fa-{{ $showIcon }} mr-1"></i>
        @endif
        {{ __($displayText) }}
    </span>
@elseif(isset($header['type']) && $header['type'] === 'boolean')
    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getBooleanBadgeClass($value) }}">
        {{ __($this->formatValue($header, $value)) }}
    </span>
@elseif(isset($header['type']) && $header['type'] === 'image')
    @if ($value)
        <img src="{{ $value }}" alt="{{ __('Image') }}" class="h-8 w-8 rounded-full object-cover">
    @else
        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    @endif
@else
    <span class="break-words">{{ __($this->formatValue($header, $value)) }}</span>
@endif
