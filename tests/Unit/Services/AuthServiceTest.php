<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\ValidationException;
use App\DTOs\UserResponseDTO;
use App\Entities\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepository $mockRepository;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(UserRepository::class);
        $this->authService = new AuthService($this->mockRepository);
    }

    public function test_register_throws_validation_exception_when_email_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->register([
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function test_register_throws_validation_exception_when_email_is_invalid(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->register([
            'email' => 'invalid-email',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function test_register_throws_validation_exception_when_password_is_too_short(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->register([
            'email' => 'test@example.com',
            'password' => '1234567',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function test_register_throws_validation_exception_when_first_name_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->register([
            'email' => 'test@example.com',
            'password' => 'password123',
            'last_name' => 'Doe'
        ]);
    }

    public function test_register_throws_validation_exception_when_last_name_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->register([
            'email' => 'test@example.com',
            'password' => 'password123',
            'first_name' => 'John'
        ]);
    }

    public function test_register_throws_conflict_exception_when_email_already_exists(): void
    {
        $this->mockRepository
            ->method('emailExists')
            ->with('existing@example.com')
            ->willReturn(true);

        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage('Email already registered');

        $this->authService->register([
            'email' => 'existing@example.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function test_register_returns_user_response_dto_on_success(): void
    {
        $savedUser = new User(
            id: 1,
            email: 'test@example.com',
            password: 'hashed_password',
            firstName: 'John',
            lastName: 'Doe',
            role: 'patient',
            createdAt: new DateTimeImmutable('2024-01-15 10:30:00')
        );

        $this->mockRepository
            ->method('emailExists')
            ->willReturn(false);

        $this->mockRepository
            ->method('save')
            ->willReturn($savedUser);

        $result = $this->authService->register([
            'email' => 'test@example.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertInstanceOf(UserResponseDTO::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('test@example.com', $result->email);
        $this->assertEquals('John', $result->firstName);
        $this->assertEquals('Doe', $result->lastName);
        $this->assertEquals('patient', $result->role);
    }

    public function test_register_assigns_patient_role_by_default(): void
    {
        $savedUser = new User(
            id: 1,
            email: 'test@example.com',
            password: 'hashed_password',
            firstName: 'John',
            lastName: 'Doe',
            role: 'patient',
            createdAt: new DateTimeImmutable()
        );

        $this->mockRepository
            ->method('emailExists')
            ->willReturn(false);

        $this->mockRepository
            ->method('save')
            ->willReturn($savedUser);

        $result = $this->authService->register([
            'email' => 'test@example.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertEquals('patient', $result->role);
    }

    public function test_register_hashes_password_before_saving(): void
    {
        $savedUser = new User(
            id: 1,
            email: 'test@example.com',
            password: 'hashed',
            firstName: 'John',
            lastName: 'Doe',
            role: 'patient',
            createdAt: new DateTimeImmutable()
        );

        $this->mockRepository
            ->method('emailExists')
            ->willReturn(false);

        $this->mockRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) {
                // Password should be hashed, not plain text
                return $user->getPassword() !== 'password123'
                    && password_verify('password123', $user->getPassword());
            }))
            ->willReturn($savedUser);

        $this->authService->register([
            'email' => 'test@example.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }
}
