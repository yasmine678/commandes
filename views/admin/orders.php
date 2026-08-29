<h1>Commandes</h1>

<div class="card">
    <form method="get" action="/commandes/admin/commandes.php" style="display:flex;gap:10px;align-items:end">
        <div style="flex:1">
            <label for="jour">Filtrer par jour de livraison</label>
            <input type="date" id="jour" name="jour" value="<?= h($day ?? '') ?>">
        </div>
        <button type="submit" style="margin-bottom:14px">Filtrer</button>
        <?php if ($day): ?><a class="btn btn-secondary" style="margin-bottom:14px" href="/commandes/admin/commandes.php">Réinitialiser</a><?php endif; ?>
    </form>
</div>

<?php $statuses = ['en attente', 'en cours', 'terminée', 'livrée', 'annulée']; ?>

<?php foreach ($orders as $order): ?>
    <div class="card">
        <h2 style="text-transform:capitalize">
            <?= format_date_fr($order['dateLivraison']) ?> — <?= h($order['firstName'] . ' ' . $order['lastName']) ?>
            <span class="badge badge-<?= h(str_replace(' ', '-', $order['status'])) ?>"><?= h($order['status']) ?></span>
        </h2>
        <p class="muted"><?= h($order['institution']) ?> · commandé le <?= date('d/m/Y H:i', strtotime($order['dateOrder'])) ?></p>
        <table>
            <thead><tr><th>Prestation</th><th>Quantité</th></tr></thead>
            <tbody>
            <?php foreach ($order['lines'] as $line): ?>
                <tr><td><?= h($line['name']) ?></td><td><?= (int)$line['quantity'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <form method="post" action="/commandes/admin/commandes.php<?= $day ? '?jour=' . h($day) : '' ?>" style="display:flex;gap:10px;align-items:end;margin-top:12px">
            <?= csrf_field() ?>
            <input type="hidden" name="ordId" value="<?= $order['ordId'] ?>">
            <div style="flex:1;max-width:220px">
                <label for="status-<?= $order['ordId'] ?>" style="margin:0 0 6px">Statut</label>
                <select id="status-<?= $order['ordId'] ?>" name="status" style="margin:0">
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= h($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-small">Mettre à jour</button>
        </form>
    </div>
<?php endforeach; ?>

<?php if (empty($orders)): ?>
    <div class="card"><p class="muted">Aucune commande.</p></div>
<?php endif; ?>
