<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'permissions' => ['manage_receipts', 'manage_documents'],
            'is_system' => false,
        ];
    }

    /**
     * State for a full administrator role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Administrator Utama',
            'slug' => 'admin',
            'permissions' => array_keys(Role::availablePermissions()),
            'is_system' => true,
        ]);
    }
}
