<?php

namespace App\Repositories\Eloquent;

use App\Contracts\FavoriteRepositoryContract;
use App\Models\FavoritePokemon;
use Illuminate\Support\Collection;

class FavoritePokemonRepository implements FavoriteRepositoryContract
{
    /**
     * Get all favorite pokemons
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return FavoritePokemon::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($favorite) {
                return $favorite->pokemon_data;
            });
    }

    /**
     * Add a pokemon to favorites with data
     *
     * @param array $pokemonData
     * @return FavoritePokemon
     */
    public function addFavoriteWithData(array $pokemonData)
    {
        return FavoritePokemon::updateOrCreate(
            ['pokemon_id' => $pokemonData['id']],
            [
                'pokemon_name' => $pokemonData['name'],
                'pokemon_data' => $pokemonData
            ]
        );
    }

    /**
     * Remove a pokemon from favorites
     *
     * @param int $pokemonId
     * @return bool
     */
    public function removeFavorite(int $pokemonId): bool
    {
        return FavoritePokemon::where('pokemon_id', $pokemonId)->delete() > 0;
    }

    /**
     * Check if a pokemon is favorited
     *
     * @param int $pokemonId
     * @return bool
     */
    public function isFavorite(int $pokemonId): bool
    {
        return FavoritePokemon::where('pokemon_id', $pokemonId)->exists();
    }

    /**
     * Filter favorites by abilities
     *
     * @param array $abilities
     * @return Collection
     */
    public function filterByAbilities(array $abilities): Collection
    {
        $favorites = FavoritePokemon::all();

        return $favorites->filter(function ($favorite) use ($abilities) {
            $pokemonAbilities = $favorite->pokemon_data['abilities'] ?? [];

            return !empty(array_intersect($abilities, $pokemonAbilities));
        })->map(function ($favorite) {
            return $favorite->pokemon_data;
        })->values();
    }
}
