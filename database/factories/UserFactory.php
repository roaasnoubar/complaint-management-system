<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
        $email = fake()->unique()->safeEmail();
        return [
            'name' => fake()->name(),
            'email' => $email,
            // توليد يوزر نيم تلقائي كما يفعل الكنترولر الخاص بكِ
            'username' => explode('@', $email)[0] . '_' . rand(100, 999), 
            'phone' => fake()->unique()->numerify('05########'),
            'birthdate' => fake()->date(),
            'password' => static::$password ??= Hash::make('password'),
            'is_verified' => true,
            'role_id' => 3, // القيمة الافتراضية للمواطن
            'authority_id' => 1,
            'remember_token' => Str::random(10),
        ];
    }
    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }
}
