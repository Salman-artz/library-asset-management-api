<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\LibraryCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    public function getPopularCollections(int $limit = 10): Collection
    {
        return LibraryCollection::query()
            ->withCount('borrowings')
            ->with('category')
            ->orderBy('borrowings_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getActiveMembers(int $limit = 10): Collection
    {
        return User::query()
            ->where('role', User::ROLE_MEMBER)
            ->withCount('borrowings')
            ->orderBy('borrowings_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getExportData(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Borrowing::query()
            ->with(['user', 'collection.category', 'fine']);

        if ($startDate) {
            $query->whereDate('borrowed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('borrowed_at', '<=', $endDate);
        }

        return $query->orderBy('borrowed_at', 'desc')->get();
    }
}
