<?php
$currentAction = (string) ($_GET['action'] ?? '');
$currentController = strtolower((string) ($_GET['controller'] ?? ''));

$nutritionNavItems = [
    [
        'label' => 'Suivi',
        'href' => 'index.php?action=suivi',
        'icon' => 'fa-fire',
        'active' => $currentAction === 'suivi' || $currentController === 'suivi',
    ],
    [
        'label' => 'Objectifs',
        'href' => 'index.php?controller=objectif&action=index',
        'icon' => 'fa-bullseye',
        'active' => $currentAction === 'objectif' || $currentController === 'objectif',
    ],
    [
        'label' => 'Dashboard',
        'href' => 'index.php?action=nutrition_dashboard',
        'icon' => 'fa-chart-line',
        'active' => strpos($currentAction, 'nutrition_dashboard') === 0,
    ],
];
?>

<div class="nutrition-subnav-wrap">
    <div class="container nutrition-subnav-shell">
        <nav class="nutrition-subnav" aria-label="Navigation du suivi nutritionnel">
            <?php foreach ($nutritionNavItems as $item): ?>
                <a
                    href="<?= htmlspecialchars($item['href']) ?>"
                    class="nutrition-subnav__link<?= !empty($item['active']) ? ' is-active' : '' ?>">
                    <i class="fa-solid <?= htmlspecialchars($item['icon']) ?>"></i>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>
