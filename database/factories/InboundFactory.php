<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InboundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id'  => Item::factory(),
            'user_id'  => User::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'supplier' => $this->faker->optional(0.7)->company(), // 70% ada supplier, 30% null
            'date'     => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'notes'    => $this->faker->optional()->sentence(),
        ];
    }

    public function today(): static
    {
        return $this->state(['date' => now()->toDateString()]);
    }

    public function yesterday(): static
    {
        return $this->state(['date' => now()->subDay()->toDateString()]);
    }

    public function forItem(Item $item): static
    {
        return $this->state(['item_id' => $item->id]);
    }

    public function byUser(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }
}