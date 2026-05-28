<?php

namespace App\Livewire;

use App\Services\GeminiService;
use Livewire\Component;

class Chatbot extends Component
{
    public bool $isOpen = false;
    public string $userMessage = '';
    public array $chatHistory = [];
    public bool $isAiTyping = false;

    public $lat = null;
    public $lng = null;

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen && empty($this->chatHistory)) {
            $this->chatHistory[] = [
                'role' => 'model',
                'parts' => [['text' => "Halo! 👋 Aku Asisten AI PulseGo. Ada yang bisa aku bantu seputar sewa lapangan terdekat atau cek jadwal kosong hari ini?"]]
            ];
        }
    }

    public function submitMessage(): void
    {
        $this->validate(['userMessage' => 'required|string|max:1000']);

        $messageText = $this->userMessage;

        // Amankan langsung ke state history agar dikirim utuh ke fetchAiReply
        $this->chatHistory[] = [
            'role' => 'user',
            'parts' => [['text' => $messageText]]
        ];

        $this->isAiTyping = true;
        $this->userMessage = '';

        $this->dispatch('scroll-to-bottom');
        
        // Panggil proses AI di background
        $this->dispatch('trigger-ai-process');
    }

    public function fetchAiReply(GeminiService $gemini): void
    {
        $this->isAiTyping = true;

        $botReply = $gemini->getChatReply($this->chatHistory, $this->lat, $this->lng);

        $this->chatHistory[] = [
            'role' => 'model',
            'parts' => [['text' => $botReply]]
        ];

        $this->isAiTyping = false;
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.chatbot');
    }
}