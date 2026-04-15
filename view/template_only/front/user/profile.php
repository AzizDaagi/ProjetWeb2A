<div class="container">
    <?php $profileUser = is_array($user ?? null) ? $user : []; ?>
    <h1><i class="fa-solid fa-user icon"></i>My Profile</h1>

    <div class="profile-card">
        <p><strong><i class="fa-solid fa-id-card icon"></i>ID:</strong> <?= htmlspecialchars((string) ($profileUser['id'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-tag icon"></i>Last Name:</strong> <?= htmlspecialchars((string) ($profileUser['nom'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-user icon"></i>First Name:</strong> <?= htmlspecialchars((string) ($profileUser['prenom'] ?? '')) ?></p>
        <p><strong><i class="fa-solid fa-envelope icon"></i>Email:</strong> <?= htmlspecialchars((string) ($profileUser['email'] ?? '')) ?></p>
    </div>
</div>
