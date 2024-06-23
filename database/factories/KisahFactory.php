<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kisah>
 */
class KisahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "judul" => $judu = fake()->sentence(),
            "slug" => \Str::slug($judu),
            "kontent" => fake()->paragraph(50),
            "gambar" => fake()->imageUrl(),
        ];
    }
}
