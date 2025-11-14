<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PokemonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_array($this->resource)) {
            return [
                'id' => $this->resource['id'] ?? null,
                'name' => $this->resource['name'] ?? null,
                'pokedex_number' => $this->resource['pokedex_number'] ?? $this->resource['id'] ?? null,
                'image_url' => $this->resource['image_url'] ?? null,
                'types' => $this->resource['types'] ?? [],
                'abilities' => $this->resource['abilities'] ?? [],
                'height' => $this->resource['height'] ?? 0,
                'weight' => $this->resource['weight'] ?? 0,
                'base_experience' => $this->resource['base_experience'] ?? 0,
                'hp' => $this->resource['hp'] ?? 0,
                'attack' => $this->resource['attack'] ?? 0,
                'defense' => $this->resource['defense'] ?? 0,
                'special_attack' => $this->resource['special_attack'] ?? 0,
                'special_defense' => $this->resource['special_defense'] ?? 0,
                'speed' => $this->resource['speed'] ?? 0,
            ];
        }

        return parent::toArray($request);
    }
}
