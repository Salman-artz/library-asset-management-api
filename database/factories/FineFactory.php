<?php

namespace Database\Factories;

use App\Models\Borrowing;
use App\Models\Fine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fine>
 */
class FineFactory extends Factory
{
    protected $model = Fine::class;

    public function definition(): array
    {
        $daysLate = fake()->numberBetween(1, 10);
        $dailyRate = fake()->numberBetween(1000, 5000);

        return [
            'borrowing_id' => Borrowing::factory(),
            'amount' => $daysLate * $dailyRate,
            'days_late' => $daysLate,
            'daily_rate' => $dailyRate,
            'status' => 'unpaid',
            'paid_at' => null,
        ];
    }
}