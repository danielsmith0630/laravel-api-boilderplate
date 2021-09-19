<?php

namespace Database\Factories;

use App\Models\SpaceMemberRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceMemberRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpaceMemberRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role' => 'member',
        ];
    }
    
    /**
     * Indicate that the member is the owner of the space.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function owner()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'owner',
            ];
        });
    }
}
