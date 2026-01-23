<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Entities\User;

class LoginResponseDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly UserResponseDTO $user
    ) {}

    public static function fromTokensAndUser(array $tokens, User $user): self
    {
        return new self(
            accessToken: $tokens['access_token'],
            refreshToken: $tokens['refresh_token'],
            tokenType: $tokens['token_type'],
            expiresIn: $tokens['expires_in'],
            user: UserResponseDTO::fromEntity($user)
        );
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'user' => $this->user->toArray()
        ];
    }
}
