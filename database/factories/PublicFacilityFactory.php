<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\FacilityCategoryFactory;
use App\Models\PublicFacility;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PublicFacility>
 */
class PublicFacilityFactory extends Factory
{
    protected $model = PublicFacility::class;

    public function definition(): array
    {
        return [
            'id_kategori' => FacilityCategoryFactory::factory(),
            'nama_fasilitas' => $this->faker->company,
            'alamat' => $this->faker->address,
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'deskripsi' => $this->faker->sentence,
            'status_operasional' => $this->faker->randomElement(['Beroperasi', 'Tidak Beroperasi']),
        ];
    }

}
