<?php

namespace App\Http\Controllers\API;

use App\Contracts\ApiResponseHandlerContract;
use App\Contracts\FavoriteRepositoryContract;
use App\Contracts\PokeApiServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddFavoritePokemonRequest;
use App\Http\Requests\RemoveFavoritePokemonRequest;
use App\Http\Resources\FavoritePokemonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PokemonController extends Controller
{
    protected $pokeApiService;
    protected $favoriteRepository;
    protected $responseHandler;

    public function __construct(
        PokeApiServiceContract $pokeApiService,
        FavoriteRepositoryContract $favoriteRepository,
        ApiResponseHandlerContract $responseHandler
    ) {
        $this->pokeApiService = $pokeApiService;
        $this->favoriteRepository = $favoriteRepository;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Display a listing of pokemons from PokeAPI
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 20);
            $search = $request->query('search');
            $type = $request->query('type');

            if ($search) {
                $searchLimit = min($perPage, 50);
                $pokemons = $this->pokeApiService->searchPokemonByName($search, $searchLimit);
                $pokemons = $this->filterByType($pokemons, $type);
                $total = count($pokemons);

                return $this->responseHandler->success([
                    'data' => $pokemons,
                    'total' => $total,
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'is_search' => true,
                ], 'Pokemons retrieved successfully');
            }

            $result = $this->pokeApiService->getPokemonList($page, $perPage);
            $pokemons = $this->filterByType($result['pokemons'], $type);

            return $this->responseHandler->success([
                'data' => $pokemons,
                'total' => $result['count'],
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($result['count'] / $perPage),
            ], 'Pokemons retrieved successfully');
        } catch (\Exception $e) {
            return $this->responseHandler->error('Failed to retrieve pokemons: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Filter pokemons by type
     *
     * @param array $pokemons
     * @param string|null $type
     * @return array
     */
    private function filterByType(array $pokemons, ?string $type): array
    {
        if (!$type) {
            return $pokemons;
        }

        return array_values(array_filter($pokemons, function ($pokemon) use ($type) {
            $data = $pokemon->resolve();
            return in_array($type, $data['types'] ?? []);
        }));
    }

    /**
     * Display the specified pokemon from PokeAPI
     *
     * @param string $pokemon
     * @return JsonResponse
     */
    public function show(string $pokemon): JsonResponse
    {
        try {
            $pokemonData = $this->pokeApiService->getPokemonDetail($pokemon);

            if ($pokemonData) {
                return $this->responseHandler->success(
                    $pokemonData,
                    'Pokemon retrieved successfully'
                );
            }

            return $this->responseHandler->error('Pokemon not found', 404);
        } catch (\Exception $e) {
            return $this->responseHandler->error('Failed to retrieve pokemon: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add pokemon to favorites
     *
     * @param AddFavoritePokemonRequest $request
     * @return JsonResponse
     */
    public function addToFavorites(AddFavoritePokemonRequest $request): JsonResponse
    {
        try {
            $pokemonId = $request->validated()['pokemon_id'];

            if ($this->favoriteRepository->isFavorite($pokemonId)) {
                return $this->responseHandler->error('Pokemon is already in favorites', 409);
            }

            $pokemonData = $this->pokeApiService->getPokemonDetail($pokemonId);

            if (!$pokemonData) {
                return $this->responseHandler->error('Pokemon not found', 404);
            }

            $this->favoriteRepository->addFavoriteWithData($pokemonData->resolve());

            return $this->responseHandler->success(
                $pokemonData,
                'Pokemon added to favorites successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->responseHandler->error('Failed to add to favorites: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove pokemon from favorites
     *
     * @param RemoveFavoritePokemonRequest $request
     * @return JsonResponse
     */
    public function removeFromFavorites(RemoveFavoritePokemonRequest $request): JsonResponse
    {
        try {
            $pokemonId = $request->validated()['pokemon_id'];

            $removed = $this->favoriteRepository->removeFavorite($pokemonId);

            if (!$removed) {
                return $this->responseHandler->error('Pokemon not found in favorites', 404);
            }

            return $this->responseHandler->success(null, 'Pokemon removed from favorites successfully');
        } catch (\Exception $e) {
            return $this->responseHandler->error('Failed to remove from favorites: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all favorites with optional search and ability filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFavorites(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');
            $abilities = $request->query('abilities');

            if ($abilities) {
                $abilitiesArray = is_array($abilities) ? $abilities : explode(',', $abilities);
                $favorites = $this->favoriteRepository->filterByAbilities($abilitiesArray);
            } else {
                $favorites = $this->favoriteRepository->getAll();
            }

            if ($search) {
                $searchLower = strtolower($search);
                $favorites = collect($favorites)->filter(function($pokemon) use ($searchLower) {
                    $name = strtolower($pokemon['name'] ?? '');
                    return strpos($name, $searchLower) !== false;
                })->values()->all();
            }

            return $this->responseHandler->success(
                FavoritePokemonResource::collection($favorites),
                'Favorites retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseHandler->error('Failed to retrieve favorites: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get list of unique abilities from favorite pokemons
     *
     * @return JsonResponse
     */
    public function getFavoriteAbilities(): JsonResponse
    {
        try {
            $favorites = $this->favoriteRepository->getAll();

            $abilitiesSet = [];
            foreach ($favorites as $pokemonData) {
                $abilities = $pokemonData['abilities'] ?? [];

                if (is_array($abilities)) {
                    foreach ($abilities as $ability) {
                        if (!empty($ability)) {
                            $abilitiesSet[$ability] = true;
                        }
                    }
                }
            }

            $abilities = array_map(function($ability) {
                return ['name' => $ability];
            }, array_keys($abilitiesSet));

            usort($abilities, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return $this->responseHandler->success(
                $abilities,
                'Favorite abilities retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseHandler->error('Failed to retrieve abilities: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get pokemons by ability from favorites
     *
     * @param string $ability
     * @return JsonResponse
     */
    public function getPokemonsByAbility(string $ability): JsonResponse
    {
        try {
            $favorites = $this->favoriteRepository->filterByAbilities([$ability]);

            return $this->responseHandler->success(
                FavoritePokemonResource::collection($favorites),
                "Pokemons with ability '{$ability}' retrieved successfully"
            );
        } catch (\Exception $e) {
            return $this->responseHandler->error('Failed to retrieve pokemons by ability: ' . $e->getMessage(), 500);
        }
    }
}
