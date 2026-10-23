<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Services\FineService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function __construct(
        private readonly FineService $fineService
    ) {}

    public function index(Request $request)
    {
        $fines = $this->fineService->getPaginated(
            $request->only(['status', 'user_id']),
            (int) ($request->per_page ?? 15)
        );

        return ApiResponse::success(
            FineResource::collection($fines),
            'Daftar denda'
        );
    }

    public function show(Fine $fine)
    {
        $data = $this->fineService->getById($fine->id);

        return ApiResponse::success(
            new FineResource($data),
            'Detail denda'
        );
    }

    public function pay(Fine $fine)
    {
        try {
            $paid = $this->fineService->pay($fine);

            return ApiResponse::success(
                new FineResource($paid),
                'Pembayaran denda berhasil dicatat'
            );
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 422);
        }
    }

    public function mine(Request $request)
    {
        $fines = $this->fineService->getUserFines(
            $request->user(),
            $request->only(['status']),
            (int) ($request->per_page ?? 15)
        );

        return ApiResponse::success(
            FineResource::collection($fines),
            'Denda saya'
        );
    }
}
