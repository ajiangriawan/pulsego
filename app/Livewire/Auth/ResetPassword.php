<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

#[Layout('layouts.guest')]
class ResetPassword extends Component
{
    public $token;
    public $email;
    public $password;
    public $password_confirmation;

    // Menangkap token dan email dari URL saat halaman dimuat
    public function mount($token)
    {
        $this->token = $token;
        // Mengambil email dari query string (?email=...) jika ada
        $this->email = request()->query('email');
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function resetPassword()
    {
        $this->validate();

        // Memproses reset password menggunakan broker bawaan Laravel
        $status = Password::broker()->reset(
            [
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ],
            function ($user, $password) {
                // Logika saat password berhasil diubah
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Flash message agar bisa ditampilkan di halaman login (jika Anda punya alert di sana)
            session()->flash('status', __($status));
            
            // Redirect ke halaman login setelah sukses
            return redirect()->route('login'); 
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}