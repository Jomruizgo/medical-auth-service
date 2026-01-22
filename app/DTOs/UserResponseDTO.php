<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Entities\User;

class UserResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $role,
        public readonly ?string $createdAt
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            role: $user->getRole(),
            createdAt: $user->getCreatedAt()?->format('Y-m-d\TH:i:s\Z')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'role' => $this->role,
            'created_at' => $this->createdAt
        ];
    }
}
