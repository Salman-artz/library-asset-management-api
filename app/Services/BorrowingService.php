<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\LibraryCollection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BorrowingService
{
    private const MAX_ACTIVE_BORROWINGS = 3;
    private const DEFAULT_LOAN_DAYS = 7;
    private const DAILY_FINE_RATE = 3000;

    public function borrow(int $collectionId, int $userId): Borrowing
    {
        return DB::transaction(function () use ($collectionId, $userId) {
            $collection = LibraryCollection::query()
                ->lockForUpdate()
                ->findOrFail($collectionId);

            $user = User::findOrFail($userId);

            // Validate stock availability
            if ($collection->available_stock < 1) {
                throw new \RuntimeException('Stok koleksi tidak tersedia.');
            }

            // Validate max active borrowings
            $activeBorrowings = Borrowing::query()
                ->where('user_id', $userId)
                ->whereIn('status', ['borrowed', 'overdue'])
                ->count();

            if ($activeBorrowings >= self::MAX_ACTIVE_BORROWINGS) {
                throw new \RuntimeException(
                    'Member sudah mencapai batas maksimal peminjaman (' . self::MAX_ACTIVE_BORROWINGS . ').'
                );
            }

            // Check if member has unpaid fines
            $hasUnpaidFines = Fine::query()
                ->whereHas('borrowing', fn ($q) => $q->where('user_id', $userId))
                ->where('status', 'unpaid')
                ->exists();

            if ($hasUnpaidFines) {
                throw new \RuntimeException('Member memiliki denda yang belum dibayar. Harap lunasi denda terlebih dahulu.');
            }

            $borrowedAt = Carbon::now();
            $dueDate = Carbon::now()->addDays(self::DEFAULT_LOAN_DAYS);

            // Create borrowing record
            $borrowing = Borrowing::create([
                'user_id' => $userId,
                'collection_id' => $collectionId,
                'borrowed_at' => $borrowedAt,
                'due_date' => $dueDate->toDateString(),
                'returned_at' => null,
                'status' => 'borrowed',
            ]);

            // Decrement available stock
            $collection->decrement('available_stock');

            // Update collection status if stock is 0
            if ($collection->fresh()->available_stock <= 0) {
                $collection->update(['status' => 'borrowed']);
            }

            return $borrowing->load(['user', 'collection.category']);
        });
    }

    public function returnCollection(Borrowing $borrowing): Borrowing
    {
        return DB::transaction(function () use ($borrowing) {
            if (! in_array($borrowing->status, ['borrowed', 'overdue'])) {
                throw new \RuntimeException('Peminjaman ini sudah dikembalikan atau dibatalkan.');
            }

            $returnedAt = Carbon::now();
            $dueDate = Carbon::parse($borrowing->due_date);

            // Calculate late days
            $daysLate = (int) max(0, $dueDate->startOfDay()->diffInDays($returnedAt->startOfDay(), false));

            // Update borrowing
            $borrowing->update([
                'returned_at' => $returnedAt,
                'status' => $daysLate > 0 ? 'overdue' : 'returned',
            ]);

            // Increment available stock
            $collection = $borrowing->collection;
            $collection->increment('available_stock');

            // Update collection status back to available
            if ($collection->fresh()->available_stock > 0 && $collection->status === 'borrowed') {
                $collection->update(['status' => 'available']);
            }

            // Create fine if late
            if ($daysLate > 0) {
                Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'amount' => $daysLate * self::DAILY_FINE_RATE,
                    'days_late' => $daysLate,
                    'daily_rate' => self::DAILY_FINE_RATE,
                    'status' => 'unpaid',
                    'paid_at' => null,
                ]);

                $borrowing->status = 'overdue';
            }

            return $borrowing->load(['user', 'collection.category', 'fine']);
        });
    }

    public function getBorrowings(User $user, array $filters = [])
    {
        $query = Borrowing::query()
            ->with(['user', 'collection.category', 'fine']);

        // Member can only see their own borrowings
        if ($user->role === User::ROLE_MEMBER) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id']) && $user->role !== User::ROLE_MEMBER) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }
}

