<?php
$errorMessage = $_SESSION['admin_aliment_error'] ?? null;
unset($_SESSION['admin_aliment_error']);
?>
<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-plus icon"></i> Ajouter un aliment au suivi</h1>
        <p class="subtitle">Creer une nouvelle entree dans le catalogue du module Suivi.</p>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
    <?php endif; ?>

    <section class="admin-widget">
        <?php require __DIR__ . '/form.php'; ?>
    </section>
</div>
