<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Field extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    // TAMBAHKAN INI SEBAGAI PENGGANTI #[Fillable]
    protected $fillable = [
        'name', 
        'description', 
        'address', 
        'latitude', 
        'longitude', 
        'facilities', 
        'min_dp_percent'
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'min_dp_percent' => 'decimal:2',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(FieldPrice::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}