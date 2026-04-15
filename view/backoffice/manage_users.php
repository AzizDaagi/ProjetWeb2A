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
require_once __DIR__ . '/../template_only/layouts/header.php'; 
?>

<div class="container admin-dashboard">
    <a href="index.php" class="btn" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; display: inline-block;">
        <i class="fa-solid fa-arrow-left"></i> Retour Dashboard
    </a>
    
    <h1>Liste des Utilisateurs</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle actuel</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $u['id']) ?></td>
                    <td><?= htmlspecialchars((string) $u['nom']) ?></td>
                    <td><?= htmlspecialchars((string) $u['email']) ?></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px;">Admin</span>
                        <?php else: ?>
                            <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px;">User</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_role">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-warning">Changer Rôle</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
