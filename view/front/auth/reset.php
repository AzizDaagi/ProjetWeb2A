<div class="container">
    <h1>Reinitialiser le mot de passe</h1>

    <?php if (!empty($_SESSION['flash_error'])) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['flash_error']); ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="post" action="/smart_nutrition/index.php?action=reset-password">
        <div class="form-group">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" class="form-control" required value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" />
        </div>

        <div class="form-group">
            <label for="code">Code de reinitialisation</label>
            <input id="code" name="code" type="text" class="form-control" required maxlength="10" />
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input id="password" name="password" type="password" class="form-control" required />
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input id="password_confirm" name="password_confirm" type="password" class="form-control" required />
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Valider</button>
            <a href="/smart_nutrition/index.php?action=login" class="btn btn-link">Retour</a>
        </div>
    </form>
</div>
