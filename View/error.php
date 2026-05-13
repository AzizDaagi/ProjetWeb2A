<?php
$pageTitle = 'Application Error';
$showNav = false;
$showFooter = true;
require __DIR__ . '/header.php';
?>

<div class="container">
    <h1><i class="fa-solid fa-triangle-exclamation icon"></i> Something went wrong</h1>
    <p class="subtitle"><?= htmlspecialchars($message ?? 'An unexpected error occurred.') ?></p>
    <a href="<?= htmlspecialchars(route_url('home')) ?>" class="btn">Back to home</a>
</div>

<?php require __DIR__ . '/footer.php'; ?>
