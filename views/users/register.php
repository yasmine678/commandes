<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand-panel">
            <div>
                <div class="auth-icon-badge">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M19 8v6"></path>
                        <path d="M22 11h-6"></path>
                    </svg>
                </div>
                <h2>Rejoignez l'équipe</h2>
                <p>Créez votre compte pour accéder au menu de la semaine et commander vos prestations en quelques clics.</p>
            </div>
            <div class="auth-foot">DEKA EDITIONS — Outil de commandes en ligne</div>
        </div>
        <div class="auth-form-panel">
            <h1>Inscription</h1>
            <p class="auth-subtitle">Quelques informations pour créer votre compte.</p>

            <?php foreach ($errors as $error): ?>
                <div class="flash flash-error"><?= h($error) ?></div>
            <?php endforeach; ?>

            <form method="post" action="/commandes/inscription.php">
                <?= csrf_field() ?>
                <div class="field-row">
                    <div>
                        <label for="firstName">Prénom</label>
                        <input type="text" id="firstName" name="firstName" value="<?= h($old['firstName']) ?>">
                    </div>
                    <div>
                        <label for="lastName">Nom</label>
                        <input type="text" id="lastName" name="lastName" required value="<?= h($old['lastName']) ?>">
                    </div>
                </div>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= h($old['email']) ?>">

                <div class="field-row">
                    <div>
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>
                    <div>
                        <label for="password_confirm">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                    </div>
                </div>

                <button type="submit">Créer mon compte</button>
            </form>

            <p class="auth-switch muted">Déjà inscrit ? <a href="/commandes/login.php">Connectez-vous</a>.</p>
        </div>
    </div>
</div>
