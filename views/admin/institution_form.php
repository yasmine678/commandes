<h1><?= h($pageTitle) ?></h1>

<?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:480px">
    <form method="post" action="/commandes/admin/institution-form.php<?= $id ? '?id=' . $id : '' ?>">
        <?= csrf_field() ?>
        <label for="name">Nom de l'institution</label>
        <input type="text" id="name" name="name" required value="<?= h($old['name']) ?>">

        <?php if ($id): ?>
            <label><input type="checkbox" name="active" style="width:auto;display:inline-block;margin-right:8px" <?= $old['active'] ? 'checked' : '' ?>> Active (autorise les nouvelles inscriptions)</label>
        <?php else: ?>
            <p class="muted">Un code d'accès unique sera généré automatiquement à la création.</p>
        <?php endif; ?>

        <button type="submit">Enregistrer</button>
        <a class="btn btn-secondary" href="/commandes/admin/institutions.php">Annuler</a>
    </form>
</div>
