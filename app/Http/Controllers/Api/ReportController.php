<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\UserResource;
use App\Services\ReportService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function popularCollections(Request $request)
    {
        $limit = (int) $request->input('limit', 10);
        $collections = $this->reportService->getPopularCollections($limit);

        return ApiResponse::success(
            CollectionResource::collection($collections),
            'Koleksi paling sering dipinjam'
        );
    }

    public function activeMembers(Request $request)
    {
        $limit = (int) $request->input('limit', 10);
        $members = $this->reportService->getActiveMembers($limit);

        return ApiResponse::success(
            UserResource::collection($members),
            'Member paling aktif meminjam'
        );
    }

    public function export(Request $request)
    {
        $borrowings = $this->reportService->getExportData(
            $request->start_date,
            $request->end_date
        );

        $data = $borrowings->map(fn ($b) => [
            'id' => $b->id,
            'user_name' => $b->user?->name,
            'user_email' => $b->user?->email,
            'collection_code' => $b->collection?->code,
            'collection_title' => $b->collection?->title,
            'category' => $b->collection?->category?->name,
            'borrowed_at' => $b->borrowed_at?->toISOString(),
            'due_date' => $b->due_date?->toISOString(),
            'returned_at' => $b->returned_at?->toISOString(),
            'status' => $b->status,
            'fine_amount' => $b->fine?->amount,
            'fine_status' => $b->fine?->status,
        ]);

        return ApiResponse::success($data, 'Data sirkulasi perpustakaan berhasil diekspor');
    }
}
