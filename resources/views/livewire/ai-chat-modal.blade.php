<div x-data="aiChatModal()" class="fixed bottom-6 right-6 z-50">
    <!-- Floating AI Button -->
    <button @click="toggleModal()"
        class="group relative bg-gradient-to-r from-purple-600 via-blue-600 to-cyan-500 hover:from-purple-700 hover:via-blue-700 hover:to-cyan-600 text-white rounded-full p-4 shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-purple-500/50">

        <!-- Animated Background Glow -->
        <div
            class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-400 to-cyan-400 opacity-75 blur-md group-hover:opacity-100 transition-opacity duration-300 animate-pulse">
        </div>

        <!-- AI Icon -->
        <div class="relative z-10 flex items-center justify-center w-6 h-6">
            <svg class="w-6 h-6 transform group-hover:rotate-12 transition-transform duration-300" fill="currentColor"
                viewBox="0 0 24 24">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
            </svg>
        </div>

        <!-- Notification Badge -->
        <div
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-bounce">
            <span class="text-xs font-bold">AI</span>
        </div>

        <!-- Tooltip -->
        <div
            class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-slate-900 dark:bg-slate-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
            Ask AI Assistant
            <div
                class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-slate-900 dark:border-t-slate-700">
            </div>
        </div>
    </button>

    <!-- AI Chat Modal -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="toggleModal()"></div>

        <!-- Modal Container -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.stop
                class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200 dark:border-slate-700">

                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 via-blue-600 to-cyan-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-white/20 rounded-full p-2">
                                <svg class="w-6 h-6 text-white" :class="{ 'animate-spin': $wire.isTyping }"
                                    fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">AI Assistant</h3>
                                <p class="text-white/80 text-sm">
                                    <span x-show="!$wire.isTyping">Powered by Gemini AI</span>
                                    <span x-show="$wire.isTyping" class="animate-pulse">Thinking...</span>
                                </p>
                            </div>
                        </div>
                        <button @click="toggleModal()"
                            class="text-white/80 hover:text-white transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Chat Container -->
                <div class="h-96 flex flex-col">
                    <!-- Messages Area -->
                    <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                        @foreach ($messages as $message)
                            @if ($message['role'] === 'assistant')
                                <!-- AI Message -->
                                <div class="flex items-start space-x-3" wire:key="message-{{ $loop->index }}">
                                    <div
                                        class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-3">
                                            <div class="text-slate-800 dark:text-slate-200 text-sm prose prose-sm max-w-none dark:prose-invert"
                                                style="white-space: pre-wrap;">{{ $message['content'] }}</div>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 mt-1 block">{{ $message['timestamp'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- User Message -->
                                <div class="flex items-start space-x-3 justify-end"
                                    wire:key="message-{{ $loop->index }}">
                                    <div class="flex-1 flex justify-end">
                                        <div
                                            class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg p-3 max-w-sm">
                                            <p class="text-white text-sm">
                                                {{ $message['content'] }}
                                            </p>
                                            <span
                                                class="text-xs text-white/70 mt-1 block">{{ $message['timestamp'] }}</span>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-full p-2 flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <!-- Loading Indicator with Spinner -->
                        <div x-show="$wire.isTyping" class="flex items-start space-x-3">
                            <div
                                class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
                                <svg class="w-4 h-4 text-white animate-spin" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                </svg>
                            </div>
                            <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-4">
                                <div class="flex items-center space-x-3">
                                    <!-- Animated spinner -->
                                    <div class="relative">
                                        <div
                                            class="w-8 h-8 border-4 border-purple-200 dark:border-purple-800 border-t-purple-600 dark:border-t-purple-400 rounded-full animate-spin">
                                        </div>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-slate-700 dark:text-slate-300 text-sm font-medium">AI is
                                            thinking...</span>
                                        <span class="text-slate-500 dark:text-slate-400 text-xs">This may take a few
                                            seconds</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="border-t border-slate-200 dark:border-slate-600 p-4">
                        <form wire:submit="sendMessage" class="flex items-center space-x-3">
                            <div class="flex-1 relative">
                                <input type="text" wire:model="userMessage"
                                    placeholder="Ask me anything about your clinic..." :disabled="$wire.isTyping"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-slate-800 dark:text-slate-200 placeholder-slate-500 dark:placeholder-slate-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @keydown.enter.prevent="if (!$wire.isTyping && $wire.userMessage.trim()) $wire.sendMessage()">
                            </div>
                            <button type="submit" :disabled="$wire.isTyping || !$wire.userMessage.trim()"
                                class="bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-full p-3 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-purple-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                <svg x-show="!$wire.isTyping" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <svg x-show="$wire.isTyping" class="w-5 h-5 animate-spin" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                </svg>
                            </button>
                        </form>

                        <!-- Actions Footer -->
                        <div class="flex items-center justify-between mt-3">
                            <!-- Quick Actions -->
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="sendQuickAction('stats')" :disabled="$wire.isTyping"
                                    class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full text-xs hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    📊 Today's Stats
                                </button>
                                <button wire:click="sendQuickAction('schedule')" :disabled="$wire.isTyping"
                                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    📅 Schedule Overview
                                </button>
                                <button wire:click="sendQuickAction('revenue')" :disabled="$wire.isTyping"
                                    class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full text-xs hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    💰 Revenue Report
                                </button>
                            </div>

                            <!-- Clear Chat Button -->
                            <div>
                                <button wire:click="clearChat" :disabled="$wire.isTyping"
                                    wire:confirm="Are you sure you want to clear the chat history?"
                                    class="inline-flex items-center px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-full text-xs hover:bg-slate-200 dark:hover:bg-slate-600 hover:text-slate-700 dark:hover:text-slate-300 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Clear Chat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('aiChatModal', () => ({
        isOpen: false,

        init() {
            this.$watch('$wire.messages', () => {
                this.$nextTick(() => this.scrollToBottom());
            });
            
            this.$watch('$wire.isTyping', () => {
                this.$nextTick(() => this.scrollToBottom());
            });
        },

        toggleModal() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                document.body.style.overflow = 'hidden';
                this.$nextTick(() => this.scrollToBottom());
            } else {
                document.body.style.overflow = 'auto';
            }
        },

        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
    }));
</script>
@endscript