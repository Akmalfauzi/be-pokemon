<?php

namespace App\Services;

use App\Contracts\PokeApiServiceContract;
use App\Http\Resources\PokemonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PokeApiService implements PokeApiServiceContract
{
    private const POKEAPI_BASE_URL = 'https://pokeapi.co/api/v2';
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'pokeapi_';

    /**
     * Get list of pokemons with pagination
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getPokemonList(int $page = 1, int $limit = 20): array
    {
        $cacheKey = self::CACHE_PREFIX . "list_page_{$page}_limit_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($page, $limit) {
            try {
                $offset = ($page - 1) * $limit;
                $response = Http::get(self::POKEAPI_BASE_URL . '/pokemon', [
                    'limit' => $limit,
                    'offset' => $offset
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    $pokemons = collect($data['results'])->map(function ($pokemon) {
                        return $this->getPokemonDetail($pokemon['name']);
                    })->filter()->values()->all();

                    return [
                        'pokemons' => $pokemons,
                        'count' => $data['count'],
                        'next' => $data['next'],
                        'previous' => $data['previous']
                    ];
                }

                return ['pokemons' => [], 'count' => 0, 'next' => null, 'previous' => null];
            } catch (\Exception $e) {
                Log::error('PokeAPI Error: ' . $e->getMessage());
                return ['pokemons' => [], 'count' => 0, 'next' => null, 'previous' => null];
            }
        });
    }

    /**
     * Get pokemon detail by ID or name
     *
     * @param string|int $idOrName
     * @return PokemonResource|null
     */
    public function getPokemonDetail($idOrName): ?PokemonResource
    {
        $cacheKey = self::CACHE_PREFIX . "detail_{$idOrName}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($idOrName) {
            try {
                $response = Http::get(self::POKEAPI_BASE_URL . "/pokemon/{$idOrName}");

                if ($response->successful()) {
                    $data = $response->json();

                    $pokemonData = [
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'pokedex_number' => $data['id'],
                        'image_url' => $data['sprites']['other']['official-artwork']['front_default']
                            ?? $data['sprites']['front_default'],
                        'types' => collect($data['types'])->pluck('type.name')->all(),
                        'abilities' => collect($data['abilities'])->pluck('ability.name')->all(),
                        'height' => $data['height'],
                        'weight' => $data['weight'],
                        'base_experience' => $data['base_experience'] ?? 0,
                        'hp' => $data['stats'][0]['base_stat'] ?? 0,
                        'attack' => $data['stats'][1]['base_stat'] ?? 0,
                        'defense' => $data['stats'][2]['base_stat'] ?? 0,
                        'special_attack' => $data['stats'][3]['base_stat'] ?? 0,
                        'special_defense' => $data['stats'][4]['base_stat'] ?? 0,
                        'speed' => $data['stats'][5]['base_stat'] ?? 0,
                    ];

                    return new PokemonResource($pokemonData);
                }

                return null;
            } catch (\Exception $e) {
                Log::error("PokeAPI Error fetching {$idOrName}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get all pokemon names from PokeAPI
     *
     * @return array
     */
    private function getAllPokemonNames(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'all_pokemon_names';

        return Cache::remember($cacheKey, 86400, function () {
            try {
                $response = Http::get(self::POKEAPI_BASE_URL . '/pokemon', [
                    'limit' => 2000,
                    'offset' => 0
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return collect($data['results'])->pluck('name')->all();
                }

                return [];
            } catch (\Exception $e) {
                Log::error('PokeAPI Error fetching all pokemon names: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Search pokemons by name with partial match
     * Returns limited number of results
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchPokemonByName(string $query, int $limit = 20): array
    {
        try {
            $query = strtolower(trim($query));

            if (empty($query)) {
                return [];
            }

            $allNames = $this->getAllPokemonNames();

            $matchingNames = collect($allNames)
                ->filter(function ($name) use ($query) {
                    return str_contains($name, $query);
                })
                ->take($limit)
                ->values()
                ->all();

            $pokemons = collect($matchingNames)
                ->map(function ($name) {
                    return $this->getPokemonDetail($name);
                })
                ->filter()
                ->values()
                ->all();

            return $pokemons;
        } catch (\Exception $e) {
            Log::error('PokeAPI Search Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
