<div class="min-h-screen bg-gray-100">

    {{-- ===== TOP HEADER BAR ===== --}}
    <div class="sticky top-0 z-30" style="background: linear-gradient(135deg, #1E6B2A, #3A9E3F);">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('field.detail', $field->id) }}" wire:navigate
                   class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-white font-extrabold text-lg leading-tight">Booking Lapangan</h1>
                    <p class="text-green-200 text-xs">{{ $field->name }}</p>
                </div>
            </div>

            {{-- Stepper --}}
            <div class="flex items-center">
                @php
                    $steps = ['Pilih Waktu', 'Detail', 'Bayar'];
                    $currentStep = 1; // bisa disesuaikan dengan state Livewire
                @endphp
                @foreach($steps as $index => $step)
                    <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold
                                {{ $index + 1 <= $currentStep ? 'bg-white text-green-700' : 'bg-white/20 text-white' }}">
                                @if($index + 1 < $currentStep)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>
                            <span class="text-[9px] font-semibold mt-1 {{ $index + 1 <= $currentStep ? 'text-white' : 'text-green-300' }}">
                                {{ $step }}
                            </span>
                        </div>
                        @if($index < count($steps) - 1)
                            <div class="flex-1 mx-2 mb-4">
                                <div class="h-0.5 {{ $index + 1 < $currentStep ? 'bg-white' : 'bg-white/30' }} rounded-full"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ===== LEFT: MAIN FORM ===== --}}
            <div class="lg:col-span-3 space-y-5">

                {{-- Field Info Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center gap-4">
                        @if($field->getFirstMediaUrl('gallery'))
                            <img src="{{ $field->getFirstMediaUrl('gallery') }}"
                                 class="w-20 h-20 object-cover rounded-xl flex-shrink-0"
                                 alt="{{ $field->name }}">
                        @else
                            <div class="w-20 h-20 rounded-xl flex items-center justify-center text-3xl flex-shrink-0" style="background: linear-gradient(135deg, #DCFCE7, #BBF7D0);">⚽</div>
                        @endif
                        <div class="flex-1">
                            <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Futsal Indoor</span>
                            <h3 class="font-extrabold text-gray-900 mt-1 text-base">{{ $field->name }}</h3>
                            <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $field->address }}
                            </p>
                            <p class="font-extrabold text-sm mt-1" style="color: #3A9E3F;">
                                Rp {{ number_format($field->prices->first()->price ?? 0, 0, ',', '.') }}/jam
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Date Picker --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-extrabold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #DCFCE7, #BBF7D0);">
                            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        Pilih Tanggal
                    </h2>

                    <input type="date"
                           wire:model.live="bookingDate"
                           min="{{ date('Y-m-d') }}"
                           class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 focus:border-green-500 focus:ring-0 transition-colors">

                    @error('bookingDate')
                        <p class="text-red-500 text-xs mt-2 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Time Slots --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-extrabold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #FEF3C7, #FDE68A);">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Pilih Jam
                    </h2>
                    <p class="text-xs text-gray-400 mb-4 ml-9">Pilih satu atau beberapa slot waktu yang tersedia</p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @forelse($this->availableTimes as $time)
                            <label class="cursor-pointer {{ $time['is_booked'] ? 'cursor-not-allowed' : '' }}">
                                <input type="checkbox"
                                       wire:model.live="selectedTimes"
                                       value="{{ $time['value'] }}"
                                       class="peer hidden"
                                       @if($time['is_booked']) disabled @endif>
                                <div class="rounded-xl border-2 p-3 text-center transition-all duration-200
                                    @if($time['is_booked'])
                                        bg-gray-50 border-gray-100 opacity-60
                                    @else
                                        bg-white border-gray-200 hover:border-green-300 hover:bg-green-50
                                        peer-checked:border-green-600 peer-checked:bg-green-50
                                    @endif">
                                    <div class="font-extrabold text-sm
                                        @if($time['is_booked']) text-gray-400
                                        @else text-gray-800 peer-checked:text-green-700 @endif">
                                        {{ $time['start'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mb-1">– {{ $time['end'] }}</div>
                                    @if($time['is_booked'])
                                        <div class="text-[10px] font-bold text-gray-400 bg-gray-100 rounded-md px-1.5 py-0.5">Terisi</div>
                                    @else
                                        <div class="text-[10px] font-bold text-green-600">
                                            Rp {{ number_format($time['price'], 0, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full text-center py-8 text-gray-400">
                                <div class="text-3xl mb-2">📅</div>
                                <p class="text-sm font-medium">Pilih tanggal terlebih dahulu</p>
                            </div>
                        @endforelse
                    </div>

                    @error('selectedTimes')
                        <p class="text-red-500 text-xs mt-3 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            {{-- ===== RIGHT: SUMMARY ===== --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-36">

                    {{-- Header --}}
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-extrabold text-gray-900">Ringkasan Pembayaran</h2>
                    </div>

                    <div class="p-5 space-y-4">

                        {{-- Price Breakdown --}}
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            {{-- Promo --}}
                            <div class="flex gap-2">
                                <input type="text"
                                       wire:model="promoCode"
                                       placeholder="Kode promo"
                                       class="flex-1 text-sm rounded-xl border-2 border-gray-200 px-3 py-2 font-medium focus:border-green-500 focus:ring-0 transition-colors">
                                <button wire:click="applyPromo"
                                        class="text-white text-xs font-bold px-3 py-2 rounded-xl transition"
                                        style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E);">
                                    Pakai
                                </button>
                            </div>

                            @if(session()->has('success_promo'))
                                <p class="text-xs font-semibold text-green-600 flex items-center gap-1">✓ {{ session('success_promo') }}</p>
                            @endif
                            @if(session()->has('error_promo'))
                                <p class="text-xs font-semibold text-red-500 flex items-center gap-1">✗ {{ session('error_promo') }}</p>
                            @endif

                            @if($discountAmount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-green-600 font-medium">Diskon Promo</span>
                                    <span class="font-bold text-green-600">– Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">PPN (11%)</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($ppnAmount, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center pt-3 border-t-2 border-gray-100">
                                <span class="font-extrabold text-gray-900">Total</span>
                                <span class="font-extrabold text-xl" style="color: #3A9E3F;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Payment Options --}}
                        <div class="pt-2">
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Opsi Pembayaran</p>

                            <div class="space-y-3">
                                {{-- Full Payment --}}
                                <label class="cursor-pointer block">
                                    <input type="radio" wire:model.live="paymentType" value="full" class="peer hidden">
                                    <div class="rounded-xl border-2 p-3.5 transition-all bg-white border-gray-200 hover:border-green-300 peer-checked:border-green-600 peer-checked:bg-green-50">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-3">
                                                <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-green-600 flex items-center justify-center flex-shrink-0
                                                    [.peer:checked~div>&]:border-green-600">
                                                    <div class="w-2 h-2 rounded-full bg-green-600 hidden peer-checked:block"></div>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 text-sm">Bayar Lunas</p>
                                                    <p class="text-[10px] text-gray-400">Bayar 100% sekarang</p>
                                                </div>
                                            </div>
                                            <span class="font-extrabold text-sm" style="color: #3A9E3F;">
                                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </label>

                                {{-- DP Payment --}}
                                <label class="cursor-pointer block">
                                    <input type="radio" wire:model.live="paymentType" value="dp" class="peer hidden">
                                    <div class="rounded-xl border-2 p-3.5 transition-all bg-white border-gray-200 hover:border-green-300 peer-checked:border-green-600 peer-checked:bg-green-50">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-bold text-gray-900 text-sm">Bayar DP</p>
                                                <p class="text-[10px] text-red-500 font-semibold">
                                                    Sisa Rp {{ number_format($grandTotal - $dpAmount, 0, ',', '.') }} di tempat
                                                </p>
                                            </div>
                                            <span class="font-extrabold text-sm" style="color: #3A9E3F;">
                                                Rp {{ number_format($dpAmount, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            @error('paymentType')
                                <p class="text-red-500 text-xs mt-2 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Payment Methods --}}
                        <div class="pt-2">
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Metode Pembayaran</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach([
                                    ['label' => 'QRIS', 'icon' => '🔲', 'color' => '#E63329'],
                                    ['label' => 'OVO', 'icon' => '💜', 'color' => '#4C3494'],
                                    ['label' => 'GoPay', 'icon' => '💚', 'color' => '#00AED6'],
                                    ['label' => 'DANA', 'icon' => '💙', 'color' => '#118EEA'],
                                ] as $method)
                                <div class="flex items-center gap-2 p-2.5 rounded-xl border border-gray-100 bg-gray-50 text-xs font-semibold text-gray-600">
                                    <span>{{ $method['icon'] }}</span>
                                    {{ $method['label'] }}
                                </div>
                                @endforeach
                            </div>
                            <p class="text-[10px] text-gray-400 mt-2 text-center">Pilih metode saat proses pembayaran</p>
                        </div>

                        {{-- CTA --}}
                        <button wire:click="processPayment"
                                wire:loading.attr="disabled"
                                class="w-full py-4 rounded-2xl text-white font-extrabold text-sm transition-all flex justify-center items-center gap-2"
                                style="background: linear-gradient(135deg, #3A9E3F, #6DBE4E); box-shadow: 0 6px 20px rgba(58,158,63,0.4);">
                            <span wire:loading.remove wire:target="processPayment" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Lanjutkan Pembayaran
                            </span>
                            <span wire:loading wire:target="processPayment" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Mengarahkan ke Midtrans...
                            </span>
                        </button>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>