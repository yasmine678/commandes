<?php

class Service
{
    public static function all(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM service ORDER BY name')->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM service WHERE serId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO service (name, description, image, price, available) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['image'] ?: null,
            $data['price'],
            $data['available'] ?? 1,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            'UPDATE service SET name = ?, description = ?, image = ?, price = ?, available = ? WHERE serId = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['image'] ?: null,
            $data['price'],
            $data['available'] ?? 0,
            $id,
        ]);
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM service WHERE serId = ?');
        $stmt->execute([$id]);
    }
}
