<?php
/**
 * @var string $pageTitle
 * @var string[] $errors
 * @var bool $notInstalled
 * @var array<int, array{name:string, price:int}> $candidates
 * @var string $rawText
 */
?>
<h1><?= h($pageTitle) ?></h1>

<?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= h($error) ?></div>
<?php endforeach; ?>

<?php if ($notInstalled): ?>
    <div class="card">
        <p>L'outil d'analyse d'image (Tesseract OCR) n'est pas installé sur ce serveur.</p>
        <p class="muted">Installez « Tesseract OCR » pour Windows (build UB Mannheim) avec le pack de langue français, à l'emplacement par défaut <code>C:\Program Files\Tesseract-OCR\</code>, puis revenez sur cette page.</p>
    </div>
<?php else: ?>

<div class="card" style="max-width:640px">
    <p class="muted">Importez une photo du menu de la semaine : le texte est extrait automatiquement, puis vous choisissez quels plats créer (nom et prix pré-remplis, modifiables).</p>
    <form method="post" action="/commandes/admin/menu-ocr.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="step" value="extract">
        <label for="photo">Photo du menu</label>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" required>
        <button type="submit">Analyser la photo</button>
    </form>
</div>

<?php if (!empty($candidates)): ?>
<div class="card" style="max-width:640px">
    <h2>Plats détectés</h2>
    <p class="muted">Décochez ou corrigez les lignes mal reconnues avant de créer les plats.</p>
    <form method="post" action="/commandes/admin/menu-ocr.php">
        <?= csrf_field() ?>
        <input type="hidden" name="step" value="create">
        <?php foreach ($candidates as $i => $c): ?>
            <div class="field-row row g-2" style="align-items:center;margin-bottom:10px">
                <div class="col-1">
                    <input type="checkbox" name="include[]" value="<?= $i ?>" checked style="width:auto;margin:0">
                </div>
                <div class="col-7">
                    <input type="text" name="name[<?= $i ?>]" value="<?= h($c['name']) ?>" style="margin:0">
                </div>
                <div class="col-4">
                    <input type="number" name="price[<?= $i ?>]" value="<?= (int)$c['price'] ?>" min="0" step="1" style="margin:0">
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit">Créer les plats sélectionnés</button>
    </form>
</div>
<?php elseif ($rawText !== ''): ?>
<div class="card" style="max-width:640px">
    <p class="muted">Texte brut lu sur la photo :</p>
    <pre style="white-space:pre-wrap"><?= h($rawText) ?></pre>
</div>
<?php endif; ?>

<?php endif; ?>
