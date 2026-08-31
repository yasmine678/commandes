<?php
/**
 * Variables injected by render() via extract() — see admin_service_form_controller()
 * in controllers/admin/services.php.
 *
 * @var string $pageTitle
 * @var string[] $errors
 * @var array{name:string,description:string,image:?string,price:string|float,available:int} $old
 * @var int|null $id
 * @var string[] $availableImages
 */
?>
<h1><?= h($pageTitle) ?></h1>

<?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:520px">
    <form method="post" action="/commandes/admin/service-form.php<?= $id ? '?id=' . $id : '' ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label for="name">Nom du plat</label>
        <input type="text" id="name" name="name" required value="<?= h($old['name']) ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"><?= h($old['description']) ?></textarea>

        <label for="price">Prix (FCFA)</label>
        <input type="number" id="price" name="price" step="1" min="0" required value="<?= h((string)$old['price']) ?>">

        <label for="image_upload">Image</label>
        <input type="file" id="image_upload" name="image_upload" accept="image/jpeg,image/png,image/gif,image/webp">
        <p class="muted" style="margin-top:-8px"></p>

        <?php if (!empty($availableImages)): ?>
            <p class="muted" style="margin-bottom:8px">Ou choisir une image déjà utilisée :</p>
            <div class="image-picker row row-cols-3 row-cols-sm-4 g-2">
                <div class="col">
                    <label class="image-option">
                        <input type="radio" name="image" value="" <?= empty($old['image']) ? 'checked' : '' ?>>
                        <span class="image-option-empty">Aucune</span>
                    </label>
                </div>
                <?php foreach ($availableImages as $file): ?>
                    <div class="col">
                        <label class="image-option">
                            <input type="radio" name="image" value="<?= h($file) ?>" <?= $old['image'] === $file ? 'checked' : '' ?>>
                            <img src="/commandes/assets/images/<?= h($file) ?>" alt="<?= h($file) ?>">
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <label><input type="checkbox" name="available" style="width:auto;display:inline-block;margin-right:8px" <?= $old['available'] ? 'checked' : '' ?>> Disponible</label>

        <button type="submit">Enregistrer</button>
        <a class="btn btn-secondary" href="/commandes/admin/services.php">Annuler</a>
    </form>
</div>
