<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => null,
            'position' => 'Pengurus Vihara',
            'phone' => '0812'.fake()->numerify('########'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Assign an administrator role to the user.
     */
    public function admin(): static
    {
        return $this->state(function (array $attributes): array {
            $role = Role::firstOrCreate(
                ['slug' => 'admin'],
                [
                    'name' => 'Administrator Utama',
                    'description' => 'Akses penuh seluruh sistem manajemen dan landing page vihara.',
                    'permissions' => array_keys(Role::availablePermissions()),
                    'is_system' => true,
                ]
            );

            return [
                'role_id' => $role->id,
                'position' => 'Ketua Sekretariat Vihara',
            ];
        });
    }
}
