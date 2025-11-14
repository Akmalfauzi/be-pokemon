<?php

namespace Database\Factories;

use App\Models\FavoritePokemon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FavoritePokemon>
 */
class FavoritePokemonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FavoritePokemon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pokemonNames = [
            'pikachu', 'charizard', 'bulbasaur', 'squirtle', 'eevee',
            'snorlax', 'mewtwo', 'dragonite', 'gengar', 'alakazam'
        ];

        $name = $this->faker->unique()->randomElement($pokemonNames);
        $pokemonId = $this->faker->unique()->numberBetween(1, 1000);

        $pokemonData = [
            'id' => $pokemonId,
            'name' => $name,
            'pokedex_number' => $pokemonId,
            'image_url' => $this->faker->imageUrl(200, 200, 'animals'),
            'types' => $this->faker->randomElements([
                'normal', 'fire', 'water', 'electric', 'grass', 'ice',
                'fighting', 'poison', 'ground', 'flying', 'psychic',
                'bug', 'rock', 'ghost', 'dragon', 'dark', 'steel', 'fairy'
            ], $this->faker->numberBetween(1, 2)),
            'abilities' => $this->faker->randomElements([
                'overgrow', 'blaze', 'torrent', 'shield-dust', 'shed-skin',
                'static', 'lightning-rod', 'rain-dish', 'solar-power',
                'keen-eye', 'tangled-feet', 'big-pecks', 'guts', 'hustle',
                'intimidate', 'unnerve', 'cute-charm', 'competitive',
                'frisk', 'inner-focus', 'infiltrator', 'magic-guard'
            ], $this->faker->numberBetween(1, 2)),
            'height' => $this->faker->numberBetween(1, 200),
            'weight' => $this->faker->numberBetween(10, 2000),
            'hp' => $this->faker->numberBetween(1, 255),
            'attack' => $this->faker->numberBetween(1, 255),
            'defense' => $this->faker->numberBetween(1, 255),
            'special_attack' => $this->faker->numberBetween(1, 255),
            'special_defense' => $this->faker->numberBetween(1, 255),
            'speed' => $this->faker->numberBetween(1, 255),
        ];

        return [
            'pokemon_id' => $pokemonId,
            'pokemon_name' => $name,
            'pokemon_data' => $pokemonData,
        ];
    }
}