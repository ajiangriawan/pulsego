<div>
    {{-- Header --}}
    <div class="px-8 pt-8 pb-6" style="background: linear-gradient(135deg, #1E6B2A, #3A9E3F);">
        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white mb-1">Atur Ulang Password 🔒</h1>
        <p class="text-green-200 text-sm font-medium">Silakan buat password baru untuk akun kamu</p>
    </div>

    <div class="px-8 py-7">

        {{-- Error Global (Biasanya untuk Token/Email Invalid) --}}
        @error('email')
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-5 flex gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700 font-medium leading-relaxed">{{ $message }}</p>
            </div>
        @enderror

        <form wire:submit="resetPassword" class="space-y-5">

            {{-- Email (Readonly) --}}
            <div>
                <label for="email" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Alamat Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    <input wire:model="email"
                           id="email" type="email" readonly required
                           class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-500 bg-gray-50 cursor-not-allowed focus:outline-none">
                </div>
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Password Baru
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input wire:model="password"
                           id="password" type="password" required autofocus
                           placeholder="Minimal 8 karakter"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-800 placeholder-gray-400 focus:border-green-500 focus:ring-0 transition-colors outline-none">
                </div>
                @error('password')
                    <span class="text-red-500 text-xs font-semibold mt-1.5 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Konfirmasi Password Baru
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <input wire:model="password_confirmation"
                           id="password_confirmation" type="password" required
                           placeholder="Ketik ulang password baru"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-800 placeholder-gray-400 focus:border-green-500 focus:ring-0 transition-colors outline-none">
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 mt-2 rounded-2xl text-white font-extrabold text-sm transition-all flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 6px 20px rgba(58,158,63,0.4);">
                
                <span wire:loading.remove wire:target="resetPassword" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Password Baru
                </span>
                
                <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>

        </form>
    </div>
</div>