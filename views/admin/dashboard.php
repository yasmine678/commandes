<h1>Administration</h1>

<div class="stat-grid row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3">
    <div class="col">
        <div class="stat-card h-100">
            <div class="stat-card-head">
                <div class="stat-icon tint-blue"><?= icon('users') ?></div>
                <span class="stat-title">Collaborateurs</span>
            </div>
            <p class="stat-value"><?= $nbUsers ?></p>
            <p class="stat-sub">comptes actifs</p>
        </div>
    </div>

    <div class="col">
        <div class="stat-card h-100">
            <div class="stat-card-head">
                <div class="stat-icon tint-amber"><?= icon('clipboard') ?></div>
                <span class="stat-title">Commandes</span>
            </div>
            <div class="stat-rows">
                <div class="stat-row"><span class="label">En attente</span><span class="value"><?= $orderCounts['en attente'] ?></span></div>
                <div class="stat-row"><span class="label">En cours</span><span class="value"><?= $orderCounts['en cours'] ?></span></div>
                <div class="stat-row"><span class="label">Terminées / livrées</span><span class="value"><?= $orderCounts['terminée'] + $orderCounts['livrée'] ?></span></div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="stat-card h-100">
            <div class="stat-card-head">
                <div class="stat-icon tint-green"><?= icon('calendar') ?></div>
                <span class="stat-title">Menu actif</span>
            </div>
            <p class="stat-value" style="font-size:1.15rem"><?= $activeMenu ? h($activeMenu['title']) : '—' ?></p>
            <?php if ($activeMenu): ?>
                <p class="stat-sub"><?= format_date_fr($activeMenu['date_begg']) ?> → <?= format_date_fr($activeMenu['date_end']) ?></p>
            <?php else: ?>
                <p class="stat-sub">Aucun menu publié</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="stat-card h-100">
            <div class="stat-card-head">
                <div class="stat-icon tint-blue"><?= icon('clipboard') ?></div>
                <span class="stat-title">Commandes par semaine</span>
            </div>
            <?= line_chart_svg($weeklyCounts) ?>
        </div>
    </div>
</div>
