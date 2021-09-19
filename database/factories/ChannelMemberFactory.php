<?php

namespace Database\Factories;

use App\Models\ChannelMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChannelMember::class;

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
     * Indicate that the member is the owner of the channel.
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
