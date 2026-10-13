<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $campus = fake()->randomElement(User::campusOptions());
        $yearLevels = User::yearLevelsForCampus($campus);
        $grade = fake()->randomElement($yearLevels);
        $courses = User::coursesFor($campus, $grade);

        return [
            'fname' => fake()->firstName(),
            'lname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'campus' => $campus,
            'grade' => $grade,
            'course' => fake()->randomElement($courses),
            'department' => fake()->optional()->randomElement(['College of IT', 'College of Business', 'College of Education']),
            'role' => 'student',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
