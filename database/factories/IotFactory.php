<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\IotModel;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IotModel>
 */
class IotFactory extends Factory
{
    // protected $model = IotModel::class;

    public function definition()
    {
        return [
            'device_id' => Str::uuid(),
            'device_name' => $this->faker->word,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'location' => $this->faker->city,
            'warning_level' => 50,
            'danger_level' => 100,
            'sensor_height' => 200,
            'status' => 'active',
        ];
    }
}
