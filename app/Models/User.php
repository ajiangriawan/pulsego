<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 1. TAMBAHKAN IMPORT SANCTUM DI SINI
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    // 2. TAMBAHKAN HasApiTokens DI DALAM KURUNG KURAWAL INI
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Menentukan apakah user bisa mengakses Panel Admin Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya user dengan role 'admin' yang bisa masuk ke Filament
        return $this->role === 'admin';
    }
}