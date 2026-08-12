<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $query = Fine::query()
            ->with('borrowing.user', 'borrowing.collection');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id) {
            $query->whereHas('borrowing', fn ($q) => $q->where('user_id', $request->user_id));
        }

        $fines = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ApiResponse::success(
            FineResource::collection($fines),
            'Daftar denda'
        );
    }

    public function show(Fine $fine)
    {
        $fine->load('borrowing.user', 'borrowing.collection');

        return ApiResponse::success(
            new FineResource($fine),
            'Detail denda'
        );
    }

    public function pay(Fine $fine)
    {
        if ($fine->status === 'paid') {
            return ApiResponse::error('Denda ini sudah dibayar.', null, 422);
        }

        $fine->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        return ApiResponse::success(
            new FineResource($fine->fresh()->load('borrowing.user', 'borrowing.collection')),
            'Pembayaran denda berhasil dicatat'
        );
    }

    public function mine(Request $request)
    {
        $query = Fine::query()
            ->with('borrowing.user', 'borrowing.collection')
            ->whereHas('borrowing', fn ($q) => $q->where('user_id', $request->user()->id));

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $fines = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ApiResponse::success(
            FineResource::collection($fines),
            'Daftar denda saya'
        );
    }
}

