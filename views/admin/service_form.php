<h1><?= h($pageTitle) ?></h1>

<?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:520px">
    <form method="post" action="/commandes/admin/service-form.php<?= $id ? '?id=' . $id : '' ?>">
        <?= csrf_field() ?>
        <label for="name">Nom de la prestation</label>
        <input type="text" id="name" name="name" required value="<?= h($old['name']) ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"><?= h($old['description']) ?></textarea>

        <label for="price">Prix (FCFA)</label>
        <input type="number" id="price" name="price" step="1" min="0" required value="<?= h((string)$old['price']) ?>">

        <label>Image</label>
        <div class="image-picker">
            <label class="image-option">
                <input type="radio" name="image" value="" <?= empty($old['image']) ? 'checked' : '' ?>>
                <span class="image-option-empty">Aucune</span>
            </label>
            <?php foreach ($availableImages as $file): ?>
                <label class="image-option">
                    <input type="radio" name="image" value="<?= h($file) ?>" <?= $old['image'] === $file ? 'checked' : '' ?>>
                    <img src="/commandes/assets/images/<?= h($file) ?>" alt="<?= h($file) ?>">
                </label>
            <?php endforeach; ?>
        </div>

        <label><input type="checkbox" name="available" style="width:auto;display:inline-block;margin-right:8px" <?= $old['available'] ? 'checked' : '' ?>> Disponible</label>

        <button type="submit">Enregistrer</button>
        <a class="btn btn-secondary" href="/commandes/admin/services.php">Annuler</a>
    </form>
</div>
