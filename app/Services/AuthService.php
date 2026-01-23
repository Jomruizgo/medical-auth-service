<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\UnauthorizedException;
use App\Core\Exceptions\ValidationException;
use App\Core\Validator;
use App\DTOs\LoginDTO;
use App\DTOs\LoginResponseDTO;
use App\DTOs\RefreshTokenDTO;
use App\DTOs\RegisterUserDTO;
use App\DTOs\TokenResponseDTO;
use App\DTOs\UserResponseDTO;
use App\Entities\User;
use App\Mappers\UserMapper;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $userRepository;
    private JwtService $jwtService;

    public function __construct(
        ?UserRepository $userRepository = null,
        ?JwtService $jwtService = null
    ) {
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->jwtService = $jwtService ?? new JwtService();
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

    public function login(array $data): LoginResponseDTO
    {
        $this->validateLogin($data);

        $dto = LoginDTO::fromArray($data);

        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !password_verify($dto->password, $user->getPassword())) {
            throw new UnauthorizedException('Invalid credentials');
        }

        $this->userRepository->updateLastLogin($user->getId());

        $tokens = $this->jwtService->generateTokenPair($user);

        return LoginResponseDTO::fromTokensAndUser($tokens, $user);
    }

    private function validateLogin(array $data): void
    {
        $validator = new Validator($data);

        $isValid = $validator->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (!$isValid) {
            throw new ValidationException($validator->getErrors());
        }
    }

    public function refresh(array $data): TokenResponseDTO
    {
        $this->validateRefresh($data);

        $dto = RefreshTokenDTO::fromArray($data);

        $userId = $this->jwtService->validateRefreshToken($dto->refreshToken);

        if (!$userId) {
            throw new UnauthorizedException('Invalid or expired refresh token');
        }

        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new UnauthorizedException('User not found');
        }

        $tokens = $this->jwtService->generateTokenPair($user);

        return TokenResponseDTO::fromTokens($tokens);
    }

    private function validateRefresh(array $data): void
    {
        $validator = new Validator($data);

        $isValid = $validator->validate([
            'refresh_token' => ['required']
        ]);

        if (!$isValid) {
            throw new ValidationException($validator->getErrors());
        }
    }
}
