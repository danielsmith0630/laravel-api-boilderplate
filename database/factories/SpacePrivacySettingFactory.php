<?php

namespace Database\Factories;

use App\Models\SpacePrivacySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpacePrivacySettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpacePrivacySetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'phone_number' => $this->faker->randomElement([1, 0]),
            'location' => $this->faker->randomElement([1, 0]),
        ];
    }
}
