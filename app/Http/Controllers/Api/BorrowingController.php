<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrowing\StoreBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Models\Borrowing;
use App\Services\BorrowingService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function __construct(
        private readonly BorrowingService $borrowingService
    ) {}

    public function index(Request $request)
    {
        $borrowings = $this->borrowingService->getBorrowings(
            $request->user(),
            $request->only(['status', 'user_id', 'per_page'])
        );

        return ApiResponse::success(
            BorrowingResource::collection($borrowings),
            'Daftar peminjaman'
        );
    }

    public function show(Borrowing $borrowing)
    {
        $user = request()->user();

        // Member can only see their own borrowings
        if ($user->role === 'member' && $borrowing->user_id !== $user->id) {
            return ApiResponse::error('Anda tidak memiliki akses ke peminjaman ini.', null, 403);
        }

        $borrowing->load(['user', 'collection.category', 'fine']);

        return ApiResponse::success(
            new BorrowingResource($borrowing),
            'Detail peminjaman'
        );
    }

    public function store(StoreBorrowingRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // Staff/admin can specify which member, member can only borrow for themselves
        $userId = in_array($user->role, ['admin', 'staff'])
            ? $data['user_id']
            : $user->id;

        try {
            $borrowing = $this->borrowingService->borrow(
                $data['collection_id'],
                $userId
            );

            return ApiResponse::success(
                new BorrowingResource($borrowing),
                'Peminjaman berhasil dicatat',
                201
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }

    public function returnCollection(Borrowing $borrowing)
    {
        try {
            $borrowing = $this->borrowingService->returnCollection($borrowing);

            $message = $borrowing->status === 'overdue'
                ? 'Koleksi berhasil dikembalikan dengan denda keterlambatan.'
                : 'Koleksi berhasil dikembalikan.';

            return ApiResponse::success(
                new BorrowingResource($borrowing),
                $message
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }
}
