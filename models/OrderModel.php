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
     * for the same user + delivery day. One serving per dish (quantity is
     * always 1 - a collaborator picks a dish or doesn't, never "x2").
     *
     * @param int[] $serviceIds selected dish IDs
     */
    public static function placeOrder(PDO $pdo, int $usId, string $dateLivraison, array $serviceIds, string $note): int
    {
        $serviceIds = array_values(array_unique(array_map('intval', $serviceIds)));
        if (empty($serviceIds)) {
            throw new InvalidArgumentException('Aucun plat sélectionné.');
        }

        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $stmt = $pdo->prepare("SELECT serId, name FROM service WHERE serId IN ($placeholders)");
        $stmt->execute($serviceIds);
        $names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $descriptionParts = [];
        foreach ($serviceIds as $serId) {
            $descriptionParts[] = $names[$serId] ?? 'Plat';
        }
        $description = implode(', ', $descriptionParts);

        $pdo->beginTransaction();
        try {
            $existing = self::findForUserAndDay($pdo, $usId, $dateLivraison);

            if ($existing) {
                $ordId = (int)$existing['ordId'];
                $upd = $pdo->prepare('UPDATE orders SET description = ?, note = ?, status = ? WHERE ordId = ?');
                $upd->execute([$description, $note, 'en attente', $ordId]);
                $del = $pdo->prepare('DELETE FROM oderline WHERE ordId = ?');
                $del->execute([$ordId]);
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO orders (dateLivraison, status, usId, description, note) VALUES (?, ?, ?, ?, ?)'
                );
                $ins->execute([$dateLivraison, 'en attente', $usId, $description, $note]);
                $ordId = (int)$pdo->lastInsertId();
            }

            $insLine = $pdo->prepare('INSERT INTO oderline (quantity, ordId, serId) VALUES (1, ?, ?)');
            foreach ($serviceIds as $serId) {
                $insLine->execute([$ordId, $serId]);
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

    /**
     * Number of orders placed per ISO week, for the last $weeks weeks
     * (oldest first). Every week in the range is present even with 0 orders.
     *
     * @return array<int, array{label:string, value:int}>
     */
    public static function weeklyCounts(PDO $pdo, int $weeks = 8): array
    {
        $stmt = $pdo->prepare(
            "SELECT YEARWEEK(dateOrder, 3) AS yw, MIN(DATE(dateOrder)) AS week_start, COUNT(*) AS c
             FROM orders
             WHERE dateOrder >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
             GROUP BY yw
             ORDER BY yw"
        );
        $stmt->execute([$weeks]);
        $rows = $stmt->fetchAll();

        $byWeek = [];
        foreach ($rows as $row) {
            $byWeek[(int)$row['yw']] = ['week_start' => $row['week_start'], 'c' => (int)$row['c']];
        }

        // Fill in every week of the range so a quiet week shows as 0, not a gap.
        $result = [];
        $cursor = new DateTime('monday this week');
        $cursor->modify('-' . ($weeks - 1) . ' weeks');
        for ($i = 0; $i < $weeks; $i++) {
            $yw = (int)$cursor->format('oW');
            $result[] = [
                'label' => $cursor->format('d/m'),
                'value' => $byWeek[$yw]['c'] ?? 0,
            ];
            $cursor->modify('+1 week');
        }
        return $result;
    }
}
