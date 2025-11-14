<?php

namespace App\Contracts;

interface FavoriteRepositoryContract
{
    /**
     * Get all favorite pokemons
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAll();

    /**
     * Add a pokemon to favorites with data
     *
     * @param array $pokemonData
     * @return mixed
     */
    public function addFavoriteWithData(array $pokemonData);

    /**
     * Remove a pokemon from favorites
     *
     * @param int $pokemonId
     * @return bool
     */
    public function removeFavorite(int $pokemonId);

    /**
     * Check if a pokemon is favorited
     *
     * @param int $pokemonId
     * @return bool
     */
    public function isFavorite(int $pokemonId): bool;

    /**
     * Filter favorites by abilities
     *
     * @param array $abilities
     * @return \Illuminate\Support\Collection
     */
    public function filterByAbilities(array $abilities);
}
