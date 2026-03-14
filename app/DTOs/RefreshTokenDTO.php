<?php

declare(strict_types=1);

namespace App\DTOs;

class RefreshTokenDTO
{
    public function __construct(
        public readonly string $refreshToken
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            refreshToken: $data['refresh_token'] ?? ''
        );
    }
}
