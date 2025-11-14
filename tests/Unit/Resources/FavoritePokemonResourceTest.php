<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\FavoritePokemonResource;
use App\Models\FavoritePokemon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritePokemonResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_transforms_array_data_correctly(): void
    {
        $pokemonData = [
            'id' => 25,
            'name' => 'pikachu',
            'pokedex_number' => 25,
            'image_url' => 'https://example.com/pikachu.png',
            'types' => ['electric'],
            'abilities' => ['static', 'lightning-rod'],
            'height' => 4,
            'weight' => 60,
            'hp' => 35,
            'attack' => 55,
            'defense' => 40,
            'special_attack' => 50,
            'special_defense' => 50,
            'speed' => 90,
        ];

        $resource = new FavoritePokemonResource($pokemonData);
        $result = $resource->toArray(request());

        // When input is array, it should return the array directly
        $this->assertEquals($pokemonData, $result);
    }

    public function test_transforms_favorite_pokemon_model_correctly(): void
    {
        $favoritePokemon = FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
            'pokemon_data' => [
                'id' => 25,
                'name' => 'pikachu',
                'pokedex_number' => 25,
                'image_url' => 'https://example.com/pikachu.png',
                'types' => ['electric'],
                'abilities' => ['static', 'lightning-rod'],
                'height' => 4,
                'weight' => 60,
                'hp' => 35,
                'attack' => 55,
                'defense' => 40,
                'special_attack' => 50,
                'special_defense' => 50,
                'speed' => 90,
            ]
        ]);

        $resource = new FavoritePokemonResource($favoritePokemon);
        $result = $resource->toArray(request());

        $this->assertEquals($favoritePokemon->id, $result['id']);
        $this->assertEquals($favoritePokemon->pokemon_name, $result['name']);
        $this->assertEquals($favoritePokemon->pokemon_id, $result['pokedex_number']);
    }

    public function test_handles_null_values_in_model(): void
    {
        $favoritePokemon = FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
        ]);

        $resource = new FavoritePokemonResource($favoritePokemon);
        $result = $resource->toArray(request());

        $this->assertEquals($favoritePokemon->id, $result['id']);
        $this->assertEquals('pikachu', $result['name']);
        $this->assertEquals(25, $result['pokedex_number']);
        $this->assertNull($result['image_url']);
        $this->assertEquals([], $result['types']); // should default to empty array
        $this->assertEquals([], $result['abilities']); // should default to empty array
        $this->assertEquals(0, $result['base_experience']); // should default to 0
    }

    public function test_can_be_used_as_collection_with_arrays(): void
    {
        $pokemonDataList = [
            [
                'id' => 25,
                'name' => 'pikachu',
                'types' => ['electric'],
                'abilities' => ['static']
            ],
            [
                'id' => 4,
                'name' => 'charmander',
                'types' => ['fire'],
                'abilities' => ['blaze']
            ]
        ];

        $resource = FavoritePokemonResource::collection($pokemonDataList);
        $result = $resource->toArray(request());

        $this->assertCount(2, $result);
        $this->assertEquals('pikachu', $result[0]['name']);
        $this->assertEquals('charmander', $result[1]['name']);
        $this->assertEquals(['electric'], $result[0]['types']);
        $this->assertEquals(['fire'], $result[1]['types']);
    }

    public function test_can_be_used_as_collection_with_models(): void
    {
        $favorites = FavoritePokemon::factory()->count(2)->create();

        $resource = FavoritePokemonResource::collection($favorites);
        $result = $resource->toArray(request());

        $this->assertCount(2, $result);

        // Check that all items have the correct structure
        foreach ($result as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('pokedex_number', $item);
        }
    }

    public function test_handles_empty_array_input(): void
    {
        $emptyData = [];

        $resource = new FavoritePokemonResource($emptyData);
        $result = $resource->toArray(request());

        $this->assertEquals($emptyData, $result);
    }

    public function test_handles_partial_data(): void
    {
        $partialData = [
            'id' => 25,
            'name' => 'pikachu'
        ];

        $resource = new FavoritePokemonResource($partialData);
        $result = $resource->toArray(request());

        $this->assertEquals($partialData, $result);
    }

    public function test_handles_model_with_id_fallback(): void
    {
        $favoritePokemon = new FavoritePokemon([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu'
        ]);
        // Don't save to database, just test the resource logic

        $resource = new FavoritePokemonResource($favoritePokemon);
        $result = $resource->toArray(request());

        // Should use pokemon_id when id is not available
        $this->assertEquals(25, $result['id']);
        $this->assertEquals('pikachu', $result['name']);
        $this->assertEquals(25, $result['pokedex_number']);
    }

    public function test_returns_correct_data_structure_for_model(): void
    {
        $favoritePokemon = FavoritePokemon::factory()->create();

        $resource = new FavoritePokemonResource($favoritePokemon);
        $result = $resource->toArray(request());

        // Check that all expected keys are present for model input
        $expectedKeys = [
            'id', 'name', 'pokedex_number', 'image_url',
            'types', 'abilities', 'height', 'weight',
            'hp', 'attack', 'defense', 'special_attack',
            'special_defense', 'speed', 'base_experience'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }
    }
}