<?php

class User
{
    public static function findByEmail(PDO $pdo, string $email): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE usId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function emailExists(PDO $pdo, string $email): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return (bool)$stmt->fetchColumn();
    }

    public static function countAdmins(PDO $pdo): int
    {
        return (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'administrateur'")->fetchColumn();
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO users (lastName, firstName, email, password, status)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['lastName'],
            $data['firstName'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['status'] ?? 'collaborateur',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function all(PDO $pdo): array
    {
        return $pdo->query(
            'SELECT usId, lastName, firstName, email, status FROM users ORDER BY lastName, firstName'
        )->fetchAll();
    }

    public static function updateStatus(PDO $pdo, int $usId, string $status): void
    {
        $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE usId = ?');
        $stmt->execute([$status, $usId]);
    }

    public static function delete(PDO $pdo, int $usId): void
    {
        $stmt = $pdo->prepare('DELETE FROM users WHERE usId = ?');
        $stmt->execute([$usId]);
    }
}
