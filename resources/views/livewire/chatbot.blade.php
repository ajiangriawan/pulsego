<div class="fixed bottom-20 right-4 z-50 md:bottom-6 md:right-6">
    
    {{-- ===== TOMBOL MELAYANG (FAB) ===== --}}
    <button wire:click="toggleChat" class="w-12 h-12 sm:w-14 sm:h-14 rounded-full text-white shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 z-50 relative"
            style="background: linear-gradient(135deg, #3A9E3F, #1E6B2A);">
        @if($isOpen)
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        @else
            <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        @endif
    </button>

    {{-- ===== KOTAK POP-UP CHAT STYLE REAL-TIME ===== --}}
    <div x-data="{ open: @entangle('isOpen') }" 
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="absolute bottom-16 right-0 w-[calc(100vw-2rem)] sm:w-96 h-[450px] bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden max-w-md"
         style="display: none;">
        
        {{-- Header --}}
        <div class="p-4 text-white flex items-center gap-3 shrink-0 shadow-sm" style="background: linear-gradient(135deg, #1E6B2A, #3A9E3F);">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-lg shadow-inner">🤖</div>
            <div>
                <h4 class="font-extrabold text-xs sm:text-sm tracking-wide">PulseGo Assistant</h4>
                <p class="text-[9px] text-green-200 font-medium flex items-center gap-1">🟢 PulseGo AI • Online Aktif</p>
            </div>
        </div>

        {{-- Area Riwayat Pesan --}}
        <div id="chat-body" class="flex-1 p-4 overflow-y-auto bg-gray-50 space-y-3 scroll-smooth">
            @foreach($chatHistory as $chat)
                <div class="flex {{ $chat['role'] === 'user' ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $loop->index }}">
                    <div class="max-w-[80%] rounded-2xl px-3.5 py-2 text-xs shadow-xs tracking-wide leading-relaxed
                        {{ $chat['role'] === 'user' 
                            ? 'bg-emerald-600 text-white rounded-br-none' 
                            : 'bg-white text-gray-800 border border-gray-150 rounded-bl-none' }}">
                        {!! nl2br(e($chat['parts'][0]['text'])) !!}
                    </div>
                </div>
            @endforeach

            {{-- Efek Animasi Tiga Titik Mengetik yang Muncul Setelah User Kirim Pesan --}}
            @if($isAiTyping)
                <div class="flex justify-start animate-fade-in">
                    <div class="bg-white border border-gray-150 rounded-2xl rounded-bl-none px-4 py-2.5 text-xs shadow-xs text-gray-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Form Input Pesan --}}
        <form wire:submit.prevent="submitMessage" class="p-3 bg-white border-t border-gray-100 flex gap-2 items-center shrink-0">
            <input type="text" 
                   wire:model="userMessage" 
                   placeholder="Tulis pesan kamu di sini..." 
                   class="flex-1 border-gray-200 rounded-xl text-xs bg-gray-50 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3 transition-colors">
            <button type="submit" wire:loading.attr="disabled" wire:target="submitMessage"
                    class="p-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 active:scale-95 transition flex-shrink-0 disabled:opacity-50">
                🚀
            </button>
        </form>
    </div>
</div>

{{-- Skrip Event Asinkronous --}}
@script
<script>
    $wire.on('scroll-to-bottom', () => {
        setTimeout(() => {
            const chatBody = document.getElementById('chat-body');
            if(chatBody) chatBody.scrollTop = chatBody.scrollHeight;
        }, 30);
    });

    // Ambil lokasi GPS dari browser HP sebelum memanggil jawaban AI
    $wire.on('trigger-ai-process', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                // Set variabel komponen Livewire secara langsung
                $wire.lat = position.coords.latitude;
                $wire.lng = position.coords.longitude;
                $wire.call('fetchAiReply');
            }, (error) => {
                // Jika GPS dimatikan, jalankan tanpa koordinat
                $wire.call('fetchAiReply');
            });
        } else {
            $wire.call('fetchAiReply');
        }
    });
</script>
@endscript