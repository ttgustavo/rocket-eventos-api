<?php

namespace Database\Factories;

use App\Infrastructure\Eloquent\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Infrastructure\Eloquent\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'slug' => fake()->slug(3),
            'details' => fake()->text(),
            'subscription_date_start' => now(),
            'subscription_date_end' => now()->addHours(2),
            'presentation_at' => now()->addDays(3),
            'status' => fake()->numberBetween(0, 5)
        ];
    }
}
