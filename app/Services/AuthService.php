<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\ValidationException;
use App\Core\Validator;
use App\DTOs\RegisterUserDTO;
use App\DTOs\UserResponseDTO;
use App\Entities\User;
use App\Mappers\UserMapper;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct(?UserRepository $userRepository = null)
    {
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function register(array $data): UserResponseDTO
    {
        $this->validateRegistration($data);

        $dto = RegisterUserDTO::fromArray($data);

        if ($this->userRepository->emailExists($dto->email)) {
            throw new ConflictException('Email already registered');
        }

        $hashedPassword = password_hash($dto->password, PASSWORD_BCRYPT);
        $user = UserMapper::fromRegisterDTO($dto, $hashedPassword);

        $savedUser = $this->userRepository->save($user);

        return UserResponseDTO::fromEntity($savedUser);
    }

    private function validateRegistration(array $data): void
    {
        $validator = new Validator($data);

        $isValid = $validator->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'min:8', 'max:255'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100']
        ]);

        if (!$isValid) {
            throw new ValidationException($validator->getErrors());
        }
    }
}
