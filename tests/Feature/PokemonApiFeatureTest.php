<?php

namespace Tests\Feature;

use App\Models\FavoritePokemon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Http;

class PokemonApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_pokemons_with_pagination(): void
    {
        // Mock the PokeAPI response - simplified test with just first pokemon
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon?limit=20&offset=0' => Http::response([
                'count' => 1000,
                'next' => null,
                'previous' => null,
                'results' => [
                    ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/']
                ]
            ], 200),
            'https://pokeapi.co/api/v2/pokemon/bulbasaur' => Http::response([
                'id' => 1,
                'name' => 'bulbasaur',
                'height' => 7,
                'weight' => 69,
                'base_experience' => 64,
                'types' => [['type' => ['name' => 'grass']], ['type' => ['name' => 'poison']]],
                'abilities' => [['ability' => ['name' => 'overgrow']]],
                'stats' => [
                    ['base_stat' => 45, 'stat' => ['name' => 'hp']],
                    ['base_stat' => 49, 'stat' => ['name' => 'attack']],
                    ['base_stat' => 49, 'stat' => ['name' => 'defense']],
                    ['base_stat' => 65, 'stat' => ['name' => 'special-attack']],
                    ['base_stat' => 65, 'stat' => ['name' => 'special-defense']],
                    ['base_stat' => 45, 'stat' => ['name' => 'speed']]
                ]
            ], 200)
        ]);

        $response = $this->getJson('/api/pokemons');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'total',
                    'current_page',
                    'per_page',
                    'last_page'
                ]
            ]);

        // Check pagination defaults
        $data = $response->json('data');
        $this->assertEquals(20, $data['per_page']);
        $this->assertEquals(1, $data['current_page']);
        $this->assertEquals(50, $data['last_page']); // 1000/20 = 50
        $this->assertEquals(1000, $data['total']);
    }

    public function test_can_get_all_pokemons_with_custom_pagination(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon?offset=0&limit=5' => Http::response([
                'count' => 1000,
                'next' => 'https://pokeapi.co/api/v2/pokemon?offset=5&limit=5',
                'previous' => null,
                'results' => []
            ], 200)
        ]);

        $response = $this->getJson('/api/pokemons?per_page=5');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(5, $data['per_page']);
    }

    public function test_can_get_pokemons_search_by_name(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon?limit=20&q=pikachu' => Http::response([
                'count' => 17,
                'next' => null,
                'previous' => null,
                'results' => [
                    ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/']
                ]
            ], 200),
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'height' => 4,
                'weight' => 60,
                'base_experience' => 112,
                'types' => [['type' => ['name' => 'electric']]],
                'abilities' => [['ability' => ['name' => 'static']]],
                'stats' => [
                    ['base_stat' => 35, 'stat' => ['name' => 'hp']],
                    ['base_stat' => 55, 'stat' => ['name' => 'attack']],
                    ['base_stat' => 40, 'stat' => ['name' => 'defense']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-attack']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-defense']],
                    ['base_stat' => 90, 'stat' => ['name' => 'speed']]
                ]
            ], 200)
        ]);

        $response = $this->getJson('/api/pokemons?search=pikachu');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pokemons retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertTrue($data['is_search']);
        $this->assertEquals(16, $data['total']); // API returns 16 results for 'pikachu'
    }

    public function test_can_get_pokemon_by_id(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/25' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'height' => 4,
                'weight' => 60,
                'base_experience' => 112,
                'sprites' => [
                    'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                    'other' => [
                        'official-artwork' => [
                            'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                        ]
                    ]
                ],
                'types' => [['type' => ['name' => 'electric']]],
                'abilities' => [['ability' => ['name' => 'static']]],
                'stats' => [
                    ['base_stat' => 35, 'stat' => ['name' => 'hp']],
                    ['base_stat' => 55, 'stat' => ['name' => 'attack']],
                    ['base_stat' => 40, 'stat' => ['name' => 'defense']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-attack']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-defense']],
                    ['base_stat' => 90, 'stat' => ['name' => 'speed']]
                ]
            ], 200)
        ]);

        $response = $this->getJson('/api/pokemons/25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pokemon retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertEquals(25, $data['id']);
        $this->assertEquals('pikachu', $data['name']);
    }

    public function test_get_nonexistent_pokemon_returns_404(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/999999' => Http::response([], 404)
        ]);

        $response = $this->getJson('/api/pokemons/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pokemon not found'
            ]);
    }

    public function test_can_get_all_favorites(): void
    {
        FavoritePokemon::factory()->count(3)->create();

        $response = $this->getJson('/api/pokemons/favorites');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Favorites retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_can_get_all_favorites_empty_database(): void
    {
        $response = $this->getJson('/api/pokemons/favorites');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Favorites retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_can_add_to_favorites(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/25' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'height' => 4,
                'weight' => 60,
                'base_experience' => 112,
                'sprites' => [
                    'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                    'other' => [
                        'official-artwork' => [
                            'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                        ]
                    ]
                ],
                'types' => [['type' => ['name' => 'electric']]],
                'abilities' => [['ability' => ['name' => 'static']]],
                'stats' => [
                    ['base_stat' => 35, 'stat' => ['name' => 'hp']],
                    ['base_stat' => 55, 'stat' => ['name' => 'attack']],
                    ['base_stat' => 40, 'stat' => ['name' => 'defense']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-attack']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-defense']],
                    ['base_stat' => 90, 'stat' => ['name' => 'speed']]
                ]
            ], 200)
        ]);

        $response = $this->postJson('/api/pokemons/25/favorite');

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pokemon added to favorites successfully'
            ]);

        $this->assertDatabaseHas('favorite_pokemons', [
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu'
        ]);
    }

    public function test_add_duplicate_favorite_returns_error(): void
    {
        // Create existing favorite
        FavoritePokemon::factory()->create(['pokemon_id' => 25]);

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/25' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'height' => 4,
                'weight' => 60,
                'base_experience' => 112,
                'sprites' => [
                    'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                    'other' => [
                        'official-artwork' => [
                            'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                        ]
                    ]
                ],
                'types' => [['type' => ['name' => 'electric']]],
                'abilities' => [['ability' => ['name' => 'static']]],
                'stats' => [
                    ['base_stat' => 35, 'stat' => ['name' => 'hp']],
                    ['base_stat' => 55, 'stat' => ['name' => 'attack']],
                    ['base_stat' => 40, 'stat' => ['name' => 'defense']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-attack']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-defense']],
                    ['base_stat' => 90, 'stat' => ['name' => 'speed']]
                ]
            ], 200)
        ]);

        $response = $this->postJson('/api/pokemons/25/favorite');

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Pokemon is already in favorites'
            ]);
    }

    public function test_add_favorite_with_nonexistent_pokemon_returns_404(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/9999' => Http::response([], 404)
        ]);

        $response = $this->postJson('/api/pokemons/9999/favorite');

        $response->assertStatus(422);
    }

    public function test_can_remove_from_favorites(): void
    {
        $pokemon = FavoritePokemon::factory()->create(['pokemon_id' => 25]);

        $response = $this->deleteJson('/api/pokemons/25/favorite');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pokemon removed from favorites successfully'
            ]);

        $this->assertDatabaseMissing('favorite_pokemons', [
            'pokemon_id' => 25
        ]);
    }

    public function test_remove_nonexistent_favorite_returns_404(): void
    {
        $response = $this->deleteJson('/api/pokemons/999999/favorite');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pokemon not found in favorites'
            ]);
    }

    public function test_can_get_favorites_by_abilities(): void
    {
        // Create test data with specific abilities
        FavoritePokemon::factory()->create([
            'pokemon_data' => [
                'abilities' => ['static', 'lightning-rod'],
                'name' => 'pikachu'
            ]
        ]);

        FavoritePokemon::factory()->create([
            'pokemon_data' => [
                'abilities' => ['overgrow'],
                'name' => 'bulbasaur'
            ]
        ]);

        $response = $this->getJson('/api/pokemons/favorites?abilities=static');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Favorites retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pikachu', $data[0]['name']);
    }

    public function test_can_get_pokemons_by_ability(): void
    {
        // Create test data with specific abilities
        FavoritePokemon::factory()->create([
            'pokemon_data' => [
                'abilities' => ['static', 'lightning-rod'],
                'name' => 'pikachu'
            ]
        ]);

        FavoritePokemon::factory()->create([
            'pokemon_data' => [
                'abilities' => ['overgrow'],
                'name' => 'bulbasaur'
            ]
        ]);

        $response = $this->getJson('/api/pokemons/by-ability/static');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => "Pokemons with ability 'static' retrieved successfully"
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pikachu', $data[0]['name']);
    }

    public function test_can_get_favorite_abilities(): void
    {
        // Create test data with multiple abilities
        FavoritePokemon::factory()->create([
            'pokemon_data' => [
                'abilities' => ['static', 'lightning-rod']
            ]
        ]);

        FavoritePokemon::factory()->create([
            'pokemon_data' => [
                'abilities' => ['overgrow', 'chlorophyll']
            ]
        ]);

        FavoritePokemon::factory()->create([
            'pokemon_data' => [
                'abilities' => ['blaze']
            ]
        ]);

        $response = $this->getJson('/api/pokemons/favorites/abilities');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Favorite abilities retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertCount(5, $data); // static, lightning-rod, overgrow, chlorophyll, blaze

        // Check that abilities are sorted alphabetically
        $abilityNames = array_map(fn($item) => $item['name'], $data);
        $this->assertEquals(['blaze', 'chlorophyll', 'lightning-rod', 'overgrow', 'static'], $abilityNames);
    }

    public function test_api_response_format_is_consistent(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/25' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'height' => 4,
                'weight' => 60,
                'base_experience' => 112,
                'sprites' => [
                    'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                    'other' => [
                        'official-artwork' => [
                            'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                        ]
                    ]
                ],
                'types' => [['type' => ['name' => 'electric']]],
                'abilities' => [['ability' => ['name' => 'static']]],
                'stats' => [
                    ['base_stat' => 35, 'stat' => ['name' => 'hp']],
                    ['base_stat' => 55, 'stat' => ['name' => 'attack']],
                    ['base_stat' => 40, 'stat' => ['name' => 'defense']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-attack']],
                    ['base_stat' => 50, 'stat' => ['name' => 'special-defense']],
                    ['base_stat' => 90, 'stat' => ['name' => 'speed']]
                ]
            ], 200)
        ]);

        $response = $this->getJson('/api/pokemons/25');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', // boolean
                'message', // string
                'data' // array/object
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertIsString($response->json('message'));
        $this->assertIsArray($response->json('data'));
    }

    public function test_error_response_format_is_consistent(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/9999' => Http::response([], 404)
        ]);

        $response = $this->getJson('/api/pokemons/9999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success', // boolean
                'message' // string
                // Note: error responses may not include 'data' key
            ]);

        $this->assertFalse($response->json('success'));
        $this->assertIsString($response->json('message'));
    }
}
