<div x-data="aiChatModal()" class="fixed bottom-6 right-6 z-50">
    <!-- Floating AI Button -->
    <button @click="toggleModal()"
        class="group relative bg-gradient-to-r from-purple-600 via-blue-600 to-cyan-500 hover:from-purple-700 hover:via-blue-700 hover:to-cyan-600 text-white rounded-full p-4 shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-purple-500/50">

        <!-- Animated Background Glow -->
        <div
            class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-400 to-cyan-400 opacity-75 blur-md group-hover:opacity-100 transition-opacity duration-300 animate-pulse">
        </div>

        <!-- AI Icon with Animation -->
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
                <div class="h-96 flex flex-col">
                    <!-- Messages Area -->
                    <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                        <!-- Welcome Message (only if no messages) -->
                        <template x-if="messages.length === 0">
                            <div class="flex items-start space-x-3">
                                <div
                                    class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
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
                                        <div
                                            class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg p-3 max-w-sm">
                                            <p class="text-white text-sm" x-text="msg.content"></p>
                                            <span class="text-xs text-white/70" x-text="msg.timestamp"></span>
                                        </div>
                                        <div
                                            class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-full p-2 flex-shrink-0">
                                            <i class="fas fa-user w-4 h-4 text-white"></i>
                                        </div>
                                    </div>
                                </template>

                                <!-- AI Message -->
                                <template x-if="msg.role === 'assistant'">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                            </svg>
                                        </div>
                                        <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-3 max-w-sm">
                                            <div class="text-slate-800 dark:text-slate-200 text-sm whitespace-pre-wrap"
                                                x-html="formatMessage(msg.content)">
                                            </div>
                                            <span class="text-xs text-slate-500 dark:text-slate-400"
                                                x-text="msg.timestamp"></span>
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
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-3 max-w-sm">
                                            <p class="text-red-800 dark:text-red-200 text-sm" x-text="msg.content">
                                            </p>
                                            <span class="text-xs text-red-600 dark:text-red-400"
                                                x-text="msg.timestamp"></span>
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
                                <input x-model="userInput" type="text"
                                    placeholder="Ask me anything about your clinic..." :disabled="isStreaming"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-slate-800 dark:text-slate-200 placeholder-slate-500 dark:placeholder-slate-400 disabled:opacity-50 disabled:cursor-not-allowed">
                            </div>
                            <button type="submit" :disabled="isStreaming || !userInput.trim()"
                                class="bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-full p-3 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-purple-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                                <svg x-show="!isStreaming" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <svg x-show="isStreaming" class="w-5 h-5 animate-spin" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </form>

                        <!-- Actions Footer -->
                        <div class="flex items-center justify-between mt-3">
                            <!-- Quick Actions -->
                            <div class="flex flex-wrap gap-2">
                                <button @click="sendQuickAction('Today\'s Stats')"
                                    :disabled="isStreaming"
                                    class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full text-xs hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    📊 Today's Stats
                                </button>
                                <button @click="sendQuickAction('Schedule Overview')"
                                    :disabled="isStreaming"
                                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    📅 Schedule Overview
                                </button>
                                <button @click="sendQuickAction('Revenue Report')"
                                    :disabled="isStreaming"
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
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function aiChatModal() {
            return {
                isOpen: false,
                messages: [],
                userInput: '',
                isStreaming: false,
                eventSource: null,
                conversationHistory: [],
                reconnectAttempts: 0,
                maxReconnectAttempts: 3,

                toggleModal() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = 'auto';
                    }
                },

                sendMessage() {
                    if (!this.userInput.trim() || this.isStreaming) return;

                    const message = this.userInput.trim();
                    this.userInput = '';

                    // Add user message
                    this.messages.push({
                        role: 'user',
                        content: message,
                        timestamp: this.getTimestamp(),
                        complete: true
                    });

                    // Add to conversation history (only keep last 10 exchanges)
                    this.conversationHistory.push({
                        role: 'user',
                        content: message
                    });

                    // Trim history to prevent it from getting too large
                    if (this.conversationHistory.length > 20) {
                        this.conversationHistory = this.conversationHistory.slice(-20);
                    }

                    this.scrollToBottom();

                    // Start streaming response
                    this.streamResponse(message);
                },

                sendQuickAction(action) {
                    this.userInput = action;
                    this.sendMessage();
                },

                streamResponse(message) {
                    this.isStreaming = true;
                    this.reconnectAttempts = 0;

                    // Add empty assistant message
                    this.messages.push({
                        role: 'assistant',
                        content: '',
                        timestamp: this.getTimestamp(),
                        complete: false
                    });

                    const params = new URLSearchParams();
                    params.append('message', message);
                    
                    // Only send relevant history (exclude current message)
                    if (this.conversationHistory.length > 1) {
                        const historyToSend = this.conversationHistory.slice(0, -1);
                        params.append('history', JSON.stringify(historyToSend));
                    }

                    const url = `{{ route('chat.stream') }}?${params.toString()}`;
                    console.log('Starting stream:', url);

                    this.eventSource = new EventSource(url);
                    let receivedData = false;

                    // Listen for message events (default data)
                    this.eventSource.addEventListener('message', (event) => {
                        receivedData = true;
                        try {
                            const data = JSON.parse(event.data);
                            const lastMessage = this.messages[this.messages.length - 1];

                            if (lastMessage && lastMessage.role === 'assistant') {
                                lastMessage.content += data.content;
                                this.scrollToBottom();
                            }
                        } catch (error) {
                            console.error('Error parsing SSE message:', error, event.data);
                        }
                    });

                    // Listen for done event
                    this.eventSource.addEventListener('done', (event) => {
                        try {
                            const data = JSON.parse(event.data);
                            console.log('Stream complete:', data);

                            const lastMessage = this.messages[this.messages.length - 1];
                            if (lastMessage && lastMessage.role === 'assistant') {
                                lastMessage.complete = true;
                                lastMessage.timestamp = this.getTimestamp();

                                this.conversationHistory.push({
                                    role: 'assistant',
                                    content: lastMessage.content
                                });

                                if (this.conversationHistory.length > 20) {
                                    this.conversationHistory = this.conversationHistory.slice(-20);
                                }
                            }

                            this.cleanup();
                        } catch (error) {
                            console.error('Error handling done event:', error);
                            this.cleanup();
                        }
                    });

                    this.eventSource.addEventListener('error', (event) => {
                        console.error('SSE Connection Error:', event)
                        if (!receivedData) {
                            // Remove the empty assistant message
                            if (this.messages.length > 0 && 
                                this.messages[this.messages.length - 1].role === 'assistant' &&
                                this.messages[this.messages.length - 1].content === '') {
                                this.messages.pop();
                            }

                            // Remove the user message from history
                            if (this.conversationHistory.length > 0 &&
                                this.conversationHistory[this.conversationHistory.length - 1].role === 'user') {
                                this.conversationHistory.pop();
                            }

                            // Add error message
                            this.messages.push({
                                role: 'error',
                                content: 'Failed to connect. Please check your internet connection and try again.',
                                timestamp: this.getTimestamp(),
                                complete: true
                            });
                        } else {
                            // We received some data, mark last message as complete
                            const lastMessage = this.messages[this.messages.length - 1];
                            if (lastMessage && lastMessage.role === 'assistant' && !lastMessage.complete) {
                                lastMessage.complete = true;
                                lastMessage.timestamp = this.getTimestamp();

                                // Add to conversation history
                                this.conversationHistory.push({
                                    role: 'assistant',
                                    content: lastMessage.content
                                });
                            }
                        }

                        this.cleanup();
                        this.scrollToBottom();
                    });

                    // Set a timeout to check if stream is stuck
                    setTimeout(() => {
                        if (this.isStreaming && !receivedData) {
                            console.warn('No data received after 10 seconds, closing connection');
                            this.cleanup();
                            
                            // Remove empty assistant message
                            if (this.messages.length > 0 && 
                                this.messages[this.messages.length - 1].role === 'assistant' &&
                                this.messages[this.messages.length - 1].content === '') {
                                this.messages.pop();
                            }

                            this.messages.push({
                                role: 'error',
                                content: 'Request timeout. Please try again.',
                                timestamp: this.getTimestamp(),
                                complete: true
                            });
                            
                            this.scrollToBottom();
                        }
                    }, 10000); // 10 second timeout
                },

                cleanup() {
                    if (this.eventSource) {
                        this.eventSource.close();
                        this.eventSource = null;
                    }
                    this.isStreaming = false;
                },

                clearChat() {
                    if (confirm('Are you sure you want to clear the chat history?')) {
                        this.messages = [];
                        this.conversationHistory = [];
                    }
                },

                getTimestamp() {
                    const now = new Date();
                    const hours = now.getHours();
                    const minutes = now.getMinutes();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    const displayHours = hours % 12 || 12;
                    const displayMinutes = minutes < 10 ? '0' + minutes : minutes;
                    
                    return `${displayHours}:${displayMinutes} ${ampm}`;
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                formatMessage(content) {
                    return content
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                },

                destroy() {
                    this.cleanup();
                }
            }
        }
    </script>
@endpush