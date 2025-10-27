<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GeminiKnowledgeService;

class AiChatModal extends Component
{
    public $messages = [];
    public $userMessage = '';
    public $isTyping = false;
    public $streamingMessage = '';
    public $isFirstMessage = true;

    protected GeminiKnowledgeService $geminiService;

    public function boot(GeminiKnowledgeService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function mount()
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => "Hello! I'm your AI assistant for Nice Smile Clinic. How can I help you today?",
            'timestamp' => now()->diffForHumans(),
        ];
    }

    public function sendMessage()
    {
        if (empty(trim($this->userMessage))) {
            return;
        }

        $this->messages[] = [
            'role' => 'user',
            'content' => $this->userMessage,
            'timestamp' => now()->diffForHumans(),
        ];

        $userMessage = $this->userMessage;
        $this->userMessage = '';
        $this->isTyping = true;
        $this->streamingMessage = '';

        $this->dispatch('start-ai-stream', [
            'message' => $userMessage,
            'isFirstMessage' => $this->isFirstMessage
        ]);
    }

    public function updateStreamingMessage($content)
    {
        $this->streamingMessage = $content;
    }

    public function completeStreaming($fullMessage)
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $fullMessage,
            'timestamp' => now()->diffForHumans(),
        ];

        $this->isTyping = false;
        $this->streamingMessage = '';
        $this->isFirstMessage = false;
    }

    public function handleError($error)
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Sorry, I encountered an error: ' . $error,
            'timestamp' => now()->diffForHumans(),
        ];

        $this->isTyping = false;
        $this->streamingMessage = '';
    }

    public function clearChat()
    {
        $this->messages = [[
            'role' => 'assistant',
            'content' => "Hello! I'm your AI assistant for Nice Smile Clinic. How can I help you today?",
            'timestamp' => now()->diffForHumans(),
        ]];
        $this->isFirstMessage = true;
        $this->streamingMessage = '';
        $this->isTyping = false;
    }

    public function sendQuickAction($action)
    {
        $messages = [
            'stats' => "Show me today's statistics",
            'schedule' => "Show me today's schedule overview",
            'revenue' => "Show me today's revenue report",
        ];

        $this->userMessage = $messages[$action] ?? '';
        $this->sendMessage();
    }

    public function render()
    {
        return view('livewire.ai-chat-modal');
    }
}