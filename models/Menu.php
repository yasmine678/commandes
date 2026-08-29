<?php

class Menu
{
    public static function all(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM menu ORDER BY date_begg DESC')->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM menu WHERE meId = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * The most recent published menu (shown to collaborators, whether or not the order window is still open).
     */
    public static function latestPublished(PDO $pdo): ?array
    {
        $stmt = $pdo->query(
            "SELECT * FROM menu WHERE statut = 'publie' ORDER BY date_begg DESC LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    public static function isOrderWindowOpen(array $menu): bool
    {
        $now = date('Y-m-d H:i:s');
        return $menu['statut'] === 'publie' && $now >= $menu['date_open'] && $now <= $menu['date_endin'];
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO menu (title, description, date_begg, date_end, date_open, date_endin, statut)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['date_begg'],
            $data['date_end'],
            $data['date_open'],
            $data['date_endin'],
            $data['statut'] ?? 'brouillon',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            'UPDATE menu SET title = ?, description = ?, date_begg = ?, date_end = ?, date_open = ?, date_endin = ?, statut = ?
             WHERE meId = ?'
        );
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['date_begg'],
            $data['date_end'],
            $data['date_open'],
            $data['date_endin'],
            $data['statut'],
            $id,
        ]);
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM menu WHERE meId = ?');
        $stmt->execute([$id]);
    }

    public static function servicesForMenu(PDO $pdo, int $meId): array
    {
        $stmt = $pdo->prepare(
            'SELECT s.* FROM service s
             INNER JOIN menu_service ms ON ms.serId = s.serId
             WHERE ms.meId = ?
             ORDER BY s.name'
        );
        $stmt->execute([$meId]);
        return $stmt->fetchAll();
    }

    public static function serviceIdsForMenu(PDO $pdo, int $meId): array
    {
        $stmt = $pdo->prepare('SELECT serId FROM menu_service WHERE meId = ?');
        $stmt->execute([$meId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Replace the set of services attached to a menu.
     * @param int[] $serviceIds
     */
    public static function setServices(PDO $pdo, int $meId, array $serviceIds): void
    {
        $pdo->beginTransaction();
        try {
            $del = $pdo->prepare('DELETE FROM menu_service WHERE meId = ?');
            $del->execute([$meId]);

            if (!empty($serviceIds)) {
                $ins = $pdo->prepare('INSERT INTO menu_service (meId, serId) VALUES (?, ?)');
                foreach ($serviceIds as $serId) {
                    $ins->execute([$meId, (int)$serId]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Working days (Mon-Fri) between date_begg and date_end inclusive.
     */
    public static function deliveryDays(array $menu): array
    {
        $days = [];
        $current = new DateTime($menu['date_begg']);
        $end = new DateTime($menu['date_end']);
        while ($current <= $end) {
            if ((int)$current->format('N') <= 5) {
                $days[] = $current->format('Y-m-d');
            }
            $current->modify('+1 day');
        }
        return $days;
    }
}
