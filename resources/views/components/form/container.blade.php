@props([
    'title' => null,
    'subtitle' => null,
    'method' => 'POST',
    'action' => '#',
    'enctype' => null
])

<div class="card p-6 animate-fade-in">
    @if($title)
        <div class="mb-6 border-b border-slate-200 dark:border-slate-600 pb-4">
            <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    <form method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" 
          @if($enctype) enctype="{{ $enctype }}" @endif {{ $attributes->merge(['class' => 'space-y-6']) }}>
        @if($method !== 'GET')
            @csrf
        @endif
        
        @if($method !== 'POST' && $method !== 'GET')
            @method($method)
        @endif

        {{ $slot }}
    </form>
</div>