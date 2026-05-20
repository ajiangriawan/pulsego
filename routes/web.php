<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

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

require __DIR__ . '/auth.php';
