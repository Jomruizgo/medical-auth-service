<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\UnauthorizedException;
use App\Core\Exceptions\ValidationException;
use App\DTOs\LoginResponseDTO;
use App\DTOs\TokenResponseDTO;
use App\DTOs\UserResponseDTO;
use App\Entities\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\JwtService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepository $mockRepository;
    private JwtService $mockJwtService;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(UserRepository::class);
        $this->mockJwtService = $this->createMock(JwtService::class);
        $this->authService = new AuthService($this->mockRepository, $this->mockJwtService);
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

    // Login tests

    public function test_login_throws_validation_exception_when_email_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->login([
            'password' => 'password123'
        ]);
    }

    public function test_login_throws_validation_exception_when_password_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->login([
            'email' => 'test@example.com'
        ]);
    }

    public function test_login_throws_unauthorized_when_user_not_found(): void
    {
        $this->mockRepository
            ->method('findByEmail')
            ->with('nonexistent@example.com')
            ->willReturn(null);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login([
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);
    }

    public function test_login_throws_unauthorized_when_password_is_incorrect(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            password: password_hash('correctpassword', PASSWORD_BCRYPT),
            firstName: 'John',
            lastName: 'Doe',
            role: 'patient'
        );

        $this->mockRepository
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($user);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }

    public function test_login_returns_tokens_on_success(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            password: password_hash('password123', PASSWORD_BCRYPT),
            firstName: 'John',
            lastName: 'Doe',
            role: 'patient',
            createdAt: new DateTimeImmutable()
        );

        $this->mockRepository
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($user);

        $this->mockRepository
            ->expects($this->once())
            ->method('updateLastLogin')
            ->with(1);

        $this->mockJwtService
            ->method('generateTokenPair')
            ->with($user)
            ->willReturn([
                'access_token' => 'access_token_value',
                'refresh_token' => 'refresh_token_value',
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]);

        $result = $this->authService->login([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertInstanceOf(LoginResponseDTO::class, $result);
        $this->assertEquals('access_token_value', $result->accessToken);
        $this->assertEquals('refresh_token_value', $result->refreshToken);
        $this->assertEquals('Bearer', $result->tokenType);
        $this->assertEquals(3600, $result->expiresIn);
        $this->assertEquals('test@example.com', $result->user->email);
    }

    public function test_login_updates_last_login_timestamp(): void
    {
        $user = new User(
            id: 5,
            email: 'test@example.com',
            password: password_hash('password123', PASSWORD_BCRYPT),
            firstName: 'John',
            lastName: 'Doe',
            role: 'patient'
        );

        $this->mockRepository
            ->method('findByEmail')
            ->willReturn($user);

        $this->mockRepository
            ->expects($this->once())
            ->method('updateLastLogin')
            ->with(5);

        $this->mockJwtService
            ->method('generateTokenPair')
            ->willReturn([
                'access_token' => 'token',
                'refresh_token' => 'refresh',
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]);

        $this->authService->login([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
    }

    // Refresh token tests

    public function test_refresh_throws_validation_exception_when_token_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->refresh([]);
    }

    public function test_refresh_throws_unauthorized_when_token_is_invalid(): void
    {
        $this->mockJwtService
            ->method('validateRefreshToken')
            ->with('invalid_token')
            ->willReturn(null);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid or expired refresh token');

        $this->authService->refresh([
            'refresh_token' => 'invalid_token'
        ]);
    }

    public function test_refresh_throws_unauthorized_when_user_not_found(): void
    {
        $this->mockJwtService
            ->method('validateRefreshToken')
            ->with('valid_token')
            ->willReturn(999);

        $this->mockRepository
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('User not found');

        $this->authService->refresh([
            'refresh_token' => 'valid_token'
        ]);
    }

    public function test_refresh_returns_new_tokens_on_success(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            password: 'hashed',
            firstName: 'John',
            lastName: 'Doe',
            role: 'patient'
        );

        $this->mockJwtService
            ->method('validateRefreshToken')
            ->with('valid_refresh_token')
            ->willReturn(1);

        $this->mockRepository
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $this->mockJwtService
            ->method('generateTokenPair')
            ->with($user)
            ->willReturn([
                'access_token' => 'new_access_token',
                'refresh_token' => 'new_refresh_token',
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]);

        $result = $this->authService->refresh([
            'refresh_token' => 'valid_refresh_token'
        ]);

        $this->assertInstanceOf(TokenResponseDTO::class, $result);
        $this->assertEquals('new_access_token', $result->accessToken);
        $this->assertEquals('new_refresh_token', $result->refreshToken);
        $this->assertEquals('Bearer', $result->tokenType);
        $this->assertEquals(3600, $result->expiresIn);
    }
}
