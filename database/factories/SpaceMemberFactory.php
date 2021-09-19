<?php

namespace Database\Factories;

use App\Models\SpaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpaceMember::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'phone_number' => $this->faker->phoneNumber,
            'space_visibility' => $this->faker->randomElement([true, false]),
        ];
    }
}
