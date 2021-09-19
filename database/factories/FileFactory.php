<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $extension = $this->faker->fileExtension;
        return [
            'path' => app()->environment() . '/banner/' . $this->faker->uuid . $extension,
            'name' => $this->faker->word,
            'extension' => $extension,
            'size' => $this->faker->randomNumber(5, false),
            'mime' => $this->faker->mimeType,
        ];
    }
}
