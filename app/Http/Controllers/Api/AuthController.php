<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    // === 1. FUNGSI REGISTER (DAFTAR) ===
    public function register(Request $request)
    {
        // 1. Tambahkan validasi untuk phone
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20', // <-- Tambahan validasi HP
        ]);

        // 2. Tambahkan phone ke dalam proses create
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone, // <-- Simpan ke database
        ]);

        $token = $user->createToken('pulsego_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil',
            'data' => $user,
            'token' => $token
        ]);
    }

    // === 2. FUNGSI LOGIN ===
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada dan passwordnya cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401); // 401 = Unauthorized
        }

        // Jika cocok, terbitkan token baru
        $token = $user->createToken('pulsego_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => $user,
            'token' => $token
        ]);
    }

    // === 3. FUNGSI LOGOUT ===
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout'
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Menggunakan sistem bawaan Laravel untuk memproses pengiriman email
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Link reset password berhasil dikirim'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email tidak ditemukan di sistem kami'
        ], 400);
    }

    public function showResetForm($token, Request $request)
    {
        // Mengarahkan ke file view: resources/views/auth/reset-password.blade.php
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed', // Harus ada input password_confirmation
        ]);

        // Jalankan perintah reset password bawaan Laravel
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Pemicu event password telah direset
                event(new PasswordReset($user));
            }
        );

        // Jika berhasil, tampilkan halaman sukses
        if ($status === Password::PASSWORD_RESET) {
            return view('auth.reset-success');
        }

        // Jika gagal (misal token kadaluarsa), kembali ke form dengan pesan error
        return back()->withErrors(['email' => [__($status)]]);
    }

    // === 4. FUNGSI UPDATE PROFIL ===
    public function updateProfile(Request $request)
    {
        $user = $request->user(); // Ambil user yang sedang login dari token

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // Update data
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }

    // === 5. FUNGSI GANTI PASSWORD ===
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed', // Pastikan ada new_password_confirmation
        ]);

        $user = $request->user();

        // Cek apakah password lama yang dimasukkan benar
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah.'
            ], 400);
        }

        // Simpan password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.'
        ]);
    }
}
