<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Entities\User;
use App\Mappers\UserMapper;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return UserMapper::fromDatabaseRow($row);
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return UserMapper::fromDatabaseRow($row);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function save(User $user): User
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password, first_name, last_name, role, created_at)
             VALUES (:email, :password, :first_name, :last_name, :role, NOW())'
        );

        $stmt->execute([
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'role' => $user->getRole()
        ]);

        $user->setId((int) $this->db->lastInsertId());

        return $this->findById($user->getId());
    }

    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }
}
