<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\User;
use App\Repositories\Contracts\FineRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FineService
{
    public function __construct(
        private readonly FineRepositoryInterface $fineRepository
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->fineRepository->getPaginated($filters, $perPage);
    }

    public function getUserFines(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['user_id'] = $user->id;

        return $this->fineRepository->getPaginated($filters, $perPage);
    }

    public function getById(int $id): Fine
    {
        $fine = $this->fineRepository->findById($id);

        if (! $fine) {
            throw new \RuntimeException('Data denda tidak ditemukan.');
        }

        return $fine;
    }

    public function pay(Fine $fine): Fine
    {
        if ($fine->status === 'paid') {
            throw new \DomainException('Denda ini sudah dibayar.');
        }

        return $this->fineRepository->markAsPaid($fine);
    }
}
