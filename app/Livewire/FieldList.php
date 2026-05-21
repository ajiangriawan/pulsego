<?php

namespace App\Livewire;

use App\Models\Field;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class FieldList extends Component
{
    use WithPagination;

    // Menyimpan state di URL agar ketika di-refresh atau tombol kembali di-klik, filter tidak hilang
    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'sort', history: true)]
    public string $sortBy = 'latest';

    #[Url(as: 'category', history: true)]
    public string $type = '';

    public int $perPage = 9;

    /**
     * Reset pagination otomatis saat filter berubah
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    /**
     * Mengatur filter tipe lapangan (Toggle Mode)
     */
    public function setType(string $type): void
    {
        $this->type = ($this->type === $type) ? '' : $type;
        $this->resetPage();
    }

    /**
     * Reset semua filter ke kondisi awal
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'sortBy', 'type']);
        $this->resetPage();
    }

    public function render()
    {
        // Membangun query utama dengan Eager Loading relasi
        $query = Field::query()
            ->with(['prices']) // Memuat data harga untuk kalkulasi card
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type, function ($q) {
                $q->where('type', $this->type);
            });

        // Logika pengurutan (Sorting)
        $query = match($this->sortBy) {
            'price_asc'  => $query->orderByRaw('(SELECT COALESCE(MIN(price), 0) FROM field_prices WHERE field_prices.field_id = fields.id) ASC'),
            'price_desc' => $query->orderByRaw('(SELECT COALESCE(MIN(price), 0) FROM field_prices WHERE field_prices.field_id = fields.id) DESC'),
            'name'       => $query->orderBy('name', 'asc'),
            default      => $query->latest(),
        };

        // Mengambil daftar tipe unik langsung dari database untuk komponen Filter Chips
        $types = Field::select('type')
            ->distinct()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->pluck('type');

        return view('livewire.field-list', [
            'fields' => $query->paginate($this->perPage),
            'types'  => $types,
        ])->layout('layouts.app'); // Memastikan terbungkus dengan layout utama PulseGo
    }
}