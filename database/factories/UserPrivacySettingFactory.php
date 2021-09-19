<?php

namespace Database\Factories;

use App\Models\UserPrivacySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPrivacySettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPrivacySetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'location' => 0,
            'phone_number' => 0,
            'last_name' => 0,
            'is_public' => 0,
            'public_messages' => 0,
        ];
    }

    /**
     * Indicate that the model's location should be public.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function publicLocation()
    {
        return $this->state(function (array $attributes) {
            return [
                'location' => 1,
            ];
        });
    }

    /**
     * Indicate that the model's phone number should be public.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function publicPhoneNumber()
    {
        return $this->state(function (array $attributes) {
            return [
                'phone_number' => 1,
            ];
        });
    }

    /**
     * Indicate that the model's last name should be public.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function publicLastName()
    {
        return $this->state(function (array $attributes) {
            return [
                'last_name' => 1,
            ];
        });
    }

    /**
     * Indicate that the user is public.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function publicUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_public' => 1,
            ];
        });
    }

    /**
     * Indicate that public messages are allowed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function publicMessages()
    {
        return $this->state(function (array $attributes) {
            return [
                'public_messages' => 1,
            ];
        });
    }
}
