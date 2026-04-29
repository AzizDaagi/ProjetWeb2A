<div class="container">
    <h1>Connexion</h1>
    <p class="subtitle">Accedez a votre espace Smart Nutrition</p>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/smart_nutrition/index.php?action=login" novalidate id="passwordLoginForm">
        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>E-mail</label>
            <input type="email" id="loginEmail" name="email" placeholder="Entrez votre e-mail" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Mot de passe</label>
            <input type="password" name="password" placeholder="Mot de passe" required>
        </div>

        <div class="login-actions">
            <button type="submit"><i class="fa-solid fa-right-to-bracket icon success"></i>Se connecter</button>
            <a class="btn btn-link forgot-link" href="/smart_nutrition/index.php?action=forgot">Mot de passe oubli&eacute; ?</a>
        </div>
    </form>

    <section
        class="face-auth-card"
        data-face-auth-mode="login"
        data-endpoint="/smart_nutrition/index.php?action=face-login"
    >
        <h2 class="face-auth-title"><i class="fa-solid fa-camera icon"></i>Connexion faciale</h2>
        <p class="face-auth-text">Saisissez votre e-mail, activez la camera puis lancez la verification faciale. L'aperçu reste masque.</p>

        <div class="face-preview-wrap is-hidden">
            <video class="face-video" autoplay playsinline muted></video>
            <canvas class="face-canvas" aria-hidden="true"></canvas>
        </div>

        <p class="face-status" aria-live="polite">Camera inactive.</p>

        <div class="face-actions">
            <button type="button" class="face-btn face-btn-secondary js-face-start">
                <i class="fa-solid fa-video"></i>Activer la camera
            </button>
            <button type="button" class="face-btn js-face-submit" disabled>
                <i class="fa-solid fa-face-smile"></i>Se connecter avec mon visage
            </button>
        </div>
    </section>

    <p class="link-text">Pas encore de compte ? <a href="/smart_nutrition/index.php?action=register">S'inscrire</a></p>
</div>
