<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleTransactionFactory extends Factory
{
    public function definition(): array
    {
        $total  = $this->faker->numberBetween(10000, 500000);
        // Bayar dibulatkan ke kelipatan 5000 terdekat di atas total
        $paid   = (int) (ceil($total / 5000) * 5000);
        $change = $paid - $total;

        return [
            'user_id'        => User::factory(),
            'total_amount'   => $total,
            'paid_amount'    => $paid,
            'change_amount'  => $change,
            'payment_method' => ['cash', 'qris', 'transfer'][array_rand(['cash', 'qris', 'transfer'])],
            'date'           => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'notes'          => $this->faker->optional()->sentence(),
        ];
    }

    public function cash(): static
    {
        return $this->state(['payment_method' => 'cash']);
    }

    public function qris(): static
    {
        return $this->state([
            'payment_method' => 'qris',
            'change_amount'  => 0, // QRIS biasanya pas
        ]);
    }

    public function transfer(): static
    {
        return $this->state([
            'payment_method' => 'transfer',
            'change_amount'  => 0,
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }

    public function today(): static
    {
        return $this->state(['date' => now()->toDateString()]);
    }

    public function withTotal(int $total, int $paid = null): static
    {
        $paid   = $paid ?? $total;
        $change = max(0, $paid - $total);

        return $this->state([
            'total_amount'  => $total,
            'paid_amount'   => $paid,
            'change_amount' => $change,
        ]);
    }
}