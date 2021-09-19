<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Channel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $privacyArray = Channel::PRIVACY_TYPES;
        $rand_key = array_rand($privacyArray);
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->text,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'privacy' => $privacyArray[$rand_key],
        ];
    }
}
