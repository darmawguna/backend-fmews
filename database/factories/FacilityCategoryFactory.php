<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FacilityCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FacilityCategory>
 */
class FacilityCategoryFactory extends Factory
{
    protected $model = FacilityCategory::class;

    public function definition(): array
    {
        return [
            'nama_kategori' => $this->faker->word(), // Misal: "Kesehatan", "Pendidikan"
            'deskripsi' => $this->faker->sentence(), // Opsional jika ada kolom ini
        ];
    }
}
