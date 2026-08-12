<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function register(RegisterRequest $request)
    {
        $payload = $this->authService->register($request->validated());

        return ApiResponse::success([
            'user' => new UserResource($payload['user']),
            'token_type' => $payload['token_type'],
            'access_token' => $payload['access_token'],
        ], 'Registrasi berhasil', 201);
    }

    public function login(LoginRequest $request)
    {
        $payload = $this->authService->login($request->validated());

        return ApiResponse::success([
            'user' => new UserResource($payload['user']),
            'token_type' => $payload['token_type'],
            'access_token' => $payload['access_token'],
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Logout berhasil');
    }

    public function me(Request $request)
    {
        return ApiResponse::success([
            'user' => new UserResource($request->user()),
        ], 'Profil pengguna');
    }
}