<?php

class Institution
{
    public static function all(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM institution ORDER BY name')->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM institution WHERE insId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Institution new self-registrations are attached to, since signup no
     * longer asks for an access code. Falls back to creating one if the
     * table is empty.
     */
    public static function defaultForSignup(PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT * FROM institution WHERE active = 1 ORDER BY insId ASC LIMIT 1");
        $institution = $stmt->fetch();
        if ($institution) {
            return $institution;
        }
        $id = self::create($pdo, 'Institution par défaut');
        return self::find($pdo, $id);
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

    public static function generateCode(): string
    {
        return strtoupper(bin2hex(random_bytes(4)));
    }

    public static function create(PDO $pdo, string $name, ?string $code = null): int
    {
        $stmt = $pdo->prepare('INSERT INTO institution (name, access_code) VALUES (?, ?)');
        $stmt->execute([$name, $code ?? self::generateCode()]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, string $name, bool $active): void
    {
        $stmt = $pdo->prepare('UPDATE institution SET name = ?, active = ? WHERE insId = ?');
        $stmt->execute([$name, $active ? 1 : 0, $id]);
    }

    public static function regenerateCode(PDO $pdo, int $id): string
    {
        $code = self::generateCode();
        $stmt = $pdo->prepare('UPDATE institution SET access_code = ? WHERE insId = ?');
        $stmt->execute([$code, $id]);
        return $code;
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM institution WHERE insId = ?');
        $stmt->execute([$id]);
    }

    public static function memberCount(PDO $pdo, int $id): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE insId = ?');
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }
}
