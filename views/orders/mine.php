<h1>Mes commandes</h1>

<?php if (empty($orders)): ?>
    <div class="card"><p class="muted">Vous n'avez pas encore passé de commande.</p></div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <div class="card">
            <h2 style="text-transform:capitalize">
                <?= format_date_fr($order['dateLivraison']) ?>
                <span class="badge badge-<?= h(str_replace(' ', '-', $order['status'])) ?>"><?= h($order['status']) ?></span>
            </h2>
            <?php if ($order['note']): ?><p>« <?= nl2br(h($order['note'])) ?> »</p><?php endif; ?>
            <table>
                <thead><tr><th>Plat</th><th>Prix</th></tr></thead>
                <tbody>
                <?php $total = 0; foreach ($order['lines'] as $line): $total += $line['price']; ?>
                    <tr>
                        <td><?= h($line['name']) ?></td>
                        <td><?= format_price((float)$line['price']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total : <?= format_price($total) ?></strong></p>
            <p class="muted">Commandé le <?= date('d/m/Y à H:i', strtotime($order['dateOrder'])) ?></p>

            <?php if ($order['status'] !== 'annulée'): ?>
            <form method="post" action="/commandes/mes-commandes.php" onsubmit="return confirm('Annuler cette commande ?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="ordId" value="<?= (int)$order['ordId'] ?>">
                <button type="submit" class="btn-danger btn-small">Annuler cette commande</button>
            </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
