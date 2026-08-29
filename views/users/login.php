<h1>Connexion</h1>

<?php foreach ($errors as $error): ?>
    <div class="flash flash-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:420px">
    <form method="post" action="/commandes/login.php">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus value="<?= h($_POST['email'] ?? '') ?>">

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Se connecter</button>
    </form>
</div>

<p class="muted">Pas encore de compte ? <a href="/commandes/inscription.php">Inscrivez-vous</a>.</p>
