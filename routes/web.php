<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Livewire\Home;
use App\Livewire\FieldDetail;
use App\Livewire\Checkout;
use App\Livewire\BookingHistory;
use App\Http\Controllers\MidtransController;
use App\Livewire\BookingHistoryPayment;
use App\Livewire\FieldList;

Route::get('/', Home::class)->name('home');


Route::get('/field/{id}', FieldDetail::class)->name('field.detail');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/booking/{booking_code}/receipt', function ($booking_code) {
    $booking = Booking::with(['user', 'field', 'items'])->where('booking_code', $booking_code)->firstOrFail();
    return view('receipt', compact('booking'));
})->name('booking.receipt');

// Route untuk halaman validasi (Tampil saat QR Code di-scan pakai HP)
Route::get('/validate/{booking_code}', function ($booking_code) {
    $booking = Booking::with(['user', 'field', 'items'])->where('booking_code', $booking_code)->firstOrFail();
    return view('validate', compact('booking'));
})->name('booking.validate');

Route::middleware('auth')->group(function () {
    Route::get('/checkout/{id}', Checkout::class)->name('checkout');
    Route::get('/checkout/{id}', Checkout::class)->name('checkout');
    Route::get('/history', BookingHistory::class)->name('booking.history');
    Route::get('/history-payment', BookingHistoryPayment::class)->name('booking.history.payment');
    Route::get('/field-list', FieldList::class)->name('field');
    
});


// Route untuk menerima notifikasi otomatis dari Midtrans
Route::post('/midtrans/callback', [MidtransController::class, 'callback'])->name('midtrans.callback');

require __DIR__ . '/auth.php';
