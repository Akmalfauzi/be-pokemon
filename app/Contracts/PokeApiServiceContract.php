<?php

namespace App\Contracts;

use App\Http\Resources\PokemonResource;

interface PokeApiServiceContract
{
    /**
     * Get list of pokemons with pagination
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getPokemonList(int $page = 1, int $limit = 20): array;

    /**
     * Get pokemon detail by ID or name
     *
     * @param string|int $idOrName
     * @return PokemonResource|null
     */
    public function getPokemonDetail($idOrName): ?PokemonResource;

    /**
     * Search pokemons by name
     *
     * @param string $name
     * @return array
     */
    public function searchPokemonByName(string $name): array;

    /**
     * Clear cache
     *
     * @return void
     */
    public function clearCache(): void;
}
