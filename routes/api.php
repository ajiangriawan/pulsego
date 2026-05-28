<?php

use App\Http\Controllers\Api\HomeController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Rute Publik Lapangan & Home
Route::get('/home', [HomeController::class, 'getHomeData']);
Route::get('/fields', [HomeController::class, 'getAllFields']);
Route::get('/fields/{id}', [HomeController::class, 'getFieldDetail']);

// Rute Autentikasi Publik
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute ini TETAP di sini karena dipanggil dari React Native (HP)
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::get('/fields/{id}/times', [HomeController::class, 'getAvailableTimes']);

// Rute Privat (HANYA bisa diakses jika membawa Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'updatePassword']);

    Route::get('/history', [HomeController::class, 'getHistory']);

    // Rute pelunasan sisa DP
    Route::post('/history/{id}/pay-remaining', [HomeController::class, 'payRemaining']);

    Route::post('/checkout', [HomeController::class, 'checkout']);
    Route::post('/history/{id}/cancel', [App\Http\Controllers\Api\HomeController::class, 'cancelBooking']);
});
