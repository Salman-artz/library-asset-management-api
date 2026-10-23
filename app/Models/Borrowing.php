<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'collection_id',
        'borrowed_at',
        'due_date',
        'returned_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_date' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(LibraryCollection::class, 'collection_id');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class, 'borrowing_id');
    }
}
