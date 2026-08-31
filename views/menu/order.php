<p class="muted"><a href="/commandes/menu.php">&larr; Retour au menu de la semaine</a></p>
<h1>Commander</h1>

<?php if (!$menu): ?>
    <div class="card">
        <p>Aucun menu n'est publié pour le moment. Revenez dimanche !</p>
    </div>
    <?php return; ?>
<?php endif; ?>

<div class="card">
    <h2><?= h($menu['title']) ?></h2>
    <?php if ($menu['description']): ?><p><?= nl2br(h($menu['description'])) ?></p><?php endif; ?>
    <p class="muted">Semaine du <?= format_date_fr($menu['date_begg']) ?> au <?= format_date_fr($menu['date_end']) ?></p>

    <?php if ($isOpen): ?>
        <div class="open-banner">
            Commandes ouvertes jusqu'au <?= date('d/m/Y à H:i', strtotime($menu['date_endin'])) ?>.
        </div>
    <?php else: ?>
        <div class="closed-banner">
            <?= strtotime($menu['date_open']) > time()
                ? 'Les commandes ouvriront le ' . date('d/m/Y à H:i', strtotime($menu['date_open'])) . '.'
                : 'Les commandes pour ce menu sont closes depuis le ' . date('d/m/Y à H:i', strtotime($menu['date_endin'])) . '.' ?>
        </div>
    <?php endif; ?>
</div>

<?php if (empty($services)): ?>
    <div class="card"><p class="muted">Aucune prestation n'a encore été associée à ce menu.</p></div>
<?php endif; ?>

<?php foreach ($deliveryDays as $day):
    $existing = $existingOrders[$day] ?? null;
    $qtyByService = [];
    if ($existing) {
        foreach ($existing['lines'] as $line) {
            $qtyByService[$line['serId']] = (int)$line['quantity'];
        }
    }
?>
<div class="card">
    <h2 style="text-transform:capitalize"><?= format_date_fr($day) ?>
        <?php if ($existing): ?>
            <span class="badge badge-<?= h(str_replace(' ', '-', $existing['order']['status'])) ?>"><?= h($existing['order']['status']) ?></span>
        <?php endif; ?>
    </h2>

    <?php if ($existing): ?>
        <p class="muted">
            Commande enregistrée pour <strong><?= h($existing['order']['institution']) ?></strong> : <?= h($existing['order']['description']) ?>
            <?php if ($existing['order']['note']): ?><br>« <?= nl2br(h($existing['order']['note'])) ?> »<?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
    <form method="post" action="/commandes/commander.php">
        <?= csrf_field() ?>
        <input type="hidden" name="dateLivraison" value="<?= h($day) ?>">

        <label for="institution-<?= $day ?>">Institution pour laquelle vous commandez</label>
        <select id="institution-<?= $day ?>" name="institution" required <?= $isOpen ? '' : 'disabled' ?>>
            <option value="">— Choisir une institution —</option>
            <?php foreach ($institutions as $institution): ?>
                <option value="<?= h($institution['name']) ?>" <?= ($existing['order']['institution'] ?? '') === $institution['name'] ? 'selected' : '' ?>><?= h($institution['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="note-<?= $day ?>">Précisez ce dont vous avez besoin</label>
        <textarea id="note-<?= $day ?>" name="note" rows="3" required <?= $isOpen ? '' : 'disabled' ?> placeholder="Ex. Tournage du séminaire annuel, 3 intervenants, montage d'un teaser de 2 minutes..."><?= h($existing['order']['note'] ?? '') ?></textarea>

        <div class="dish-grid">
            <?php foreach ($services as $service): ?>
                <div class="dish-card">
                    <?php if ($service['image']): ?>
                        <img class="dish-image" src="/commandes/assets/images/<?= h($service['image']) ?>" alt="">
                    <?php endif; ?>
                    <h3><?= h($service['name']) ?></h3>
                    <p class="desc"><?= h($service['description']) ?></p>
                    <p class="price"><?= format_price((float)$service['price']) ?></p>
                    <div class="qty">
                        <label for="qty-<?= $day ?>-<?= $service['serId'] ?>" style="margin:0">Quantité</label>
                        <input type="number" min="0" max="20"
                               id="qty-<?= $day ?>-<?= $service['serId'] ?>"
                               name="qty[<?= $service['serId'] ?>]"
                               value="<?= (int)($qtyByService[$service['serId']] ?? 0) ?>"
                               <?= $isOpen ? '' : 'disabled' ?>>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($isOpen): ?>
            <button type="submit"><?= $existing ? 'Mettre à jour ma commande' : 'Commander pour ce jour' ?></button>
        <?php endif; ?>
    </form>
    <?php endif; ?>
</div>
<?php endforeach; ?>
