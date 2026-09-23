<?php

namespace Database\Factories;

use App\Enums\RoleUtilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 *
 * @method \App\Models\User create($attributes = [], ?\Illuminate\Database\Eloquent\Model $parent = null)
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Comme un vrai compte : un role et un compte actif
            'role' => RoleUtilisateur::Agent,
            'actif' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Utilisateur de test avec un role donne : User::factory()->role(RoleUtilisateur::Directeur) */
    public function role(RoleUtilisateur $role): static
    {
        return $this->state(fn (array $attributes) => ['role' => $role]);
    }
}
