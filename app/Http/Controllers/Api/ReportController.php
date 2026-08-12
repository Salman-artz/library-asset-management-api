<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\UserResource;
use App\Models\Borrowing;
use App\Models\LibraryCollection;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function popularCollections(Request $request)
    {
        $limit = $request->input('limit', 10);

        $collections = LibraryCollection::query()
            ->withCount('borrowings')
            ->with('category')
            ->orderBy('borrowings_count', 'desc')
            ->limit($limit)
            ->get();

        return ApiResponse::success(
            CollectionResource::collection($collections),
            'Koleksi paling sering dipinjam'
        );
    }

    public function activeMembers(Request $request)
    {
        $limit = $request->input('limit', 10);

        $members = User::query()
            ->where('role', User::ROLE_MEMBER)
            ->withCount('borrowings')
            ->orderBy('borrowings_count', 'desc')
            ->limit($limit)
            ->get();

        return ApiResponse::success(
            UserResource::collection($members),
            'Member paling aktif meminjam'
        );
    }

    public function export(Request $request)
    {
        $query = Borrowing::query()
            ->with(['user', 'collection.category', 'fine']);

        if ($request->start_date) {
            $query->whereDate('borrowed_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('borrowed_at', '<=', $request->end_date);
        }

        $borrowings = $query->orderBy('borrowed_at', 'desc')->get();

        $data = $borrowings->map(fn ($b) => [
            'id' => $b->id,
            'member' => $b->user->name,
            'collection' => $b->collection->title,
            'borrowed_at' => $b->borrowed_at->toDateTimeString(),
            'due_date' => $b->due_date,
            'returned_at' => $b->returned_at?->toDateTimeString(),
            'status' => $b->status,
            'fine_amount' => $b->fine?->amount ?? 0,
            'fine_status' => $b->fine?->status ?? '-',
        ]);

        if ($request->format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.borrowings', ['data' => $data]);
            return $pdf->download('laporan-peminjaman.pdf');
        }

        return ApiResponse::success([
            'total_records' => $data->count(),
            'records' => $data,
        ], 'Laporan peminjaman');
    }
}

