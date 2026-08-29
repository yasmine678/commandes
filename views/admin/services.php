<h1>Prestations</h1>
<p><a class="btn" href="/commandes/admin/service-form.php">+ Nouvelle prestation</a></p>

<div class="card">
<table>
    <thead><tr><th>Nom</th><th>Description</th><th>Prix</th><th>Disponible</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($services as $service): ?>
        <tr>
            <td><?= h($service['name']) ?></td>
            <td class="muted"><?= h($service['description']) ?></td>
            <td><?= format_price((float)$service['price']) ?></td>
            <td><?= $service['available'] ? 'Oui' : 'Non' ?></td>
            <td class="actions">
                <a class="btn btn-small btn-secondary" href="/commandes/admin/service-form.php?id=<?= $service['serId'] ?>">Modifier</a>
                <form class="inline" method="post" action="/commandes/admin/services.php" onsubmit="return confirm('Supprimer cette prestation ?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="serId" value="<?= $service['serId'] ?>">
                    <button type="submit" class="btn-danger btn-small">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($services)): ?>
        <tr><td colspan="5" class="muted">Aucune prestation pour le moment.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
