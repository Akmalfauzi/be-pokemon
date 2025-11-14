<?php

use App\Http\Controllers\API\PokemonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Pokemon API Routes
Route::prefix('pokemons')->group(function () {
    // Get pokemons from PokeAPI (with pagination, search, filter)
    Route::get('/', [PokemonController::class, 'index']);

    // Favorites routes
    Route::get('/favorites', [PokemonController::class, 'getFavorites']);
    Route::get('/favorites/abilities', [PokemonController::class, 'getFavoriteAbilities']);

    // Get pokemons by ability from favorites
    Route::get('/by-ability/{ability}', [PokemonController::class, 'getPokemonsByAbility']);

    // Get specific pokemon by ID or name from PokeAPI
    Route::get('/{pokemon}', [PokemonController::class, 'show']);

    // Favorite/Unfavorite pokemon
    Route::post('/{pokemon}/favorite', [PokemonController::class, 'addToFavorites']);
    Route::delete('/{pokemon}/favorite', [PokemonController::class, 'removeFromFavorites']);
});
