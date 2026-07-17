<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['ATK', 'Bakery_Jadi', 'Bakery_Bahan_Baku'];
        $category   = $categories[array_rand($categories)];

        $prefixes = [
            'ATK'               => 'ATK',
            'Bakery_Jadi'       => 'BKJ',
            'Bakery_Bahan_Baku' => 'BBK',
        ];

        $units = [
            'ATK'               => 'pcs',
            'Bakery_Jadi'       => 'pcs',
            'Bakery_Bahan_Baku' => 'kg',
        ];

        return [
            'sku'         => $prefixes[$category] . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
            'name'        => fake()->words(3, true),
            'category'    => $category,
            'unit'        => $units[$category],
            'description' => null,
            'stock'       => rand(0, 100),
            'price'       => rand(1000, 500000),
        ];
    }
}