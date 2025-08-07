@php
    $message = session('success') ?: session('error');
    $type = session('success') ? 'success' : (session('error') ? 'error' : null);
@endphp

<div x-data="{
    show: false,
    message: '',
    type: null,

    init() {
        // Move this element to body level to ensure proper positioning
        document.body.appendChild(this.$el);

        this.$nextTick(() => {
            this.checkForMessage();
        });

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

        setTimeout(() => {
            this.show = false;
        }, 8000); // Display for 8 seconds
    },

    dismiss() {
        this.show = false;
    }
}" x-show="show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="transform opacity-0 translate-y-2 scale-95"
    x-transition:enter-end="transform opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="transform opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="transform opacity-0 translate-y-2 scale-95"
    class="fixed top-4 right-4 max-w-sm rounded-lg shadow-2xl overflow-hidden"
    style="position: fixed !important; top: 1rem !important; right: 1rem !important; z-index: 9999 !important; pointer-events: auto !important;">

    <div class="px-4 py-3 flex items-center justify-between"
        :class="type === 'success' ? 'bg-green-50 border border-green-200 dark:bg-green-900 dark:border-green-700' :
            'bg-red-50 border border-red-200 dark:bg-red-900 dark:border-red-700'">
        <div class="flex items-center">
            <template x-if="type === 'success'">
                <svg class="h-6 w-6 text-green-500 dark:text-green-400 mr-3 flex-shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </template>
            <template x-if="type === 'error'">
                <svg class="h-6 w-6 text-red-500 dark:text-red-400 mr-3 flex-shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </template>
            <span class="text-sm font-medium pr-2"
                :class="type === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'"
                x-text="message"></span>
        </div>
        <button @click="dismiss()"
            class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded p-1 transition-colors duration-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Progress bar for auto-dismiss -->
    <div class="h-1 bg-gray-200 dark:bg-gray-700">
        <div class="h-full transition-all duration-[8000ms] ease-linear"
            :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'" x-show="show"
            x-transition:enter="transition-all duration-100" x-transition:enter-start="w-full"
            x-transition:enter-end="w-0">
        </div>
    </div>
</div>
