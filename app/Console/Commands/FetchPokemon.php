<?php

namespace App\Console\Commands;

use App\Models\Ability;
use App\Models\Pokemon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchPokemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pokemon:fetch {--start=1 : Start ID} {--end=400 : End ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Pokemon data from PokeAPI and store in database (ID 1-400, weight >= 100)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startId = (int) $this->option('start');
        $endId = (int) $this->option('end');

        $this->info("Fetching Pokemon from ID {$startId} to {$endId}...");
        $this->newLine();

        $bar = $this->output->createProgressBar($endId - $startId + 1);
        $bar->start();

        $savedCount = 0;
        $skippedCount = 0;

        for ($id = $startId; $id <= $endId; $id++) {
            try {
                $pokemon = $this->fetchPokemon($id);

                if ($pokemon) {
                    // Filter: Only save Pokemon with weight >= 100
                    if ($pokemon['weight'] >= 100) {
                        $this->savePokemon($pokemon);
                        $savedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error fetching Pokemon ID {$id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Completed!");
        $this->info("Saved: {$savedCount} Pokemon");
        $this->info("Skipped (weight < 100): {$skippedCount} Pokemon");

        return Command::SUCCESS;
    }

    /**
     * Fetch Pokemon data from PokeAPI
     */
    private function fetchPokemon(int $id): ?array
    {
        $response = Http::timeout(30)->get("https://pokeapi.co/api/v2/pokemon/{$id}");

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Save Pokemon to database
     */
    private function savePokemon(array $data): void
    {
        // Download and save image
        $imagePath = $this->downloadImage($data);

        // Create or update Pokemon
        $pokemon = Pokemon::updateOrCreate(
            ['name' => $data['name']],
            [
                'base_experience' => $data['base_experience'],
                'weight' => $data['weight'],
                'image_path' => $imagePath,
            ]
        );

        // Process abilities (only non-hidden)
        $abilityIds = [];
        foreach ($data['abilities'] as $abilityData) {
            // Only save abilities with is_hidden: false
            if ($abilityData['is_hidden'] === false) {
                $ability = Ability::firstOrCreate(
                    ['name' => $abilityData['ability']['name']]
                );
                $abilityIds[] = $ability->id;
            }
        }

        // Sync abilities to pivot table
        $pokemon->abilities()->sync($abilityIds);
    }

    /**
     * Download Pokemon image and save to local storage
     */
    private function downloadImage(array $data): ?string
    {
        // Get image URL from sprites (using official artwork or front_default)
        $imageUrl = $data['sprites']['other']['official-artwork']['front_default']
            ?? $data['sprites']['front_default']
            ?? null;

        if (!$imageUrl) {
            return null;
        }

        try {
            $response = Http::timeout(30)->get($imageUrl);

            if ($response->failed()) {
                return null;
            }

            // Create directory if not exists
            $directory = 'pokemon_images';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Get file extension from URL
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
            $filename = "{$data['name']}.{$extension}";
            $path = "{$directory}/{$filename}";

            // Save image to storage
            Storage::disk('public')->put($path, $response->body());

            // Return the relative path
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }
}
