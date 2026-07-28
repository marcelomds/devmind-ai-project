<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\InvalidCredentialsException;
use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private const TOKEN_NAME = 'api-token';

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function register(string $name, string $email, string $password): array
    {
        $user = $this->users->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        return $this->issueToken($user);
    }

    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        return $this->issueToken($user);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    private function issueToken(User $user): array
    {
        return [
            'user' => $user,
            'token' => $user->createToken(self::TOKEN_NAME)->plainTextToken,
        ];
    }
}
