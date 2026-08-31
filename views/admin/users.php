<h1>Utilisateurs</h1>

<div class="card">
<table>
    <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= h($u['firstName'] . ' ' . $u['lastName']) ?></td>
            <td class="muted"><?= h($u['email']) ?></td>
            <td><span class="badge badge-<?= $u['status'] === 'administrateur' ? 'publie' : 'brouillon' ?>"><?= h($u['status']) ?></span></td>
            <td class="actions">
                <?php if ((int)$u['usId'] !== $currentId): ?>
                    <form class="inline" method="post" action="/commandes/admin/utilisateurs.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="usId" value="<?= $u['usId'] ?>">
                        <button type="submit" class="btn-small btn-secondary">
                            <?= $u['status'] === 'administrateur' ? 'Rétrograder' : 'Promouvoir admin' ?>
                        </button>
                    </form>
                    <form class="inline" method="post" action="/commandes/admin/utilisateurs.php" onsubmit="return confirm('Supprimer cet utilisateur et toutes ses commandes ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="usId" value="<?= $u['usId'] ?>">
                        <button type="submit" class="btn-small btn-danger">Supprimer</button>
                    </form>
                <?php else: ?>
                    <span class="muted">(vous)</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
