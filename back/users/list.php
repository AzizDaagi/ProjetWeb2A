<div class="container">
    <h1><i class="fa-solid fa-users icon"></i> User Management</h1>
    <p class="subtitle">Complete list of registered users</p>

    <table class="users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): ?>
                <?php $currentRole = ($u['role'] ?? 'user') === 'admin' ? 'admin' : 'user'; ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['prenom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="role-badge role-<?= htmlspecialchars($currentRole) ?>">
                            <?= htmlspecialchars(ucfirst($currentRole)) ?>
                        </span>
                    </td>
                    <td class="users-actions">
                        <a href="<?= htmlspecialchars(app_url('index.php?action=edit-user&id=' . $u['id'])) ?>" class="btn-edit">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <form method="POST" action="<?= htmlspecialchars(app_url('index.php?action=toggle-role')) ?>" class="inline-form">
                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                            <input type="hidden" name="current_role" value="<?= htmlspecialchars($currentRole) ?>">
                            <button type="submit" class="btn-role <?= $currentRole === 'admin' ? 'is-admin' : 'is-user' ?>">
                                <?= $currentRole === 'admin' ? 'Set as User' : 'Set as Admin' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No users found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="<?= htmlspecialchars(app_url('index.php?action=profile')) ?>" class="btn secondary">Back to profile</a>
</div>
