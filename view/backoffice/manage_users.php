<?php
$pageTitle = 'Gestion des Utilisateurs';
require_once __DIR__ . '/../../controler/UserController.php';

$controller = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'toggle_role') {
            $controller->toggleRole($_POST['id']);
        } elseif ($_POST['action'] === 'delete') {
            $controller->deleteUser($_POST['id']);
        }
        header('Location: manage_users.php');
        exit;
    }
}

$users = $controller->listUsers();
require_once __DIR__ . '/../template_only/layouts/admin_header.php'; 
?>

<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-users icon"></i> Utilisateurs</h1>
        <p class="subtitle">Liste complète des utilisateurs inscrits</p>
    </div>

    <div class="users-tools">
        <div class="admin-search-wrap users-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="usersSearchInput" placeholder="Rechercher un utilisateur..." autocomplete="off">
        </div>

        <div class="users-tools-meta">
            <span id="usersResultsCount" class="users-results-count"><?= count($users) ?> utilisateur(s)</span>
            <a href="#" class="btn-admin" style="background: #2ecc71; color: white;">
                <i class="fa-solid fa-user-plus"></i> Ajouter
            </a>
            <button type="button" class="btn-admin-secondary users-export-btn">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </button>
        </div>
    </div>

    <div class="table-wrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>E-mail</th>
                    <th>Rôle</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars((string) $u['id']) ?></strong></td>
                            <td><?= htmlspecialchars((string) $u['nom']) ?></td>
                            <td><?= htmlspecialchars((string) $u['email']) ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em;">Admin</span>
                                <?php else: ?>
                                    <span style="background: #2ecc71; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em;">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="users-actions" style="text-align: right;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_role">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn-edit" style="border: none; cursor: pointer; padding: 8px 12px;">
                                        <i class="fa-solid fa-rotate"></i> Changer Rôle
                                    </button>
                                </form>
                                <form method="POST" action="manage_users.php" style="display:inline;" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn-delete-user" style="background: transparent; color: #e74c3c; border: none; cursor: pointer; padding: 8px 12px;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 30px; color: #7f8c8d;">Aucun utilisateur trouvé</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/admin_footer.php'; ?>
