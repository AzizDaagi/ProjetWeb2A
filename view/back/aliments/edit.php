<?php
$errorMessage = $_SESSION['admin_aliment_error'] ?? null;
unset($_SESSION['admin_aliment_error']);
?>
<div class="admin-page admin-module-page">
    <div class="admin-page-header">
        <span class="admin-page-kicker">Catalogue approuve</span>
        <h1>Modifier un aliment</h1>
        <p>Mettre a jour les informations nutritionnelles du catalogue alimentaire officiel.</p>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars((string) $errorMessage) ?></div>
    <?php endif; ?>

    <section class="admin-card">
        <?php require __DIR__ . '/form.php'; ?>
    </section>
</div>
