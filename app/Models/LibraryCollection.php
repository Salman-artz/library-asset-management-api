<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryCollection extends Model
{
    use HasFactory;

    protected $table = 'collections';

    protected $fillable = [
        'code',
        'title',
        'category_id',
        'cover_image_path',
        'total_stock',
        'available_stock',
        'condition',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_stock' => 'integer',
            'available_stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'collection_id');
    }
}