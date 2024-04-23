<?php

namespace Database\Factories;

use App\Infrastructure\Eloquent\Models\Attendee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendee>
 */
class AttendeeFactory extends Factory
{
    protected $model = Attendee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->randomDigit(),
            'event_id' => fake()->randomDigit(),
            'status' => fake()->numberBetween(0, 2)
        ];
    }
}
