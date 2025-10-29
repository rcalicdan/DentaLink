@push('scripts')
    <script>
        function aiChatModal() {
            return {
                isOpen: false,
                isStreaming: false,
                userInput: '',
                messages: [],
                conversationHistory: [],
                eventSource: null,
                streamCompleted: false,

                init() {},

                destroy() {
                    this.cleanup();
                },

                toggleModal() {
                    this.isOpen = !this.isOpen;
                    document.body.style.overflow = this.isOpen ? 'hidden' : 'auto';
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                addMessage(role, content, isComplete = true) {
                    this.messages.push({
                        role: role,
                        content: content,
                        timestamp: this.getTimestamp(),
                        complete: isComplete,
                    });
                    this.scrollToBottom();
                },

                updateLastAssistantMessage(chunk) {
                    const lastMessage = this.messages[this.messages.length - 1];
                    if (lastMessage && lastMessage.role === 'assistant') {
                        const container = this.$refs.messagesContainer;
                        if (!container) return;

                        const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 10;

                        lastMessage.content += chunk;

                        if (isScrolledToBottom) {
                            this.scrollToBottom();
                        }
                    }
                },

                finalizeLastAssistantMessage() {
                    const lastMessage = this.messages[this.messages.length - 1];
                    if (lastMessage && lastMessage.role === 'assistant' && !lastMessage.complete) {
                        lastMessage.complete = true;
                        lastMessage.timestamp = this.getTimestamp();
                        this.updateConversationHistory('assistant', lastMessage.content);
                    }
                },

                updateConversationHistory(role, content) {
                    this.conversationHistory.push({
                        role,
                        content
                    });
                    if (this.conversationHistory.length > 20) {
                        this.conversationHistory = this.conversationHistory.slice(-20);
                    }
                },

                clearChat() {
                    if (confirm('Are you sure you want to clear the chat history?')) {
                        this.messages = [];
                        this.conversationHistory = [];
                        this.cleanup();
                    }
                },

                sendMessage() {
                    const message = this.userInput.trim();
                    if (!message || this.isStreaming) return;

                    this.addMessage('user', message);
                    this.updateConversationHistory('user', message);
                    this.userInput = '';

                    this.streamResponse(message);
                },

                sendQuickAction(action) {
                    this.userInput = action;
                    this.sendMessage();
                },

                streamResponse(message) {
                    this.isStreaming = true;
                    this.streamCompleted = false;
                    this.addMessage('assistant', '', false);

                    const url = this.buildStreamURL(message);
                    this.eventSource = new EventSource(url);

                    this.setupEventListeners();
                },

                buildStreamURL(message) {
                    const params = new URLSearchParams({
                        message: message,
                        enhanced: 'false',
                    });

                    if (this.conversationHistory.length > 1) {
                        const history = this.conversationHistory.slice(0, -1);
                        params.append('history', JSON.stringify(history));
                    }
                    return `{{ route('chat.stream') }}?${params.toString()}`;
                },

                setupEventListeners() {
                    this.eventSource.addEventListener('connected', this.handleConnectedEvent.bind(this));
                    this.eventSource.addEventListener('message', this.handleMessageEvent.bind(this));
                    this.eventSource.addEventListener('done', this.handleDoneEvent.bind(this));
                    this.eventSource.addEventListener('error', this.handleErrorEvent.bind(this));
                    this.eventSource.onerror = this.handleConnectionError.bind(this);
                },

                handleConnectedEvent(event) {
                    console.log('Stream connected successfully');
                },

                handleMessageEvent(event) {
                    try {
                        const data = JSON.parse(event.data);
                        if (data.content) {
                            this.updateLastAssistantMessage(data.content);
                        }
                    } catch (error) {
                        console.error('Error parsing message:', error);
                    }
                },

                handleDoneEvent(event) {
                    this.streamCompleted = true;
                    this.finalizeLastAssistantMessage();
                    this.cleanup();
                },

                handleErrorEvent(event) {
                    try {
                        const errorData = JSON.parse(event.data);
                        this.addMessage('error', errorData.error || 'An unexpected error occurred.');
                    } catch (e) {
                        this.addMessage('error', 'An unexpected error occurred.');
                    }
                    this.streamCompleted = true;
                    this.cleanup();
                },

                handleConnectionError(event) {
                    if (this.streamCompleted) {
                        return;
                    }

                    const lastMessage = this.messages[this.messages.length - 1];
                    const wasInProgress = lastMessage && lastMessage.role === 'assistant' && !lastMessage.complete;

                    if (wasInProgress && lastMessage.content.trim() !== '') {
                        this.finalizeLastAssistantMessage();
                        console.log('Stream completed with content');
                    } else if (wasInProgress && lastMessage.content === '') {
                        this.messages.pop();
                        this.addMessage('error', 'Failed to connect to the AI assistant. Please check your connection.');
                    } else if (!wasInProgress) {
                        if (lastMessage && lastMessage.role === 'assistant' && lastMessage.content === '') {
                            this.messages.pop();
                        }
                        this.addMessage('error', 'Failed to connect to the AI assistant. Please check your connection.');
                    }
                    
                    this.cleanup();
                },

                handleStreamTimeout() {
                    const lastMessage = this.messages[this.messages.length - 1];
                    if (lastMessage && lastMessage.role === 'assistant' && lastMessage.content === '') {
                        this.messages.pop();
                    }
                    this.addMessage('error', 'Request timeout. The server took too long to respond.');
                    this.cleanup();
                },

                cleanup() {
                    if (this.eventSource) {
                        this.eventSource.close();
                        this.eventSource = null;
                    }
                    this.isStreaming = false;
                },

                getTimestamp() {
                    const now = new Date();
                    return now.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                formatMessage(content) {
                    if (typeof content !== 'string') return '';
                    return content
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                }
            }
        }
    </script>
@endpush