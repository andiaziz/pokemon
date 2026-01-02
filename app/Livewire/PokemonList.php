<?php

namespace App\Livewire;

use App\Models\Pokemon;
use Livewire\Component;
use Livewire\WithPagination;

class PokemonList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $weightFilter = '';
    public string $sortField = 'weight';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'weightFilter' => ['except' => ''],
        'sortField' => ['except' => 'weight'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingWeightFilter()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Pokemon::query()->with('abilities');

        // Search filter
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Weight category filter
        if ($this->weightFilter) {
            match ($this->weightFilter) {
                'light' => $query->whereBetween('weight', [100, 150]),
                'medium' => $query->whereBetween('weight', [151, 199]),
                'heavy' => $query->where('weight', '>=', 200),
                default => null,
            };
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $pokemons = $query->paginate($this->perPage);

        return view('livewire.pokemon-list', [
            'pokemons' => $pokemons,
        ]);
    }
}
