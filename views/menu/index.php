<div class="hero-banner" style="background-image:url('/commandes/assets/images/hero-studio.jpg')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="eyebrow">DEKA Editions</p>
        <h1>Le menu de la semaine, préparé pour vous</h1>
        <p>Choisissez vos prestations et le jour qui vous convient — simple, rapide, sans rendez-vous.</p>
        <p style="margin-top:22px">
            <a class="btn" href="/commandes/commander.php">Commander maintenant</a>
        </p>
    </div>
</div>

<div class="feature-grid">
    <div class="feature-card">
        <img src="/commandes/assets/images/clapperboard.jpg" alt="Captation & tournage">
        <div class="feature-body">
            <h3>Captation & tournage</h3>
            <p>Prises de vues professionnelles, sur site ou en studio.</p>
        </div>
    </div>
    <div class="feature-card">
        <img src="/commandes/assets/images/event-coverage.jpg" alt="Couverture d'événements">
        <div class="feature-body">
            <h3>Couverture d'événements</h3>
            <p>Reportage photo et vidéo de vos temps forts.</p>
        </div>
    </div>
    <div class="feature-card">
        <img src="/commandes/assets/images/editing.jpg" alt="Montage & post-production">
        <div class="feature-body">
            <h3>Montage & post-production</h3>
            <p>Habillage, étalonnage et livraison soignée.</p>
        </div>
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

        <a class="btn" href="/commandes/commander.php">Voir le menu et commander</a>
    </div>
<?php else: ?>
    <div class="card">
        <p>Aucun menu n'est publié pour le moment. Revenez dimanche !</p>
    </div>
<?php endif; ?>
