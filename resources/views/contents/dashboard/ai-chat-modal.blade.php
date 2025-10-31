<div x-data="aiChatModal()" class="fixed bottom-6 right-6 z-50">
    <!-- Floating AI Button -->
    @include('contents.dashboard.partials.floating-icon')

    <!-- AI Chat Modal -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="toggleModal()"></div>

        <!-- Modal Container -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.stop x-ref="modal"
                {{-- THE CHANGE IS ON THIS LINE: Changed max-w-lg md:max-w-3xl to max-w-2xl --}}
                class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200 dark:border-slate-700"
                style="min-width: 320px; min-height: 400px;">

                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 via-blue-600 to-cyan-500 px-6 py-4 cursor-move"
                    @mousedown="startDrag">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-white/20 rounded-full p-2">
                                <svg class="w-6 h-6 text-white" :class="{ 'animate-spin': isStreaming }"
                                    fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">AI Assistant</h3>
                                <p class="text-white/80 text-sm">
                                    <span x-show="!isStreaming">Powered by Gemini AI</span>
                                    <span x-show="isStreaming" class="animate-pulse">Thinking...</span>
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
                @include('contents.dashboard.partials.chat-container')
            </div>
        </div>
    </div>
</div>

@include('contents.dashboard.partials.ai-model-scripts')