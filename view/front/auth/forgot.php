<div class="container">
    <h1>Mot de passe oublie</h1>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e) : ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['flash_error']); ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])) : ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="post" action="/smart_nutrition/index.php?action=forgot">
        <div class="form-group">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" class="form-control" required />
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Envoyer le code de vérification</button>
            <a href="/smart_nutrition/index.php?action=login" class="btn btn-link">Retour</a>
        </div>
    </form>
</div>
