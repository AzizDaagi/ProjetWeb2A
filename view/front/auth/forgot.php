<?php include __DIR__ . '/../../layouts/public_brand.php'; ?>

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

    <?php if (!empty($_SESSION['dev_reset_link'])) : ?>
        <div class="alert alert-warning">
            <strong>Mode developpement :</strong>
            SMTP non configure en local. Utilisez ce lien de reinitialisation :
            <br>
            <a href="<?php echo htmlspecialchars($_SESSION['dev_reset_link']); ?>"><?php echo htmlspecialchars($_SESSION['dev_reset_link']); ?></a>
        </div>
        <?php unset($_SESSION['dev_reset_link']); ?>
    <?php endif; ?>

    <form method="post" action="/projet-web-25-26/index.php?action=forgot">
        <div class="form-group">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" class="form-control" required />
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Envoyer le code de vÃƒÂ©rification</button>
            <a href="/projet-web-25-26/index.php?action=login" class="btn btn-link">Retour</a>
        </div>
    </form>
</div>
