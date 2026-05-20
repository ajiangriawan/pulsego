<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Informasi Lapangan</h2>
                    <div class="flex items-center gap-4">
                        @if($field->getFirstMediaUrl('gallery'))
                            <img src="{{ $field->getFirstMediaUrl('gallery') }}" class="w-24 h-24 object-cover rounded-lg">
                        @endif
                        <div>
                            <h3 class="text-lg font-bold">{{ $field->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $field->address }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Pilih Tanggal & Waktu</h2>
                    
                    <input type="date" wire:model.live="bookingDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 mb-2">
                    
                    @error('bookingDate') 
                        <span class="text-red-500 text-sm mb-4 block font-semibold">{{ $message }}</span> 
                    @enderror

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @forelse($this->availableTimes as $time)
                            <label class="cursor-pointer">
                                <input type="checkbox" wire:model.live="selectedTimes" value="{{ $time['value'] }}" class="peer hidden" @if($time['is_booked']) disabled @endif>
                                <div class="rounded-lg border-2 p-3 text-center transition-all 
                                    {{ $time['is_booked'] ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white border-gray-200 text-gray-700 hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700' }}">
                                    <div class="font-bold">{{ $time['start'] }} - {{ $time['end'] }}</div>
                                    <div class="text-xs mt-1">{{ $time['is_booked'] ? 'Telah Dipesan' : 'Rp ' . number_format($time['price'], 0, ',', '.') }}</div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full text-center text-gray-500 py-4">Tidak ada jadwal tersedia pada hari ini.</div>
                        @endforelse
                    </div>
                    @error('selectedTimes') <span class="text-red-500 text-sm mt-2 block font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Ringkasan Pembayaran</h2>

                    <div class="space-y-3 text-sm text-gray-600 mb-4">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="flex gap-2 mb-2">
                            <input type="text" wire:model="promoCode" placeholder="Kode Promo" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <button wire:click="applyPromo" class="bg-gray-800 text-white px-3 rounded-md hover:bg-gray-700 text-sm transition">Gunakan</button>
                        </div>
                        @if (session()->has('success_promo')) <p class="text-green-600 text-xs">{{ session('success_promo') }}</p> @endif
                        @if (session()->has('error_promo')) <p class="text-red-600 text-xs">{{ session('error_promo') }}</p> @endif

                        @if($discountAmount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span>PPN (11%)</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($ppnAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-3 text-lg font-bold text-gray-900">
                            <span>Total</span>
                            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mb-6 mt-4 pt-4 border-t border-gray-200">
                        <label class="block text-sm font-bold text-gray-900 mb-3">Pilih Opsi Pembayaran</label>
                        
                        <div class="flex flex-col gap-3">
                            <label class="cursor-pointer relative">
                                <input type="radio" wire:model.live="paymentType" value="full" class="peer hidden">
                                <div class="rounded-lg border-2 p-4 transition-all bg-white border-gray-200 hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-bold text-gray-900 text-base">Bayar Lunas (100%)</div>
                                            <div class="text-xs text-gray-500 mt-0.5">Selesaikan semua tagihan sekarang</div>
                                        </div>
                                        <div class="text-lg font-extrabold text-blue-700">
                                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute top-4 right-4 hidden peer-checked:block text-blue-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            </label>

                            <label class="cursor-pointer relative">
                                <input type="radio" wire:model.live="paymentType" value="dp" class="peer hidden">
                                <div class="rounded-lg border-2 p-4 transition-all bg-white border-gray-200 hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-bold text-gray-900 text-base">Bayar DP Saja (30%)</div>
                                            <div class="text-xs text-red-500 font-medium mt-0.5">Sisa Rp {{ number_format($grandTotal - $dpAmount, 0, ',', '.') }} dibayar di tempat</div>
                                        </div>
                                        <div class="text-lg font-extrabold text-blue-700">
                                            Rp {{ number_format($dpAmount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute top-4 right-4 hidden peer-checked:block text-blue-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            </label>
                        </div>
                        @error('paymentType') <span class="text-red-500 text-sm mt-2 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <button wire:click="processPayment" wire:loading.attr="disabled" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow transition flex justify-center items-center">
                        <span wire:loading.remove wire:target="processPayment">Lanjutkan Pembayaran</span>
                        <span wire:loading wire:target="processPayment">Mengarahkan ke Midtrans...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>