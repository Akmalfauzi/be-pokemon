<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorite_pokemons', function (Blueprint $table) {
            $table->id();
            $table->integer('pokemon_id')->unique();
            $table->string('pokemon_name');
            $table->json('pokemon_data')->nullable(); // Store complete pokemon data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_pokemons');
    }
};
