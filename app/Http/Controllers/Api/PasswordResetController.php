<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use App\Support\ApiResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $status = $this->authService->sendPasswordResetLink($request->validated('email'));

        return ApiResponse::success(null, __($status));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = $this->authService->resetPassword($request->validated());

        return ApiResponse::success(null, __($status));
    }
}