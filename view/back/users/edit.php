<div class="container admin-page admin-form-page">
    <?php $editableUser = is_array($user ?? null) ? $user : []; ?>
    <?php $editableRoleSlug = (string) ($editableUser['role'] ?? 'user'); ?>
    <?php $isEditingAdmin = $editableRoleSlug === 'admin'; ?>
    <?php $editableRoleLabel = $editableRoleSlug === 'admin' ? 'Admin' : 'Utilisateur'; ?>
    <h1><i class="fa-solid fa-user-pen icon"></i> Modifier un utilisateur</h1>
    <p class="subtitle"><?= htmlspecialchars(trim((string) (($editableUser['prenom'] ?? '') . ' ' . ($editableUser['nom'] ?? '')))) ?></p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/smart_nutrition/index.php?action=update-user" novalidate>
        <input type="hidden" name="id" value="<?= (int) ($editableUser['id'] ?? 0) ?>">

        <div class="field">
            <label><i class="fa-solid fa-tag icon"></i>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars((string) ($editableUser['nom'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Prenom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars((string) ($editableUser['prenom'] ?? '')) ?>" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-calendar icon"></i>Date de naissance</label>
            <input type="date" name="date_naissance" value="<?= htmlspecialchars((string) ($editableUser['date_naissance'] ?? '')) ?>" min="1950-01-01" max="<?= date('Y-m-d', strtotime('-13 years')) ?>" required>
        </div>

        <?php if (!$isEditingAdmin): ?>
            <div class="field">
                <label><i class="fa-solid fa-venus-mars icon"></i>Sexe</label>
                <select name="sexe" required>
                    <option value="">Selectionnez...</option>
                    <option value="homme" <?= ((string) ($editableUser['sexe'] ?? '') === 'homme') ? 'selected' : '' ?>>Homme</option>
                    <option value="femme" <?= ((string) ($editableUser['sexe'] ?? '') === 'femme') ? 'selected' : '' ?>>Femme</option>
                </select>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-hourglass-half icon"></i>Age</label>
                <input type="number" name="age" min="13" max="120" value="<?= htmlspecialchars((string) ($editableUser['age'] ?? '')) ?>" readonly required>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-weight-scale icon"></i>Poids (kg)</label>
                <input type="number" name="poids" min="30" max="250" step="0.01" value="<?= htmlspecialchars((string) ($editableUser['poids'] ?? '')) ?>" required>
            </div>

            <div class="field">
                <label><i class="fa-solid fa-ruler-vertical icon"></i>Taille (cm)</label>
                <input type="number" name="taille" min="1" max="300" step="0.01" value="<?= htmlspecialchars((string) ($editableUser['taille'] ?? '')) ?>">
            </div>

            <div class="field">
                <label><i class="fa-solid fa-bullseye icon"></i>Objectif</label>
                <textarea name="objectif" rows="4" required><?= htmlspecialchars((string) ($editableUser['objectif'] ?? '')) ?></textarea>
            </div>
        <?php endif; ?>

        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($editableUser['email'] ?? '')) ?>" required>
        </div>

        <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Enregistrer</button>
    </form>

    <div class="actions">
        <a href="/smart_nutrition/index.php?action=users-list" class="btn secondary">Retour a la liste</a>
    </div>
</div>
