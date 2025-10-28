<!-- Chat Container -->
<div class="h-96 flex flex-col">
    <!-- Messages Area -->
    <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
        <!-- Welcome Message (only if no messages) -->
        <template x-if="messages.length === 0">
            <div class="flex items-start space-x-3">
                <div class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                    </svg>
                </div>
                <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-3 max-w-sm">
                    <p class="text-slate-800 dark:text-slate-200 text-sm">
                        Hello! I'm your AI assistant. How can I help you with your dental clinic
                        today?
                    </p>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Just now</span>
                </div>
            </div>
        </template>

        <!-- Dynamic Messages -->
        <template x-for="(msg, index) in messages" :key="index">
            <div>
                <!-- User Message -->
                <template x-if="msg.role === 'user'">
                    <div class="flex items-start space-x-3 justify-end">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg p-3 max-w-sm">
                            <p class="text-white text-sm" x-text="msg.content"></p>
                            <span class="text-xs text-white/70" x-text="msg.timestamp"></span>
                        </div>
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-full p-2 flex-shrink-0">
                            <i class="fas fa-user w-4 h-4 text-white"></i>
                        </div>
                    </div>
                </template>

                <!-- AI Message -->
                <template x-if="msg.role === 'assistant'">
                    <div class="flex items-start space-x-3">
                        <div class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                            </svg>
                        </div>
                        <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-3 max-w-sm">
                            <div class="text-slate-800 dark:text-slate-200 text-sm whitespace-pre-wrap"
                                x-html="formatMessage(msg.content)">
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400" x-text="msg.timestamp"></span>
                            <!-- Streaming Cursor -->
                            <span x-show="!msg.complete"
                                class="inline-block w-2 h-4 bg-slate-800 dark:bg-slate-200 ml-1 animate-pulse">
                            </span>
                        </div>
                    </div>
                </template>

                <!-- Error Message -->
                <template x-if="msg.role === 'error'">
                    <div class="flex items-start space-x-3">
                        <div class="bg-red-500 rounded-full p-2 flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-3 max-w-sm">
                            <p class="text-red-800 dark:text-red-200 text-sm" x-text="msg.content">
                            </p>
                            <span class="text-xs text-red-600 dark:text-red-400" x-text="msg.timestamp"></span>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <!-- Input Area -->
    <div class="border-t border-slate-200 dark:border-slate-600 p-4">
        <form @submit.prevent="sendMessage()" class="flex items-center space-x-3">
            <div class="flex-1 relative">
                <input x-model="userInput" type="text" placeholder="Ask me anything about your clinic..."
                    :disabled="isStreaming"
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-slate-800 dark:text-slate-200 placeholder-slate-500 dark:placeholder-slate-400 disabled:opacity-50 disabled:cursor-not-allowed">
            </div>
            <button type="submit" :disabled="isStreaming || !userInput.trim()"
                class="bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-full p-3 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-purple-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                <svg x-show="!isStreaming" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <svg x-show="isStreaming" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </form>

        <!-- Actions Footer -->
        <div class="flex items-center justify-between mt-3">
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-2">
                <button @click="sendQuickAction('Today\'s Stats')" :disabled="isStreaming"
                    class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full text-xs hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    📊 Today's Stats
                </button>
                <button @click="sendQuickAction('Schedule Overview')" :disabled="isStreaming"
                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    📅 Schedule Overview
                </button>
                <button @click="sendQuickAction('Revenue Report')" :disabled="isStreaming"
                    class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full text-xs hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    💰 Revenue Report
                </button>
            </div>

            <!-- Clear Chat Button -->
            <div>
                <button @click="clearChat()" :disabled="isStreaming || messages.length === 0"
                    class="inline-flex items-center px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-full text-xs hover:bg-slate-200 dark:hover:bg-slate-600 hover:text-slate-700 dark:hover:text-slate-300 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-eraser mr-1.5"></i>
                    Clear Chat
                </button>
            </div>
        </div>
    </div>
</div>