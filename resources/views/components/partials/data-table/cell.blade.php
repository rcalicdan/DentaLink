@if (isset($header['type']) && $header['type'] === 'badge')
    @php
        if (is_array($value)) {
            $displayText = $value['text'] ?? '';
            $badgeClass = $value['class'] ?? $this->getBadgeClass($value);
        } else {
            $displayText = $this->formatValue($header, $value);
            $badgeClass = $this->getBadgeClass($value);
        }
    @endphp
    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
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
