<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\PokemonResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PokemonResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_transforms_pokemon_data_correctly(): void
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

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        $this->assertEquals($pokemonData['id'], $result['id']);
        $this->assertEquals($pokemonData['name'], $result['name']);
        $this->assertEquals($pokemonData['pokedex_number'], $result['pokedex_number']);
        $this->assertEquals($pokemonData['image_url'], $result['image_url']);
        $this->assertEquals($pokemonData['types'], $result['types']);
        $this->assertEquals($pokemonData['abilities'], $result['abilities']);
        $this->assertEquals($pokemonData['height'], $result['height']);
        $this->assertEquals($pokemonData['weight'], $result['weight']);
        $this->assertEquals($pokemonData['hp'], $result['hp']);
        $this->assertEquals($pokemonData['attack'], $result['attack']);
        $this->assertEquals($pokemonData['defense'], $result['defense']);
        $this->assertEquals($pokemonData['special_attack'], $result['special_attack']);
        $this->assertEquals($pokemonData['special_defense'], $result['special_defense']);
        $this->assertEquals($pokemonData['speed'], $result['speed']);
    }

    public function test_handles_null_image_url(): void
    {
        $pokemonData = [
            'id' => 25,
            'name' => 'pikachu',
            'pokedex_number' => 25,
            'image_url' => null,
            'types' => ['electric'],
            'abilities' => ['static'],
            'height' => 4,
            'weight' => 60,
            'hp' => 35,
            'attack' => 55,
            'defense' => 40,
            'special_attack' => 50,
            'special_defense' => 50,
            'speed' => 90,
        ];

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        $this->assertNull($result['image_url']);
    }

    public function test_handles_multiple_types(): void
    {
        $pokemonData = [
            'id' => 1,
            'name' => 'bulbasaur',
            'pokedex_number' => 1,
            'image_url' => 'https://example.com/bulbasaur.png',
            'types' => ['grass', 'poison'],
            'abilities' => ['overgrow'],
            'height' => 7,
            'weight' => 69,
            'hp' => 45,
            'attack' => 49,
            'defense' => 49,
            'special_attack' => 65,
            'special_defense' => 65,
            'speed' => 45,
        ];

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        $this->assertCount(2, $result['types']);
        $this->assertContains('grass', $result['types']);
        $this->assertContains('poison', $result['types']);
    }

    public function test_handles_multiple_abilities(): void
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

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        $this->assertCount(2, $result['abilities']);
        $this->assertContains('static', $result['abilities']);
        $this->assertContains('lightning-rod', $result['abilities']);
    }

    public function test_returns_correct_data_structure(): void
    {
        $pokemonData = [
            'id' => 25,
            'name' => 'pikachu',
            'pokedex_number' => 25,
            'image_url' => 'https://example.com/pikachu.png',
            'types' => ['electric'],
            'abilities' => ['static'],
            'height' => 4,
            'weight' => 60,
            'hp' => 35,
            'attack' => 55,
            'defense' => 40,
            'special_attack' => 50,
            'special_defense' => 50,
            'speed' => 90,
        ];

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        // Check that all expected keys are present
        $expectedKeys = [
            'id', 'name', 'pokedex_number', 'image_url',
            'types', 'abilities', 'height', 'weight',
            'hp', 'attack', 'defense', 'special_attack',
            'special_defense', 'speed', 'base_experience'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }

        // Check that no timestamps are included (as per resource design)
        $this->assertArrayNotHasKey('created_at', $result);
        $this->assertArrayNotHasKey('updated_at', $result);
    }

    public function test_handles_array_input(): void
    {
        $pokemonData = [
            'id' => 25,
            'name' => 'pikachu',
            'pokedex_number' => 25,
            'image_url' => 'https://example.com/pikachu.png',
            'types' => ['electric'],
            'abilities' => ['static'],
            'height' => 4,
            'weight' => 60,
            'hp' => 35,
            'attack' => 55,
            'defense' => 40,
            'special_attack' => 50,
            'special_defense' => 50,
            'speed' => 90,
        ];

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        $this->assertEquals('pikachu', $result['name']);
        $this->assertEquals(['electric'], $result['types']);
    }

    public function test_can_be_used_as_collection(): void
    {
        $pokemonList = [
            [
                'id' => 25,
                'name' => 'pikachu',
                'pokedex_number' => 25,
                'types' => ['electric'],
                'abilities' => ['static'],
                'height' => 4,
                'weight' => 60,
                'hp' => 35,
                'attack' => 55,
                'defense' => 40,
                'special_attack' => 50,
                'special_defense' => 50,
                'speed' => 90,
            ],
            [
                'id' => 4,
                'name' => 'charmander',
                'pokedex_number' => 4,
                'types' => ['fire'],
                'abilities' => ['blaze'],
                'height' => 6,
                'weight' => 85,
                'hp' => 39,
                'attack' => 52,
                'defense' => 43,
                'special_attack' => 60,
                'special_defense' => 50,
                'speed' => 65,
            ]
        ];

        $resource = PokemonResource::collection($pokemonList);
        $result = $resource->toArray(request());

        $this->assertCount(2, $result);
        $this->assertEquals('pikachu', $result[0]['name']);
        $this->assertEquals('charmander', $result[1]['name']);
        $this->assertEquals(['electric'], $result[0]['types']);
        $this->assertEquals(['fire'], $result[1]['types']);
    }

    public function test_handles_zero_stats(): void
    {
        $pokemonData = [
            'id' => 999,
            'name' => 'testpokemon',
            'pokedex_number' => 999,
            'image_url' => null,
            'types' => ['normal'],
            'abilities' => [],
            'height' => 0,
            'weight' => 0,
            'hp' => 0,
            'attack' => 0,
            'defense' => 0,
            'special_attack' => 0,
            'special_defense' => 0,
            'speed' => 0,
        ];

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        $this->assertEquals(0, $result['hp']);
        $this->assertEquals(0, $result['attack']);
        $this->assertEquals(0, $result['defense']);
        $this->assertEquals(0, $result['special_attack']);
        $this->assertEquals(0, $result['special_defense']);
        $this->assertEquals(0, $result['speed']);
        $this->assertEquals(0, $result['height']);
        $this->assertEquals(0, $result['weight']);
    }

    public function test_handles_max_stats(): void
    {
        $pokemonData = [
            'id' => 999,
            'name' => 'testpokemon',
            'pokedex_number' => 999,
            'image_url' => null,
            'types' => ['normal'],
            'abilities' => [],
            'height' => 200,
            'weight' => 2000,
            'hp' => 255,
            'attack' => 255,
            'defense' => 255,
            'special_attack' => 255,
            'special_defense' => 255,
            'speed' => 255,
        ];

        $resource = new PokemonResource($pokemonData);
        $result = $resource->toArray(request());

        $this->assertEquals(255, $result['hp']);
        $this->assertEquals(255, $result['attack']);
        $this->assertEquals(255, $result['defense']);
        $this->assertEquals(255, $result['special_attack']);
        $this->assertEquals(255, $result['special_defense']);
        $this->assertEquals(255, $result['speed']);
        $this->assertEquals(200, $result['height']);
        $this->assertEquals(2000, $result['weight']);
    }
}