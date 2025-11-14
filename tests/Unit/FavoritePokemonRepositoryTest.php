<?php

namespace Tests\Unit;

use App\Models\FavoritePokemon;
use App\Repositories\Eloquent\FavoritePokemonRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FavoritePokemonRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FavoritePokemonRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FavoritePokemonRepository();
    }

    public function test_can_add_favorite_pokemon_with_data(): void
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

        $favorite = $this->repository->addFavoriteWithData($pokemonData);

        $this->assertInstanceOf(FavoritePokemon::class, $favorite);
        $this->assertEquals(25, $favorite->pokemon_id);
        $this->assertEquals('pikachu', $favorite->pokemon_name);
        $this->assertIsArray($favorite->pokemon_data);
        $this->assertEquals('pikachu', $favorite->pokemon_data['name']);
        $this->assertEquals(25, $favorite->pokemon_data['id']);
        $this->assertEquals(['electric'], $favorite->pokemon_data['types']);
    }

    public function test_can_update_existing_favorite_pokemon(): void
    {
        $pokemonData = [
            'id' => 25,
            'name' => 'pikachu',
            'types' => ['electric'],
        ];

        // Add first favorite
        $favorite = $this->repository->addFavoriteWithData($pokemonData);
        $this->assertEquals('pikachu', $favorite->pokemon_name);

        // Update with new data
        $updatedPokemonData = [
            'id' => 25,
            'name' => 'pikachu-updated',
            'types' => ['electric', 'fairy'],
        ];

        $updatedFavorite = $this->repository->addFavoriteWithData($updatedPokemonData);

        // Should update existing record, not create new one
        $this->assertEquals($favorite->id, $updatedFavorite->id);
        $this->assertEquals('pikachu-updated', $updatedFavorite->pokemon_name);
        $this->assertEquals(['electric', 'fairy'], $updatedFavorite->pokemon_data['types']);

        // Should still only have one record in database
        $this->assertDatabaseCount('favorite_pokemons', 1);
    }

    public function test_can_remove_favorite_pokemon(): void
    {
        $favorite = FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu'
        ]);

        $result = $this->repository->removeFavorite(25);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('favorite_pokemons', [
            'id' => $favorite->id,
            'pokemon_id' => 25
        ]);
    }

    public function test_remove_nonexistent_favorite_returns_false(): void
    {
        $result = $this->repository->removeFavorite(9999);

        $this->assertFalse($result);
    }

    public function test_can_get_all_favorites(): void
    {
        // Create multiple favorites
        FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
            'pokemon_data' => ['id' => 25, 'name' => 'pikachu']
        ]);
        FavoritePokemon::factory()->create([
            'pokemon_id' => 4,
            'pokemon_name' => 'charmander',
            'pokemon_data' => ['id' => 4, 'name' => 'charmander']
        ]);
        FavoritePokemon::factory()->create([
            'pokemon_id' => 1,
            'pokemon_name' => 'bulbasaur',
            'pokemon_data' => ['id' => 1, 'name' => 'bulbasaur']
        ]);

        $favorites = $this->repository->getAll();

        $this->assertInstanceOf(Collection::class, $favorites);
        $this->assertCount(3, $favorites);

        // Should return pokemon_data, not model instances
        $this->assertIsArray($favorites->first());
        $this->assertEquals('pikachu', $favorites->first()['name']);
    }

    public function test_can_check_if_pokemon_is_favorite(): void
    {
        FavoritePokemon::factory()->create(['pokemon_id' => 25]);

        $isFavorite = $this->repository->isFavorite(25);
        $this->assertTrue($isFavorite);

        $isNotFavorite = $this->repository->isFavorite(999);
        $this->assertFalse($isNotFavorite);
    }

    public function test_can_filter_by_abilities(): void
    {
        // Create favorites with different abilities
        FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
            'pokemon_data' => [
                'id' => 25,
                'name' => 'pikachu',
                'abilities' => ['static', 'lightning-rod']
            ]
        ]);
        FavoritePokemon::factory()->create([
            'pokemon_id' => 4,
            'pokemon_name' => 'charmander',
            'pokemon_data' => [
                'id' => 4,
                'name' => 'charmander',
                'abilities' => ['blaze', 'solar-power']
            ]
        ]);
        FavoritePokemon::factory()->create([
            'pokemon_id' => 1,
            'pokemon_name' => 'bulbasaur',
            'pokemon_data' => [
                'id' => 1,
                'name' => 'bulbasaur',
                'abilities' => ['overgrow', 'chlorophyll']
            ]
        ]);

        $filtered = $this->repository->filterByAbilities(['static', 'blaze']);

        $this->assertInstanceOf(Collection::class, $filtered);
        $this->assertCount(2, $filtered);

        // Should return pokemon_data
        $pokemonNames = $filtered->pluck('name')->toArray();
        $this->assertContains('pikachu', $pokemonNames);
        $this->assertContains('charmander', $pokemonNames);
        $this->assertNotContains('bulbasaur', $pokemonNames);
    }

    public function test_filter_by_abilities_with_single_ability(): void
    {
        FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_data' => [
                'id' => 25,
                'name' => 'pikachu',
                'abilities' => ['static']
            ]
        ]);

        $filtered = $this->repository->filterByAbilities(['static']);

        $this->assertCount(1, $filtered);
        $this->assertEquals('pikachu', $filtered->first()['name']);
    }

    public function test_filter_by_abilities_with_no_matches(): void
    {
        FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_data' => [
                'id' => 25,
                'name' => 'pikachu',
                'abilities' => ['static']
            ]
        ]);

        $filtered = $this->repository->filterByAbilities(['nonexistent-ability']);

        $this->assertCount(0, $filtered);
    }

    public function test_get_all_returns_ordered_by_created_at_desc(): void
    {
        $first = FavoritePokemon::factory()->create([
            'pokemon_id' => 1,
            'pokemon_name' => 'bulbasaur',
            'pokemon_data' => ['id' => 1, 'name' => 'bulbasaur'],
            'created_at' => now()->subMinutes(5)
        ]);
        $second = FavoritePokemon::factory()->create([
            'pokemon_id' => 4,
            'pokemon_name' => 'charmander',
            'pokemon_data' => ['id' => 4, 'name' => 'charmander'],
            'created_at' => now()->subMinutes(2)
        ]);
        $third = FavoritePokemon::factory()->create([
            'pokemon_id' => 25,
            'pokemon_name' => 'pikachu',
            'pokemon_data' => ['id' => 25, 'name' => 'pikachu'],
            'created_at' => now()->subMinutes(1)
        ]);

        $favorites = $this->repository->getAll();

        // Should be ordered by created_at descending (newest first)
        $this->assertEquals('pikachu', $favorites[0]['name']);
        $this->assertEquals('charmander', $favorites[1]['name']);
        $this->assertEquals('bulbasaur', $favorites[2]['name']);
    }

    public function test_add_favorite_with_minimal_data(): void
    {
        $pokemonData = [
            'id' => 999,
            'name' => 'minimal-pokemon'
        ];

        $favorite = $this->repository->addFavoriteWithData($pokemonData);

        $this->assertInstanceOf(FavoritePokemon::class, $favorite);
        $this->assertEquals(999, $favorite->pokemon_id);
        $this->assertEquals('minimal-pokemon', $favorite->pokemon_name);
        $this->assertEquals($pokemonData, $favorite->pokemon_data);
    }
}