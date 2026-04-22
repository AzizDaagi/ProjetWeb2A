<?php $users = $users ?? []; ?>
<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-users icon"></i> Utilisateurs</h1>
        <p class="subtitle">Liste dynamique des utilisateurs enregistres dans l'application.</p>
    </div>

    <section class="admin-widget">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Age</th>
                    <th>Poids</th>
                    <th>Taille</th>
                    <th>Objectif</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $userRow): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $userRow['id']) ?></td>
                            <td><?= htmlspecialchars($userRow['nom'] !== '' ? $userRow['nom'] : 'Utilisateur #' . $userRow['id']) ?></td>
                            <td><?= $userRow['age'] !== null ? htmlspecialchars((string) $userRow['age']) . ' ans' : '-' ?></td>
                            <td><?= $userRow['poids'] !== null ? htmlspecialchars(number_format((float) $userRow['poids'], 1, '.', ' ')) . ' kg' : '-' ?></td>
                            <td><?= $userRow['taille'] !== null ? htmlspecialchars(number_format((float) $userRow['taille'], 0, '.', ' ')) . ' cm' : '-' ?></td>
                            <td><?= $userRow['objectif_calories'] !== null ? htmlspecialchars(number_format((float) $userRow['objectif_calories'], 0, '.', ' ')) . ' kcal' : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="admin-empty-cell">Aucun utilisateur enregistre.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
