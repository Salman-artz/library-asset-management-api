<?php

namespace Database\Factories;

use App\Models\Borrowing;
use App\Models\LibraryCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Borrowing>
 */
class BorrowingFactory extends Factory
{
    protected $model = Borrowing::class;

    public function definition(): array
    {
        $borrowedAt = now()->subDays(fake()->numberBetween(1, 10));

        return [
            'user_id' => User::factory()->member(),
            'collection_id' => LibraryCollection::factory(),
            'borrowed_at' => $borrowedAt,
            'due_date' => $borrowedAt->copy()->addDays(7)->toDateString(),
            'returned_at' => null,
            'status' => 'borrowed',
        ];
    }
}
