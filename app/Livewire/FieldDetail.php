<?php

namespace App\Livewire;

use App\Models\Field;
use Livewire\Component;

class FieldDetail extends Component
{
    public $field;

    public function mount($id)
    {
        // Tarik data lapangan beserta harga dinamisnya
        $this->field = Field::with('prices')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.field-detail')->layout('layouts.app');
    }
}