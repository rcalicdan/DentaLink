@php
    $message = session('success') ?: session('error');
    $type = session('success') ? 'success' : (session('error') ? 'error' : null);
@endphp

<div x-data="{
    show: false,
    message: '',
    type: null,
    progress: 0,
    duration: 5000,
    interval: null,

    init() {
        this.checkForMessage();
        this.$wire.$on('show-message', (event) => {
            this.showMessage(event[0].message, event[0].type);
        });
        this.$wire.$watch('$refresh', () => {
            this.checkForMessage();
        });
    },

    checkForMessage() {
        @if($type && $message)
        this.showMessage('{{ addslashes(__($message)) }}', '{{ $type }}');
        @endif
    },

    showMessage(msg, msgType) {
        this.message = msg;
        this.type = msgType;
        this.show = true;
        this.startTimer();
    },

    startTimer() {
        this.progress = 0;
        const increment = 100 / (this.duration / 10);
        this.interval = setInterval(() => {
            this.progress += increment;
            if (this.progress >= 100) {
                this.progress = 100;
                this.show = false;
                clearInterval(this.interval);
            }
        }, 10);
    },

    resetTimer() {
        clearInterval(this.interval);
        this.progress = 0;
        this.show = true;
        this.startTimer();
    },

    dismiss() {
        this.show = false;
        clearInterval(this.interval);
    }
}" x-show="show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="transform opacity-0 translate-y-2"
    x-transition:enter-end="transform opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="transform opacity-100 translate-y-0"
    x-transition:leave-end="transform opacity-0 translate-y-2"
    class="fixed top-4 right-4 z-50 max-w-sm w-full rounded-lg shadow-lg overflow-hidden border-l-4 bg-white"
    :class="{
        'bg-green-100 border-green-500': type === 'success',
        'bg-red-100 border-red-500': type === 'error'
    }">

    <!-- Progress bar -->
    <div class="h-1 w-full bg-gray-200">
        <div class="h-full transition-all ease-linear duration-100"
            :class="{
                'bg-green-500': type === 'success',
                'bg-red-500': type === 'error'
            }"
            :style="`width: ${progress}%`"></div>
    </div>

    <div class="px-4 py-3 flex items-center justify-between">
        <div class="flex items-center">
            <!-- Success Icon -->
            <template x-if="type === 'success'">
                <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </template>

            <!-- Error Icon -->
            <template x-if="type === 'error'">
                <svg class="h-6 w-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </template>

            <span class="text-sm font-medium"
                :class="{
                    'text-green-800': type === 'success',
                    'text-red-800': type === 'error'
                }"
                x-text="message"></span>
        </div>

        <button @click="dismiss()" class="ml-4 text-gray-400 hover:text-gray-500 focus:outline-none">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

{{-- Keep the session-based flash message as fallback --}}
@php
    $message = session('success') ?: session('error');
    $type = session('success') ? 'success' : (session('error') ? 'error' : null);
@endphp

@if ($type && $message)
    @php
        $bgColor = $type === 'success' ? 'bg-green-100 border-green-500' : 'bg-red-100 border-red-500';
        $textColor = $type === 'success' ? 'text-green-800' : 'text-red-800';
        $iconColor = $type === 'success' ? 'text-green-500' : 'text-red-500';
    @endphp

    <div x-data="{
        show: true,
        progress: 0,
        duration: 5000,
        interval: null,
        startTimer() {
            this.progress = 0;
            const increment = 100 / (this.duration / 10);
            this.interval = setInterval(() => {
                this.progress += increment;
                if (this.progress >= 100) {
                    this.progress = 100;
                    this.show = false;
                    clearInterval(this.interval);
                }
            }, 10);
        },
        resetTimer() {
            clearInterval(this.interval);
            this.progress = 0;
            this.show = true;
            this.startTimer();
        }
    }" x-init="startTimer()" x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform opacity-0 translate-y-2"
        x-transition:enter-end="transform opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="transform opacity-100 translate-y-0"
        x-transition:leave-end="transform opacity-0 translate-y-2"
        class="fixed top-4 right-4 z-50 max-w-sm w-full rounded-lg shadow-lg overflow-hidden border-l-4 {{ $bgColor }} bg-white">
        <!-- Progress bar -->
        <div class="h-1 w-full bg-gray-200">
            <div class="h-full {{ $type === 'success' ? 'bg-green-500' : 'bg-red-500' }}" :style="`width: ${progress}%`"
                x-transition:enter="transition-all ease-linear duration-100"></div>
        </div>

        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center">
                @if ($type === 'success')
                    <svg class="h-6 w-6 {{ $iconColor }} mr-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @elseif ($type === 'error')
                    <svg class="h-6 w-6 {{ $iconColor }} mr-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                @endif
                <span class="text-sm font-medium {{ $textColor }}">{{ $message }}</span>
            </div>
            <button @click="show = false" class="ml-4 text-gray-400 hover:text-gray-500 focus:outline-none">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
    </div>
@endif
