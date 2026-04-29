<div class="container">
    <h1>Inscription</h1>
    <p class="subtitle">Creez votre compte (e-mail + mot de passe), puis completez vos donnees apres connexion</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/smart_nutrition/index.php?action=register" novalidate>
        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" placeholder="Nom" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Prenom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" placeholder="Prenom" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="E-mail" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Mot de passe</label>
            <input type="password" name="password" placeholder="Mot de passe" required>
        </div>

        <section class="face-auth-card" data-face-auth-mode="register">
            <input type="hidden" name="face_descriptor" data-face-descriptor-input>
            <h2 class="face-auth-title"><i class="fa-solid fa-id-card icon"></i>Reconnaissance faciale (optionnel)</h2>
            <p class="face-auth-text">Capturez votre visage pour activer la connexion faciale des la creation du compte.</p>

            <p class="face-state-badge is-missing">Aucune empreinte faciale enregistree.</p>

            <div class="face-preview-wrap">
                <video class="face-video" autoplay playsinline muted></video>
                <canvas class="face-canvas" aria-hidden="true"></canvas>
            </div>

            <p class="face-status" aria-live="polite">Camera inactive.</p>

            <div class="face-actions">
                <button type="button" class="face-btn face-btn-secondary js-face-start">
                    <i class="fa-solid fa-video"></i>Activer la camera
                </button>
                <button type="button" class="face-btn js-face-submit" disabled>
                    <i class="fa-solid fa-floppy-disk"></i>Capturer mon visage
                </button>
                <button type="button" class="face-btn face-btn-danger js-face-clear" disabled>
                    <i class="fa-solid fa-trash"></i>Retirer l'empreinte
                </button>
            </div>
        </section>

        <button type="submit" class="btn-accent"><i class="fa-solid fa-user-plus icon success"></i>S'inscrire</button>
    </form>

    <p class="link-text">Deja inscrit ? <a href="/smart_nutrition/index.php?action=login">Se connecter</a></p>
</div>
