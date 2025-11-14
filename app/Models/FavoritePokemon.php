<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FavoritePokemon extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'favorite_pokemons';

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
        'pokemon_data' => 'array',
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
