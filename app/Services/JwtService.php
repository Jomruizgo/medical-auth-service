<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entities\User;
use DateTimeImmutable;
use Exception;

class JwtService
{
    private string $secret;
    private string $algorithm;
    private int $accessExpiration;
    private int $refreshExpiration;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? '';
        $this->algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
        $this->accessExpiration = (int) ($_ENV['JWT_ACCESS_EXPIRATION'] ?? 3600);
        $this->refreshExpiration = (int) ($_ENV['JWT_REFRESH_EXPIRATION'] ?? 604800);
    }

    public function generateAccessToken(User $user): string
    {
        $now = new DateTimeImmutable();

        $payload = [
            'iat' => $now->getTimestamp(),
            'exp' => $now->getTimestamp() + $this->accessExpiration,
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'type' => 'access'
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function generateRefreshToken(User $user): string
    {
        $now = new DateTimeImmutable();

        $payload = [
            'iat' => $now->getTimestamp(),
            'exp' => $now->getTimestamp() + $this->refreshExpiration,
            'sub' => $user->getId(),
            'type' => 'refresh'
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function generateTokenPair(User $user): array
    {
        return [
            'access_token' => $this->generateAccessToken($user),
            'refresh_token' => $this->generateRefreshToken($user),
            'token_type' => 'Bearer',
            'expires_in' => $this->accessExpiration
        ];
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAccessExpiration(): int
    {
        return $this->accessExpiration;
    }
}
