<h1>Menus</h1>
<p>
    <a class="btn" href="/commandes/admin/menu-form.php">+ Nouveau menu</a>
    <a class="btn btn-secondary" href="/commandes/admin/menu-ocr.php">Importer depuis une photo</a>
</p>

<div class="card">
<table>
    <thead><tr><th>Titre</th><th>Période</th><th>Commandes</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($menus as $menu): ?>
        <tr>
            <td><?= h($menu['title']) ?></td>
            <td class="muted"><?= format_date_fr($menu['date_begg']) ?> → <?= format_date_fr($menu['date_end']) ?></td>
            <td class="muted"><?= date('d/m H:i', strtotime($menu['date_open'])) ?> → <?= date('d/m H:i', strtotime($menu['date_endin'])) ?></td>
            <td><span class="badge badge-<?= h($menu['statut']) ?>"><?= h($menu['statut']) ?></span></td>
            <td class="actions">
                <a class="btn btn-small btn-secondary" href="/commandes/admin/menu-form.php?id=<?= $menu['meId'] ?>">Modifier</a>
                <form class="inline" method="post" action="/commandes/admin/menus.php" onsubmit="return confirm('Supprimer ce menu et toutes les commandes liées ?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="meId" value="<?= $menu['meId'] ?>">
                    <button type="submit" class="btn-danger btn-small">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($menus)): ?>
        <tr><td colspan="5" class="muted">Aucun menu pour le moment.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
