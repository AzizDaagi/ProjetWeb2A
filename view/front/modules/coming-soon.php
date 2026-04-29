<div class="container">
    <?php $isAdminSession = isset($_SESSION['user_id']) && (($_SESSION['user_role'] ?? 'user') === 'admin'); ?>
    <?php $backAction = $isAdminSession ? 'admin-dashboard' : 'home'; ?>
    <h1><i class="fa-solid fa-gears"></i> <?= htmlspecialchars($moduleTitle ?? 'Module') ?></h1>
    <p class="subtitle">Feuille de route des fonctionnalites</p>

    <p style="text-align:center; line-height:1.8; margin-bottom:18px;">
        <?= htmlspecialchars($moduleDescription ?? 'Ce module sera bientot disponible.') ?>
    </p>

    <div class="actions">
        <a class="btn" href="/smart_nutrition/index.php?action=<?= htmlspecialchars($backAction) ?>">
            <i class="fa-solid <?= $isAdminSession ? 'fa-gauge-high' : 'fa-home' ?>"></i>
            <?= $isAdminSession ? 'Retour au dashboard admin' : 'Retour a l\'accueil' ?>
        </a>
    </div>
</div>
