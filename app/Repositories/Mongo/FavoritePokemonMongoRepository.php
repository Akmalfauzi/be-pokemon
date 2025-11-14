<?php

namespace App\Repositories\Mongo;

use App\Contracts\FavoriteRepositoryContract;
use App\Models\Mongo\FavoritePokemonMongo;
use Illuminate\Support\Collection;

class FavoritePokemonMongoRepository implements FavoriteRepositoryContract
{
    /**
     * Get all favorite pokemons
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return FavoritePokemonMongo::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($favorite) {
                return $favorite->pokemon_data;
            });
    }

    /**
     * Add a pokemon to favorites with data
     *
     * @param array $pokemonData
     * @return FavoritePokemonMongo
     */
    public function addFavoriteWithData(array $pokemonData)
    {
        return FavoritePokemonMongo::updateOrCreate(
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
        return FavoritePokemonMongo::where('pokemon_id', $pokemonId)->delete() > 0;
    }

    /**
     * Check if a pokemon is favorited
     *
     * @param int $pokemonId
     * @return bool
     */
    public function isFavorite(int $pokemonId): bool
    {
        return FavoritePokemonMongo::where('pokemon_id', $pokemonId)->exists();
    }

    /**
     * Filter favorites by abilities
     *
     * @param array $abilities
     * @return Collection
     */
    public function filterByAbilities(array $abilities): Collection
    {
        $favorites = FavoritePokemonMongo::whereIn('pokemon_data.abilities', $abilities)
            ->get()
            ->map(function ($favorite) {
                return $favorite->pokemon_data;
            });

        return $favorites;
    }
}
