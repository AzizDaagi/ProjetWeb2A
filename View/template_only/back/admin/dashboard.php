<div class="container admin-dashboard">
    <h1><i class="fa-solid fa-user-shield icon"></i> Admin Dashboard</h1>
    <p class="subtitle">Overview and quick access to administration modules.</p>

    <div class="admin-cards">
        <div class="admin-card">
            <p class="admin-card-label">Total Users</p>
            <p class="admin-card-value"><?= htmlspecialchars((string) $totalUsers) ?></p>
        </div>
        <div class="admin-card">
            <p class="admin-card-label">System Status</p>
            <p class="admin-card-value">Online</p>
        </div>
    </div>

    <div class="actions admin-actions">
        <a href="/smart_nutrition/index.php?action=users-list" class="btn">
            <i class="fa-solid fa-users"></i> Manage Users
        </a>
        <a href="/smart_nutrition/index.php?action=recipes-management" class="btn">
            <i class="fa-solid fa-book-open"></i> Manage Recipes
        </a>
        <a href="/smart_nutrition/index.php?action=foods-management" class="btn">
            <i class="fa-solid fa-apple-whole"></i> Manage Foods
        </a>
        <a href="/smart_nutrition/index.php?action=recommendations-management" class="btn">
            <i class="fa-solid fa-chart-line"></i> Nutrition Recommendations
        </a>
    </div>

    <div class="admin-recent">
        <h2>Recent Users</h2>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $userRow): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $userRow['id']) ?></td>
                            <td><?= htmlspecialchars((string) $userRow['prenom']) ?></td>
                            <td><?= htmlspecialchars((string) $userRow['nom']) ?></td>
                            <td><?= htmlspecialchars((string) $userRow['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No users available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
