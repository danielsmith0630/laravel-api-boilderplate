<?php

namespace Database\Factories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Space::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $privacyArray = Space::PRIVACY_TYPES;
        $rand_key = array_rand($privacyArray);
        return [
            'name' => $this->faker->company,
            'bio' => $this->faker->text,
            'website' => $this->faker->url,
            'phone_number' => $this->faker->phoneNumber,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'address' => $this->faker->address,
            'privacy' => $privacyArray[$rand_key],
        ];
    }
}
