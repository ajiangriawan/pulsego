<?php

namespace App\Livewire;

use App\Models\Field;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        // Ambil semua lapangan
        $fields = Field::latest()->get();
        
        // Gunakan layout bawaan Breeze/Laravel
        return view('livewire.home', compact('fields'))
            ->layout('layouts.app'); 
    }
}