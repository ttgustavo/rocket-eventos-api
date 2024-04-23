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
            'user_id' => fake()->numberBetween(100000, 999999),
            'event_id' => fake()->numberBetween(100000, 999999),
            'status' => fake()->numberBetween(100000, 999999)
        ];
    }
}
