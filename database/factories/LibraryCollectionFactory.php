<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\LibraryCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LibraryCollection>
 */
class LibraryCollectionFactory extends Factory
{
    protected $model = LibraryCollection::class;

    public function definition(): array
    {
        $totalStock = fake()->numberBetween(3, 15);

        return [
            'code' => 'COL-'.strtoupper(Str::random(8)),
            'title' => fake()->unique()->sentence(3),
            'category_id' => Category::factory(),
            'cover_image_path' => null,
            'total_stock' => $totalStock,
            'available_stock' => $totalStock,
            'condition' => fake()->randomElement(['good', 'fair', 'damaged']),
            'status' => 'available',
        ];
    }
}
