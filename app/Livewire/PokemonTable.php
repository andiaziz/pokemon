<?php

namespace App\Livewire;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Footer;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Header;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class PokemonTable extends PowerGridComponent
{
    public string $tableName = 'pokemon-table';
    
    // Default sort by weight descending
    public string $sortField = 'weight';
    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        return [
            (new Header())
                ->showSearchInput(),
            (new Footer())
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Pokemon::query()->with('abilities');
    }

    public function fields(): PowerGridFields
    {
        return (new PowerGridFields())
            ->add('id')
            ->add('name')
            ->add('base_experience')
            ->add('weight')
            ->add('image_path', function (Pokemon $pokemon) {
                $url = asset('storage/' . $pokemon->image_path);
                return '<div class="flex justify-center"><img src="' . $url . '" alt="' . e($pokemon->name) . '" class="w-20 h-20 object-contain rounded-lg bg-gray-50 p-1"></div>';
            })
            ->add('abilities_list', function (Pokemon $pokemon) {
                return $pokemon->abilities->pluck('name')->join(', ');
            });
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title('Image')
                ->field('image_path'),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Base Experience', 'base_experience')
                ->sortable(),

            Column::make('Weight', 'weight')
                ->sortable(),

            Column::make('Abilities', 'abilities_list'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('weight')
                ->dataSource([
                    ['id' => 'light', 'name' => 'Light (100-150)'],
                    ['id' => 'medium', 'name' => 'Medium (151-199)'],
                    ['id' => 'heavy', 'name' => 'Heavy (≥200)'],
                ])
                ->optionLabel('name')
                ->optionValue('id')
                ->builder(function ($builder, $value) {
                    if (empty($value)) {
                        return;
                    }
                    
                    match ($value) {
                        'light' => $builder->whereBetween('weight', [100, 150]),
                        'medium' => $builder->whereBetween('weight', [151, 199]),
                        'heavy' => $builder->where('weight', '>=', 200),
                        default => null,
                    };
                }),
        ];
    }
}
