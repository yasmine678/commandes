<?php

class Institution
{
    public static function all(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM institution ORDER BY name')->fetchAll();
    }

    public static function activeList(PDO $pdo): array
    {
        return $pdo->query("SELECT * FROM institution WHERE active = 1 ORDER BY name")->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM institution WHERE insId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function nameExists(PDO $pdo, string $name, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM institution WHERE name = ?';
        $params = [$name];
        if ($excludeId) {
            $sql .= ' AND insId != ?';
            $params[] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    public static function create(PDO $pdo, string $name): int
    {
        $stmt = $pdo->prepare('INSERT INTO institution (name) VALUES (?)');
        $stmt->execute([$name]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, string $name, bool $active): void
    {
        $stmt = $pdo->prepare('UPDATE institution SET name = ?, active = ? WHERE insId = ?');
        $stmt->execute([$name, $active ? 1 : 0, $id]);
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM institution WHERE insId = ?');
        $stmt->execute([$id]);
    }

    public static function orderCount(PDO $pdo, string $name): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE institution = ?');
        $stmt->execute([$name]);
        return (int)$stmt->fetchColumn();
    }
}
