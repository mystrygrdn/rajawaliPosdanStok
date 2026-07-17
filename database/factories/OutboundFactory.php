<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\SaleTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutboundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id'             => Item::factory(),
            'user_id'             => User::factory(),
            'sale_transaction_id' => null,
            'quantity'            => $this->faker->numberBetween(1, 20),
            'customer'            => $this->faker->optional()->name(),
            'source'              => 'manual',
            'date'                => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'notes'               => $this->faker->optional()->sentence(),
        ];
    }

    public function manual(): static
    {
        return $this->state([
            'source'              => 'manual',
            'sale_transaction_id' => null,
        ]);
    }

    public function fromKasir(SaleTransaction $transaction = null): static
    {
        return $this->state([
            'source'              => 'kasir',
            'sale_transaction_id' => $transaction?->id ?? SaleTransaction::factory(),
            'customer'            => 'Pelanggan Toko',
        ]);
    }

    public function forItem(Item $item): static
    {
        return $this->state(['item_id' => $item->id]);
    }

    public function byUser(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }

    public function today(): static
    {
        return $this->state(['date' => now()->toDateString()]);
    }
}