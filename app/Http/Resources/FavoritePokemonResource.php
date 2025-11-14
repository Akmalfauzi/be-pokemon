<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoritePokemonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->id ?? $this->pokemon_id,
            'name' => $this->name ?? $this->pokemon_name,
            'pokedex_number' => $this->pokedex_number ?? $this->pokemon_id,
            'image_url' => $this->image_url,
            'types' => $this->types ?? [],
            'abilities' => $this->abilities ?? [],
            'height' => $this->height,
            'weight' => $this->weight,
            'base_experience' => $this->base_experience ?? 0,
            'hp' => $this->hp,
            'attack' => $this->attack,
            'defense' => $this->defense,
            'special_attack' => $this->special_attack,
            'special_defense' => $this->special_defense,
            'speed' => $this->speed,
        ];
    }
}
