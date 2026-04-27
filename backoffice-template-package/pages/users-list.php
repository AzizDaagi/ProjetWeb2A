<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-users icon"></i> Utilisateurs</h1>
        <p class="subtitle">Exemple de liste pour le backoffice.</p>
    </div>

    <section class="admin-widget">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>E-mail</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $userRow): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $userRow['nom']) ?></td>
                        <td><?= htmlspecialchars((string) $userRow['prenom']) ?></td>
                        <td><?= htmlspecialchars((string) $userRow['email']) ?></td>
                        <td><a href="#" class="btn-edit">Modifier</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
