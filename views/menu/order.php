<h1>Menu de la semaine</h1>

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
    <div class="card"><p class="muted">Aucun plat n'a encore été associé à ce menu.</p></div>
<?php endif; ?>

<?php foreach ($deliveryDays as $day):
    $existing = $existingOrders[$day] ?? null;
    $selectedServices = [];
    if ($existing) {
        foreach ($existing['lines'] as $line) {
            $selectedServices[] = (int)$line['serId'];
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
            Commande enregistrée : <?= h($existing['order']['description']) ?>
            <?php if ($existing['order']['note']): ?><br>« <?= nl2br(h($existing['order']['note'])) ?> »<?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
    <form method="post" action="/commandes/menu.php">
        <?= csrf_field() ?>
        <input type="hidden" name="dateLivraison" value="<?= h($day) ?>">

        <label for="note-<?= $day ?>">Précisez ce dont vous avez besoin</label>
        <textarea id="note-<?= $day ?>" name="note" rows="3" required <?= $isOpen ? '' : 'disabled' ?> placeholder="Ex. Tournage du séminaire annuel, 3 intervenants, montage d'un teaser de 2 minutes..."><?= h($existing['order']['note'] ?? '') ?></textarea>

        <div class="dish-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php foreach ($services as $service): ?>
                <div class="col">
                    <div class="dish-card h-100">
                        <?php if ($service['image']): ?>
                            <img class="dish-image" src="/commandes/assets/images/<?= h($service['image']) ?>" alt="">
                        <?php endif; ?>
                        <h3><?= h($service['name']) ?></h3>
                        <p class="desc"><?= h($service['description']) ?></p>
                        <p class="price"><?= format_price((float)$service['price']) ?></p>
                        <div class="qty">
                            <label for="dish-<?= $day ?>-<?= $service['serId'] ?>">
                                <input type="checkbox"
                                       id="dish-<?= $day ?>-<?= $service['serId'] ?>"
                                       name="dishes[]"
                                       value="<?= $service['serId'] ?>"
                                       <?= in_array((int)$service['serId'], $selectedServices, true) ? 'checked' : '' ?>
                                       <?= $isOpen ? '' : 'disabled' ?>>
                                Je choisis ce plat
                            </label>
                        </div>
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
