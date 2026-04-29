<div class="container">
    <?php $profileUser = is_array($user ?? null) ? $user : []; ?>
    <?php $profileRoleSlug = trim((string) ($profileUser['role'] ?? ($_SESSION['user_role'] ?? 'user'))); ?>
    <?php $isAdminProfile = $profileRoleSlug === 'admin'; ?>
    <?php $hasFaceDescriptor = trim((string) ($profileUser['face_descriptor'] ?? '')) !== ''; ?>
    <h1><i class="fa-solid fa-user icon"></i>Mon profil</h1>

    <?php
    $profileIncomplete =
        trim((string) ($profileUser['date_naissance'] ?? '')) === '' ||
        (!$isAdminProfile && (
            trim((string) ($profileUser['sexe'] ?? '')) === '' ||
            trim((string) ($profileUser['age'] ?? '')) === '' ||
            trim((string) ($profileUser['poids'] ?? '')) === '' ||
            trim((string) ($profileUser['objectif'] ?? '')) === ''
        ));
    ?>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($profileIncomplete): ?>
        <div class="alert alert-error">Completez vos donnees personnelles pour finaliser votre compte.</div>
    <?php endif; ?>

    <form method="POST" action="/smart_nutrition/index.php?action=update-profile" novalidate>
        <div class="field">
            <label><i class="fa-solid fa-tag icon"></i>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars((string) ($profileUser['nom'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Prenom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars((string) ($profileUser['prenom'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-calendar icon"></i>Date de naissance</label>
            <input type="date" name="date_naissance" value="<?= htmlspecialchars((string) ($profileUser['date_naissance'] ?? '')) ?>" min="1950-01-01" max="<?= date('Y-m-d', strtotime('-13 years')) ?>" required>
        </div>

        <?php if (!$isAdminProfile): ?>
            <div class="field">
                <label><i class="fa-solid fa-venus-mars icon"></i>Sexe</label>
                <select name="sexe" required>
                    <option value="">Selectionnez...</option>
                    <option value="homme" <?= ((string) ($profileUser['sexe'] ?? '') === 'homme') ? 'selected' : '' ?>>Homme</option>
                    <option value="femme" <?= ((string) ($profileUser['sexe'] ?? '') === 'femme') ? 'selected' : '' ?>>Femme</option>
                </select>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-hourglass-half icon"></i>Age</label>
                <input type="number" name="age" min="13" max="79" value="<?= htmlspecialchars((string) ($profileUser['age'] ?? '')) ?>" readonly required>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-weight-scale icon"></i>Poids (kg)</label>
                <input type="number" name="poids" min="30" max="250" step="0.01" value="<?= htmlspecialchars((string) ($profileUser['poids'] ?? '')) ?>" required>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-ruler-vertical icon"></i>Taille (cm)</label>
                <input type="number" name="taille" min="120" max="210" step="0.01" value="<?= htmlspecialchars((string) ($profileUser['taille'] ?? '')) ?>" required>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-bullseye icon"></i>Objectif</label>
                <textarea name="objectif" rows="4" required><?= htmlspecialchars((string) ($profileUser['objectif'] ?? '')) ?></textarea>
            </div>
        <?php endif; ?>

        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($profileUser['email'] ?? '')) ?>" required>
        </div>

        <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Enregistrer mes donnees</button>
    </form>

    <section
        class="face-auth-card"
        data-face-auth-mode="enroll"
        data-endpoint="/smart_nutrition/index.php?action=save-face-descriptor"
        data-clear-endpoint="/smart_nutrition/index.php?action=clear-face-descriptor"
    >
        <h2 class="face-auth-title"><i class="fa-solid fa-id-card icon"></i>Reconnaissance faciale</h2>
        <p class="face-auth-text">Enregistrez votre visage pour vous connecter sans mot de passe depuis l'ecran de login.</p>

        <p class="face-state-badge <?= $hasFaceDescriptor ? 'is-ready' : 'is-missing' ?>">
            <?= $hasFaceDescriptor ? 'Empreinte faciale active.' : 'Aucune empreinte faciale enregistree.' ?>
        </p>

        <div class="face-preview-wrap">
            <video class="face-video" autoplay playsinline muted></video>
            <canvas class="face-canvas" aria-hidden="true"></canvas>
        </div>

        <p class="face-status" aria-live="polite">
            <?= $hasFaceDescriptor ? 'Empreinte disponible. Vous pouvez la mettre a jour ou la supprimer.' : 'Camera inactive.' ?>
        </p>

        <div class="face-actions">
            <button type="button" class="face-btn face-btn-secondary js-face-start">
                <i class="fa-solid fa-video"></i>Activer la camera
            </button>
            <button type="button" class="face-btn js-face-submit" disabled>
                <i class="fa-solid fa-floppy-disk"></i>Enregistrer mon visage
            </button>
            <button type="button" class="face-btn face-btn-danger js-face-clear" <?= $hasFaceDescriptor ? '' : 'disabled' ?>>
                <i class="fa-solid fa-trash"></i>Supprimer empreinte
            </button>
        </div>
    </section>

    <div class="profile-card">
        <p><strong><i class="fa-solid fa-tag icon"></i>Nom:</strong> <?= htmlspecialchars((string) ($profileUser['nom'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-user icon"></i>Prenom:</strong> <?= htmlspecialchars((string) ($profileUser['prenom'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-calendar icon"></i>Date de naissance:</strong> <?= htmlspecialchars((string) ($profileUser['date_naissance'] ?? '')) ?></p>
        <?php if (!$isAdminProfile): ?>
            <p><strong><i class="fa-solid fa-venus-mars icon"></i>Sexe:</strong> <?= htmlspecialchars((string) ($profileUser['sexe'] ?? '')) ?></p>
            <p><strong><i class="fa-solid fa-hourglass-half icon"></i>Age:</strong> <?= htmlspecialchars((string) ($profileUser['age'] ?? '')) ?></p>
            <p><strong><i class="fa-solid fa-weight-scale icon"></i>Poids:</strong> <?= htmlspecialchars((string) ($profileUser['poids'] ?? '')) ?> kg</p>
            <p><strong><i class="fa-solid fa-ruler-vertical icon"></i>Taille:</strong> <?= htmlspecialchars((string) ($profileUser['taille'] ?? '')) ?> cm</p>
            <p><strong><i class="fa-solid fa-bullseye icon"></i>Objectif:</strong> <?= htmlspecialchars((string) ($profileUser['objectif'] ?? '')) ?></p>
        <?php endif; ?>
        <p><strong><i class="fa-solid fa-envelope icon"></i>E-mail:</strong> <?= htmlspecialchars((string) ($profileUser['email'] ?? '')) ?></p>
    </div>
</div>
