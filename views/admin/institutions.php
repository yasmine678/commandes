<h1>Institutions</h1>
<p><a class="btn" href="/commandes/admin/institution-form.php">+ Nouvelle institution</a></p>

<div class="card">
<table>
    <thead><tr><th>Nom</th><th>Code d'accès</th><th>Membres</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($institutions as $institution): ?>
        <tr>
            <td><?= h($institution['name']) ?></td>
            <td><code><?= h($institution['access_code']) ?></code></td>
            <td><?= (int)$institution['memberCount'] ?></td>
            <td><span class="badge badge-<?= $institution['active'] ? 'publie' : 'archive' ?>"><?= $institution['active'] ? 'Active' : 'Inactive' ?></span></td>
            <td class="actions">
                <a class="btn btn-small btn-secondary" href="/commandes/admin/institution-form.php?id=<?= $institution['insId'] ?>">Modifier</a>
                <form class="inline" method="post" action="/commandes/admin/institutions.php" onsubmit="return confirm('Générer un nouveau code pour cette institution ? L\'ancien code cessera de fonctionner.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="regenerate">
                    <input type="hidden" name="insId" value="<?= $institution['insId'] ?>">
                    <button type="submit" class="btn-small btn-secondary">Régénérer le code</button>
                </form>
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
        <tr><td colspan="5" class="muted">Aucune institution pour le moment.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
