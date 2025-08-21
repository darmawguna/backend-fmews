<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shelter>
 */
class ShelterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_shelter' => $this->faker->company . ' Shelter',
            'alamat' => $this->faker->address,
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'kapasitas_maksimum' => $kapasitas = $this->faker->numberBetween(50, 500),
            'ketersediaan_saat_ini' => $this->faker->numberBetween(0, $kapasitas),
            'status' => $this->faker->randomElement(['Buka', 'Penuh', 'Tutup']),
        ];
    }
}
