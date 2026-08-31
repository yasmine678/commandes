<div class="hero-banner" style="background-image:linear-gradient(135deg, #a9673a 0%, #7c4a29 100%)">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="eyebrow">DEKA Editions</p>
        <h1>Le menu de la semaine, préparé pour vous</h1>
        <p>Choisissez vos plats et le jour qui vous convient — simple, rapide, sans rendez-vous.</p>
        <p style="margin-top:22px">
            <a class="btn" href="/commandes/menu.php">Commander maintenant</a>
        </p>
    </div>
</div>

<?php if ($menu): ?>
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

        <a class="btn" href="/commandes/menu.php">Voir le menu et commander</a>
    </div>
<?php else: ?>
    <div class="card">
        <p>Aucun menu n'est publié pour le moment. Revenez dimanche !</p>
    </div>
<?php endif; ?>

<script src="/commandes/assets/js/hero-cursor.js" defer></script>
