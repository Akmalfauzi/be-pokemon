<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class FavoritePokemonMongo extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'favorite_pokemons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pokemon_id',
        'pokemon_name',
        'pokemon_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pokemon_id' => 'integer',
    ];

    /**
     * Check if pokemon is favorited
     *
     * @param int $pokemonId
     * @return bool
     */
    public static function isFavorited(int $pokemonId): bool
    {
        return static::where('pokemon_id', $pokemonId)->exists();
    }
}
