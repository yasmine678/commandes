<h1>Inscription</h1>

<?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:520px">
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
</div>

<p class="muted">Déjà inscrit ? <a href="/commandes/login.php">Connectez-vous</a>.</p>
