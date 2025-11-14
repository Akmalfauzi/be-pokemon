<?php

namespace Tests\Unit;

use App\Services\PokeApiService;
use App\Http\Resources\PokemonResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PokeApiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_can_get_pokemon_list(): void
    {
        $mockListResponse = [
            'count' => 2,
            'next' => null,
            'previous' => null,
            'results' => [
                [
                    'name' => 'bulbasaur',
                    'url' => 'https://pokeapi.co/api/v2/pokemon/1/'
                ]
            ]
        ];

        $mockPokemonDetail = [
            'id' => 1,
            'name' => 'bulbasaur',
            'height' => 7,
            'weight' => 69,
            'base_experience' => 64,
            'types' => [
                ['type' => ['name' => 'grass']],
                ['type' => ['name' => 'poison']]
            ],
            'abilities' => [
                ['ability' => ['name' => 'overgrow']],
                ['ability' => ['name' => 'chlorophyll']]
            ],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 45],
                ['stat' => ['name' => 'attack'], 'base_stat' => 49],
                ['stat' => ['name' => 'defense'], 'base_stat' => 49],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 65],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 65],
                ['stat' => ['name' => 'speed'], 'base_stat' => 45],
            ],
            'sprites' => [
                'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/1.png',
                'other' => [
                    'official-artwork' => [
                        'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/1.png'
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon*' => Http::sequence()
                ->push($mockListResponse) // First call for list
                ->push($mockPokemonDetail) // Second call for detail
        ]);

        $service = app(PokeApiService::class);
        $result = $service->getPokemonList(1, 20);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('pokemons', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('next', $result);
        $this->assertArrayHasKey('previous', $result);
        $this->assertEquals(2, $result['count']);
        $this->assertCount(1, $result['pokemons']);
    }

    public function test_caches_pokemon_list_response(): void
    {
        $mockListResponse = [
            'count' => 2,
            'results' => [
                ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/']
            ]
        ];

        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'types' => [['type' => ['name' => 'electric']]],
            'abilities' => [['ability' => ['name' => 'static']]],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 35],
                ['stat' => ['name' => 'attack'], 'base_stat' => 55],
            ],
            'sprites' => ['front_default' => 'https://example.com/pikachu.png']
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon*' => Http::sequence()
                ->push($mockListResponse)
                ->push($mockPokemonDetail)
        ]);

        $service = app(PokeApiService::class);

        // First call should hit the API
        $service->getPokemonList(1, 20);
        $this->assertEquals(2, Http::recorded()->count());

        // Second call should use cache
        $service->getPokemonList(1, 20);
        $this->assertEquals(2, Http::recorded()->count()); // Should still be 2, not 4
    }

    public function test_can_get_pokemon_detail(): void
    {
        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'types' => [
                ['type' => ['name' => 'electric']]
            ],
            'abilities' => [
                ['ability' => ['name' => 'static']],
                ['ability' => ['name' => 'lightning-rod']]
            ],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 35],
                ['stat' => ['name' => 'attack'], 'base_stat' => 55],
                ['stat' => ['name' => 'defense'], 'base_stat' => 40],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 50],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 50],
                ['stat' => ['name' => 'speed'], 'base_stat' => 90],
            ],
            'sprites' => [
                'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                'other' => [
                    'official-artwork' => [
                        'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response($mockPokemonDetail, 200),
        ]);

        $service = app(PokeApiService::class);
        $result = $service->getPokemonDetail('pikachu');

        $this->assertInstanceOf(PokemonResource::class, $result);
        $pokemonData = $result->toArray(request());
        $this->assertEquals('pikachu', $pokemonData['name']);
        $this->assertEquals(25, $pokemonData['id']);
        $this->assertEquals(['electric'], $pokemonData['types']);
        $this->assertEquals(['static', 'lightning-rod'], $pokemonData['abilities']);
        $this->assertEquals(35, $pokemonData['hp']);
        $this->assertEquals(55, $pokemonData['attack']);
    }

    public function test_returns_null_for_nonexistent_pokemon(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/nonexistent' => Http::response(['detail' => 'Not found'], 404),
        ]);

        $service = app(PokeApiService::class);
        $result = $service->getPokemonDetail('nonexistent');

        $this->assertNull($result);
    }

    public function test_caches_pokemon_detail_response(): void
    {
        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'types' => [['type' => ['name' => 'electric']]],
            'abilities' => [['ability' => ['name' => 'static']]],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 35],
                ['stat' => ['name' => 'attack'], 'base_stat' => 55],
                ['stat' => ['name' => 'defense'], 'base_stat' => 40],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 50],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 50],
                ['stat' => ['name' => 'speed'], 'base_stat' => 90],
            ],
            'sprites' => [
                'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                'other' => [
                    'official-artwork' => [
                        'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response($mockPokemonDetail, 200),
        ]);

        $service = app(PokeApiService::class);

        // First call should hit the API
        $service->getPokemonDetail('pikachu');
        $this->assertEquals(1, Http::recorded()->count());

        // Second call should use cache - we can't actually test caching here
        // because Cache::remember uses Laravel's cache system internally
        // but we can verify the method works without hitting API twice
        $result = $service->getPokemonDetail('pikachu');
        $this->assertEquals(1, Http::recorded()->count()); // Should still be 1, not 2
        $this->assertInstanceOf(PokemonResource::class, $result);
    }

    public function test_can_search_pokemon_by_name(): void
    {
        $mockAllNamesResponse = [
            'results' => [
                ['name' => 'pikachu'],
                ['name' => 'pichu'],
                ['name' => 'raichu'],
                ['name' => 'charmander'],
            ]
        ];

        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'types' => [['type' => ['name' => 'electric']]],
            'abilities' => [['ability' => ['name' => 'static']]],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 35],
                ['stat' => ['name' => 'attack'], 'base_stat' => 55],
                ['stat' => ['name' => 'defense'], 'base_stat' => 40],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 50],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 50],
                ['stat' => ['name' => 'speed'], 'base_stat' => 90],
            ],
            'sprites' => [
                'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                'other' => [
                    'official-artwork' => [
                        'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                    ]
                ]
            ]
        ];

        $mockPichuDetail = [
            'id' => 172,
            'name' => 'pichu',
            'height' => 2,
            'weight' => 20,
            'base_experience' => 41,
            'types' => [['type' => ['name' => 'electric']]],
            'abilities' => [['ability' => ['name' => 'static']]],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 20],
                ['stat' => ['name' => 'attack'], 'base_stat' => 40],
                ['stat' => ['name' => 'defense'], 'base_stat' => 15],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 40],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 35],
                ['stat' => ['name' => 'speed'], 'base_stat' => 60],
            ],
            'sprites' => [
                'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/172.png',
                'other' => [
                    'official-artwork' => [
                        'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/172.png'
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon*limit=2000*' => Http::response($mockAllNamesResponse, 200),
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response($mockPokemonDetail, 200),
            'https://pokeapi.co/api/v2/pokemon/pichu' => Http::response($mockPichuDetail, 200),
        ]);

        $service = app(PokeApiService::class);
        $result = $service->searchPokemonByName('pika', 10);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(2, count($result)); // Up to 2 results (pikachu and pichu)

        // Check that all results contain the search term
        foreach ($result as $pokemon) {
            $this->assertInstanceOf(PokemonResource::class, $pokemon);
            $pokemonData = $pokemon->toArray(request());
            $this->assertStringContainsString('pika', $pokemonData['name']);
        }
    }

    public function test_search_returns_empty_for_nonexistent_pokemon(): void
    {
        $mockAllNamesResponse = [
            'results' => [
                ['name' => 'pikachu'],
                ['name' => 'charmander'],
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon*limit=2000*' => Http::response($mockAllNamesResponse, 200),
        ]);

        $service = app(PokeApiService::class);
        $result = $service->searchPokemonByName('nonexistentpokemon', 10);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_search_handles_empty_query(): void
    {
        $service = app(PokeApiService::class);
        $result = $service->searchPokemonByName('', 10);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_handles_api_error_gracefully(): void
    {
        Http::fake([
            'https://pokeapi.co/api/v2/pokemon*' => Http::response('Server Error', 500),
        ]);

        $service = app(PokeApiService::class);

        // getPokemonList should return empty array on error
        $result = $service->getPokemonList(1, 20);
        $this->assertIsArray($result);
        $this->assertEquals(['pokemons' => [], 'count' => 0, 'next' => null, 'previous' => null], $result);

        // getPokemonDetail should return null on error
        $detail = $service->getPokemonDetail('pikachu');
        $this->assertNull($detail);

        // searchPokemonByName should return empty array on error
        $searchResult = $service->searchPokemonByName('pika', 10);
        $this->assertIsArray($searchResult);
        $this->assertEmpty($searchResult);
    }

    public function test_get_pokemon_detail_by_id(): void
    {
        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'types' => [
                ['type' => ['name' => 'electric']]
            ],
            'abilities' => [
                ['ability' => ['name' => 'static']],
                ['ability' => ['name' => 'lightning-rod']]
            ],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 35],
                ['stat' => ['name' => 'attack'], 'base_stat' => 55],
                ['stat' => ['name' => 'defense'], 'base_stat' => 40],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 50],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 50],
                ['stat' => ['name' => 'speed'], 'base_stat' => 90],
            ],
            'sprites' => [
                'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                'other' => [
                    'official-artwork' => [
                        'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/25' => Http::response($mockPokemonDetail, 200),
        ]);

        $service = app(PokeApiService::class);
        $result = $service->getPokemonDetail(25);

        $this->assertInstanceOf(PokemonResource::class, $result);
        $pokemonData = $result->toArray(request());
        $this->assertEquals('pikachu', $pokemonData['name']);
        $this->assertEquals(25, $pokemonData['id']);
    }

    public function test_clears_cache(): void
    {
        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'types' => [['type' => ['name' => 'electric']]],
            'stats' => [['stat' => ['name' => 'hp'], 'base_stat' => 35]],
            'sprites' => ['front_default' => 'https://example.com/pikachu.png']
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response($mockPokemonDetail, 200),
        ]);

        $service = app(PokeApiService::class);

        // First call
        $service->getPokemonDetail('pikachu');
        $this->assertEquals(1, Http::recorded()->count());

        // Clear cache
        $service->clearCache();

        // Second call should hit API again
        $service->getPokemonDetail('pikachu');
        $this->assertEquals(2, Http::recorded()->count());
    }

    public function test_transforms_pokemon_data_correctly(): void
    {
        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'types' => [
                ['type' => ['name' => 'electric']]
            ],
            'abilities' => [
                ['ability' => ['name' => 'static']],
                ['ability' => ['name' => 'lightning-rod']]
            ],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 35],
                ['stat' => ['name' => 'attack'], 'base_stat' => 55],
                ['stat' => ['name' => 'defense'], 'base_stat' => 40],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 50],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 50],
                ['stat' => ['name' => 'speed'], 'base_stat' => 90],
            ],
            'sprites' => [
                'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png',
                'other' => [
                    'official-artwork' => [
                        'front_default' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png'
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response($mockPokemonDetail, 200),
        ]);

        $service = app(PokeApiService::class);
        $result = $service->getPokemonDetail('pikachu');
        $pokemonData = $result->toArray(request());

        // Test that data is transformed correctly
        $this->assertEquals(25, $pokemonData['id']);
        $this->assertEquals('pikachu', $pokemonData['name']);
        $this->assertEquals(4, $pokemonData['height']);
        $this->assertEquals(60, $pokemonData['weight']);
        $this->assertEquals(112, $pokemonData['base_experience']);
        $this->assertEquals(['electric'], $pokemonData['types']);
        $this->assertEquals(['static', 'lightning-rod'], $pokemonData['abilities']);
        $this->assertEquals(35, $pokemonData['hp']);
        $this->assertEquals(55, $pokemonData['attack']);
        $this->assertEquals(40, $pokemonData['defense']);
        $this->assertEquals(50, $pokemonData['special_attack']);
        $this->assertEquals(50, $pokemonData['special_defense']);
        $this->assertEquals(90, $pokemonData['speed']);
        $this->assertEquals(
            'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png',
            $pokemonData['image_url']
        );
    }

    public function test_handles_missing_sprites(): void
    {
        $mockPokemonDetail = [
            'id' => 25,
            'name' => 'pikachu',
            'height' => 4,
            'weight' => 60,
            'base_experience' => 112,
            'types' => [],
            'abilities' => [],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 35],
                ['stat' => ['name' => 'attack'], 'base_stat' => 55],
                ['stat' => ['name' => 'defense'], 'base_stat' => 40],
                ['stat' => ['name' => 'special-attack'], 'base_stat' => 50],
                ['stat' => ['name' => 'special-defense'], 'base_stat' => 50],
                ['stat' => ['name' => 'speed'], 'base_stat' => 90],
            ],
            'sprites' => [
                'front_default' => 'https://example.com/pikachu.png'
                // No other artwork
            ]
        ];

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response($mockPokemonDetail, 200),
        ]);

        $service = app(PokeApiService::class);
        $result = $service->getPokemonDetail('pikachu');

        $this->assertNotNull($result);
        $pokemonData = $result->toArray(request());
        $this->assertEquals('https://example.com/pikachu.png', $pokemonData['image_url']);
    }
}