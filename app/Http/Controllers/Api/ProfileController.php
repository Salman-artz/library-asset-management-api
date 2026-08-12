<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function update(UpdateProfileRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $user = $request->user();
            if ($user->avatar_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('users/avatars', 'public');
        }

        $user = $this->authService->updateProfile($request->user(), $data);

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Profil berhasil diperbarui');
    }
}