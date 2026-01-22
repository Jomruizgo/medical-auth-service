<?php

declare(strict_types=1);

namespace App\Mappers;

use App\DTOs\RegisterUserDTO;
use App\Entities\User;
use DateTimeImmutable;

class UserMapper
{
    public static function fromRegisterDTO(RegisterUserDTO $dto, string $hashedPassword): User
    {
        return new User(
            id: null,
            email: $dto->email,
            password: $hashedPassword,
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            role: $dto->role
        );
    }

    public static function fromDatabaseRow(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            email: $row['email'],
            password: $row['password'],
            firstName: $row['first_name'],
            lastName: $row['last_name'],
            role: $row['role'],
            createdAt: isset($row['created_at']) ? new DateTimeImmutable($row['created_at']) : null,
            updatedAt: isset($row['updated_at']) ? new DateTimeImmutable($row['updated_at']) : null,
            lastLogin: isset($row['last_login']) ? new DateTimeImmutable($row['last_login']) : null
        );
    }
}
