<h1>Institutions</h1>
<p><a class="btn" href="/commandes/admin/institution-form.php">+ Nouvelle institution</a></p>

<div class="card">
<table>
    <thead><tr><th>Nom</th><th>Commandes</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($institutions as $institution): ?>
        <tr>
            <td><?= h($institution['name']) ?></td>
            <td><?= (int)$institution['orderCount'] ?></td>
            <td><span class="badge badge-<?= $institution['active'] ? 'publie' : 'archive' ?>"><?= $institution['active'] ? 'Active' : 'Inactive' ?></span></td>
            <td class="actions">
                <a class="btn btn-small btn-secondary" href="/commandes/admin/institution-form.php?id=<?= $institution['insId'] ?>">Modifier</a>
                <form class="inline" method="post" action="/commandes/admin/institutions.php" onsubmit="return confirm('Supprimer cette institution ?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="insId" value="<?= $institution['insId'] ?>">
                    <button type="submit" class="btn-small btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($institutions)): ?>
        <tr><td colspan="4" class="muted">Aucune institution pour le moment.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
