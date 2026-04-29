<div class="container admin-page admin-form-page">
    <?php $newUser = is_array($user ?? null) ? $user : []; ?>
    <h1><i class="fa-solid fa-user-plus icon"></i> Ajouter un utilisateur</h1>
    <p class="subtitle">Creation d'un nouveau compte utilisateur</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/smart_nutrition/index.php?action=store-user" novalidate>
        <div class="field">
            <label><i class="fa-solid fa-tag icon"></i>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars((string) ($newUser['nom'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Prenom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars((string) ($newUser['prenom'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-calendar icon"></i>Date de naissance</label>
            <input type="date" name="date_naissance" value="<?= htmlspecialchars((string) ($newUser['date_naissance'] ?? '')) ?>" min="1950-01-01" max="<?= date('Y-m-d', strtotime('-13 years')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-venus-mars icon"></i>Sexe</label>
            <select name="sexe" required>
                <option value="">Selectionnez...</option>
                <option value="homme" <?= ((string) ($newUser['sexe'] ?? '') === 'homme') ? 'selected' : '' ?>>Homme</option>
                <option value="femme" <?= ((string) ($newUser['sexe'] ?? '') === 'femme') ? 'selected' : '' ?>>Femme</option>
            </select>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-hourglass-half icon"></i>Age</label>
            <input type="number" name="age" min="13" max="120" value="<?= htmlspecialchars((string) ($newUser['age'] ?? '')) ?>" readonly required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-weight-scale icon"></i>Poids (kg)</label>
            <input type="number" name="poids" min="30" max="250" step="0.01" value="<?= htmlspecialchars((string) ($newUser['poids'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-ruler-vertical icon"></i>Taille (cm)</label>
            <input type="number" name="taille" min="1" max="300" step="0.01" value="<?= htmlspecialchars((string) ($newUser['taille'] ?? '')) ?>">
        </div>

        <div class="field">
            <label><i class="fa-solid fa-bullseye icon"></i>Objectif</label>
            <textarea name="objectif" rows="4" required><?= htmlspecialchars((string) ($newUser['objectif'] ?? '')) ?></textarea>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($newUser['email'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Mot de passe</label>
            <input type="password" name="password" minlength="6" required>
        </div>

        <button type="submit" class="btn-admin"><i class="fa-solid fa-check"></i> Ajouter</button>
    </form>

    <div class="actions">
        <a href="/smart_nutrition/index.php?action=users-list" class="btn-admin-secondary">Retour a la liste</a>
    </div>
</div>
