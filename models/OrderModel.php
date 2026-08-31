<?php

class OrderModel
{
    public static function findForUserAndDay(PDO $pdo, int $usId, string $dateLivraison): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM orders WHERE usId = ? AND dateLivraison = ? AND status != 'annulée'"
        );
        $stmt->execute([$usId, $dateLivraison]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new order or replace the lines of an existing (non-cancelled) order
     * for the same user + delivery day.
     *
     * @param array $items [serId => quantity]
     */
    public static function placeOrder(PDO $pdo, int $usId, string $dateLivraison, array $items, string $institution, string $note): int
    {
        $items = array_filter($items, fn($qty) => (int)$qty > 0);
        if (empty($items)) {
            throw new InvalidArgumentException('Aucune prestation sélectionnée.');
        }

        $serviceIds = array_map('intval', array_keys($items));
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $stmt = $pdo->prepare("SELECT serId, name FROM service WHERE serId IN ($placeholders)");
        $stmt->execute($serviceIds);
        $names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $descriptionParts = [];
        foreach ($items as $serId => $qty) {
            $descriptionParts[] = ($names[$serId] ?? 'Prestation') . ' x' . (int)$qty;
        }
        $description = implode(', ', $descriptionParts);

        $pdo->beginTransaction();
        try {
            $existing = self::findForUserAndDay($pdo, $usId, $dateLivraison);

            if ($existing) {
                $ordId = (int)$existing['ordId'];
                $upd = $pdo->prepare('UPDATE orders SET description = ?, institution = ?, note = ?, status = ? WHERE ordId = ?');
                $upd->execute([$description, $institution, $note, 'en attente', $ordId]);
                $del = $pdo->prepare('DELETE FROM oderline WHERE ordId = ?');
                $del->execute([$ordId]);
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO orders (dateLivraison, status, usId, institution, description, note) VALUES (?, ?, ?, ?, ?, ?)'
                );
                $ins->execute([$dateLivraison, 'en attente', $usId, $institution, $description, $note]);
                $ordId = (int)$pdo->lastInsertId();
            }

            $insLine = $pdo->prepare('INSERT INTO oderline (quantity, ordId, serId) VALUES (?, ?, ?)');
            foreach ($items as $serId => $qty) {
                $insLine->execute([(int)$qty, $ordId, (int)$serId]);
            }

            $pdo->commit();
            return $ordId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function linesForOrder(PDO $pdo, int $ordId): array
    {
        $stmt = $pdo->prepare(
            'SELECT ol.*, s.name, s.price FROM oderline ol
             INNER JOIN service s ON s.serId = ol.serId
             WHERE ol.ordId = ?'
        );
        $stmt->execute([$ordId]);
        return $stmt->fetchAll();
    }

    public static function listForUser(PDO $pdo, int $usId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE usId = ? ORDER BY dateLivraison DESC');
        $stmt->execute([$usId]);
        return $stmt->fetchAll();
    }

    public static function listAll(PDO $pdo, ?string $day = null): array
    {
        $sql = 'SELECT o.*, u.firstName, u.lastName
                FROM orders o
                INNER JOIN users u ON u.usId = o.usId';
        $params = [];
        if ($day) {
            $sql .= ' WHERE o.dateLivraison = ?';
            $params[] = $day;
        }
        $sql .= ' ORDER BY o.dateLivraison DESC, u.lastName';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(PDO $pdo, int $ordId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE ordId = ?');
        $stmt->execute([$ordId]);
        return $stmt->fetch() ?: null;
    }

    public static function updateStatus(PDO $pdo, int $ordId, string $status): void
    {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE ordId = ?');
        $stmt->execute([$status, $ordId]);
    }

    public static function cancel(PDO $pdo, int $ordId, int $usId): void
    {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'annulée' WHERE ordId = ? AND usId = ?");
        $stmt->execute([$ordId, $usId]);
    }
}
