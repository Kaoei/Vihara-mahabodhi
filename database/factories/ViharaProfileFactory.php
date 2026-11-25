<?php

namespace Database\Factories;

use App\Models\ViharaProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViharaProfile>
 */
class ViharaProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ViharaProfile::defaultData();
    }
}
