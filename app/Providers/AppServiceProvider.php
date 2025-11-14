<?php

namespace App\Providers;

use App\Contracts\ApiResponseHandlerContract;
use App\Contracts\FavoriteRepositoryContract;
use App\Contracts\PokeApiServiceContract;
use App\Handlers\JsonApiResponseHandler;
use App\Repositories\Eloquent\FavoritePokemonRepository;
// use App\Repositories\Mongo\FavoritePokemonMongoRepository; // Uncomment to use MongoDB
use App\Services\PokeApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind API Response Handler
        $this->app->bind(ApiResponseHandlerContract::class, JsonApiResponseHandler::class);

        // Bind PokeAPI Service
        $this->app->singleton(PokeApiServiceContract::class, PokeApiService::class);

        // Bind Favorite Repository
        // Choose one implementation: SQL (Eloquent) or MongoDB
        $this->app->bind(FavoriteRepositoryContract::class, FavoritePokemonRepository::class);

        // To use MongoDB instead, comment the line above and uncomment this:
        // $this->app->bind(FavoriteRepositoryContract::class, FavoritePokemonMongoRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
