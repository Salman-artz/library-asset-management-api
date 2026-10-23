<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepository $users
    ) {}

    public function register(array $data): array
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => User::ROLE_MEMBER,
            'password' => $data['password'],
        ]);

        return $this->makeAuthPayload($user, 'member-registration');
    }

    public function login(array $data): array
    {
        $user = $this->users->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        return $this->makeAuthPayload($user, 'login');
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->users->update($user, $data);
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }

    private function makeAuthPayload(User $user, string $tokenName): array
    {
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token_type' => 'Bearer',
            'access_token' => $token,
        ];
    }
}
