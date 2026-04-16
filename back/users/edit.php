<div class="container">
    <?php $editableUser = is_array($user ?? null) ? $user : []; ?>
    <h1><i class="fa-solid fa-user-pen icon"></i> Edit User</h1>
    <p class="subtitle"><?= htmlspecialchars(trim((string) (($editableUser['prenom'] ?? '') . ' ' . ($editableUser['nom'] ?? '')))) ?></p>

    <div class="profile-card">
        <p><strong><i class="fa-solid fa-id-card icon"></i>ID:</strong> <?= htmlspecialchars((string) ($editableUser['id'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-tag icon"></i>Last Name:</strong> <?= htmlspecialchars((string) ($editableUser['nom'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-user icon"></i>First Name:</strong> <?= htmlspecialchars((string) ($editableUser['prenom'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-envelope icon"></i>Email:</strong> <?= htmlspecialchars((string) ($editableUser['email'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-user-shield icon"></i>Role:</strong> <?= htmlspecialchars(ucfirst((string) ($editableUser['role'] ?? 'user'))) ?></p>
    </div>

    <div class="actions">
        <a href="<?= htmlspecialchars(app_url('index.php?action=users-list')) ?>" class="btn secondary">Back to list</a>
    </div>
</div>
