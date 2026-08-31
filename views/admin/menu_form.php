<?php
function to_datetime_local(string $value): string
{
    if ($value === '') return '';
    return substr(str_replace(' ', 'T', $value), 0, 16);
}
?>
<h1><?= h($pageTitle) ?></h1>

<?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:680px">
    <form method="post" action="/commandes/admin/menu-form.php<?= $id ? '?id=' . $id : '' ?>">
        <?= csrf_field() ?>
        <label for="title">Titre du menu</label>
        <input type="text" id="title" name="title" required value="<?= h($old['title']) ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="2"><?= h($old['description']) ?></textarea>

        <div class="field-row row g-3">
            <div class="col-md-6">
                <label for="date_begg">Début de la semaine</label>
                <input type="date" id="date_begg" name="date_begg" required value="<?= h($old['date_begg']) ?>">
            </div>
            <div class="col-md-6">
                <label for="date_end">Fin de la semaine</label>
                <input type="date" id="date_end" name="date_end" required value="<?= h($old['date_end']) ?>">
            </div>
        </div>

        <div class="field-row row g-3">
            <div class="col-md-6">
                <label for="date_open">Ouverture des commandes</label>
                <input type="datetime-local" id="date_open" name="date_open" required value="<?= h(to_datetime_local($old['date_open'])) ?>">
            </div>
            <div class="col-md-6">
                <label for="date_endin">Clôture des commandes</label>
                <input type="datetime-local" id="date_endin" name="date_endin" required value="<?= h(to_datetime_local($old['date_endin'])) ?>">
            </div>
        </div>
        <p class="muted" style="margin-top:-8px">Typiquement : ouverture le dimanche à 00h00, clôture le lundi à 22h00. Un menu au statut « Publié » ne devient visible pour les collaborateurs qu'à partir de cette date d'ouverture — vous pouvez donc le préparer et le publier à l'avance, il apparaîtra tout seul le moment venu.</p>

        <label for="statut">Statut</label>
        <select id="statut" name="statut">
            <option value="brouillon" <?= $old['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
            <option value="publie" <?= $old['statut'] === 'publie' ? 'selected' : '' ?>>Publié</option>
            <option value="archive" <?= $old['statut'] === 'archive' ? 'selected' : '' ?>>Archivé</option>
        </select>

        <label>Plats proposés cette semaine</label>
        <div class="dish-grid row row-cols-1 row-cols-sm-2 g-2">
            <?php foreach ($allServices as $service): ?>
                <div class="col">
                    <label style="display:flex;align-items:center;gap:8px;border:1px solid var(--border);border-radius:8px;padding:10px;font-weight:400">
                        <input type="checkbox" name="services[]" value="<?= $service['serId'] ?>" style="width:auto;margin:0"
                            <?= in_array((int)$service['serId'], $selectedServiceIds, true) ? 'checked' : '' ?>>
                        <?= h($service['name']) ?> — <?= format_price((float)$service['price']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($allServices)): ?>
            <p class="muted">Aucun plat n'existe encore. <a href="/commandes/admin/service-form.php">Créez-en un</a> d'abord.</p>
        <?php endif; ?>

        <button type="submit">Enregistrer</button>
        <a class="btn btn-secondary" href="/commandes/admin/menus.php">Annuler</a>
    </form>
</div>
