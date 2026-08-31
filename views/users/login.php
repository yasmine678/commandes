<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand-panel">
            <div>
                <div class="auth-icon-badge">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="6" y="3" width="12" height="18" rx="2"></rect>
                        <path d="M9 3v2a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1V3"></path>
                        <path d="m9 13 2 2 4-4"></path>
                    </svg>
                </div>
                <h2>Ravis de vous revoir</h2>
                <p>Connectez-vous pour consulter le menu de la semaine, passer vos commandes et suivre leur statut.</p>
            </div>
            <div class="auth-foot">DEKA EDITIONS — Outil de commandes en ligne</div>
        </div>
        <div class="auth-form-panel">
            <h1>Connexion</h1>
            <p class="auth-subtitle">Entrez vos identifiants pour accéder à votre espace.</p>

            <?php foreach ($errors as $error): ?>
                <div class="flash flash-error"><?= h($error) ?></div>
            <?php endforeach; ?>

            <form method="post" action="/commandes/login.php">
                <?= csrf_field() ?>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus value="<?= h($_POST['email'] ?? '') ?>">

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Se connecter</button>
            </form>

            <p class="auth-switch muted">Pas encore de compte ? <a href="/commandes/inscription.php">Inscrivez-vous</a>.</p>
        </div>
    </div>
</div>
