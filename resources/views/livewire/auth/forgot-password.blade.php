<div>
    {{-- Header --}}
    <div class="px-8 pt-8 pb-6" style="background: linear-gradient(135deg, #1E6B2A, #3A9E3F);">
        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white mb-1">Lupa Password? 🔑</h1>
        <p class="text-green-200 text-sm font-medium">Kami akan kirimkan link reset ke email kamu</p>
    </div>

    <div class="px-8 py-7">

        {{-- Info Text --}}
        <div class="bg-green-50 border border-green-100 rounded-2xl p-4 mb-6 flex gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700 font-medium leading-relaxed">
                Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk membuat password baru.
            </p>
        </div>

        {{-- Session Status --}}
        @if(session('status'))
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-bold text-green-700">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Gunakan wire:submit saja untuk Livewire v3 (tidak perlu .prevent) --}}
        <form wire:submit="sendResetLink" class="space-y-5">

            {{-- Email --}}
            <div>
                <label for="email" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Alamat Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input wire:model="email"
                           id="email" type="email" name="email"
                           required autofocus
                           placeholder="nama@email.com"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-800 placeholder-gray-400 focus:border-green-500 focus:ring-0 transition-colors outline-none">
                </div>
                
                {{-- Diubah menjadi directive @error agar lebih aman dan tidak error --}}
                @error('email')
                    <span class="text-red-500 text-xs font-semibold mt-1.5 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 rounded-2xl text-white font-extrabold text-sm transition-all flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 6px 20px rgba(58,158,63,0.4);">
                
                {{-- wire:target disesuaikan dengan nama fungsi di PHP --}}
                <span wire:loading.remove wire:target="sendResetLink" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Link Reset Password
                </span>
                
                {{-- wire:target disesuaikan dengan nama fungsi di PHP --}}
                <span wire:loading wire:target="sendResetLink" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Mengirim Email...
                </span>
            </button>

        </form>

        {{-- Back to login --}}
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" wire:navigate
               class="inline-flex items-center gap-1.5 text-sm font-semibold hover:underline transition"
               style="color: #3A9E3F;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke halaman masuk
            </a>
        </div>

    </div>
</div>