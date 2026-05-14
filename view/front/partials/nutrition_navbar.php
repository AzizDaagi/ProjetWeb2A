<?php
if (!empty($GLOBALS['nutrition_nav_rendered'])) {
    return;
}
$GLOBALS['nutrition_nav_rendered'] = true;

$currentAction = (string) ($_GET['action'] ?? '');
$currentController = (string) ($_GET['controller'] ?? '');

$isSuiviActive = $currentController === 'suivi' || $currentAction === 'suivi';
$isObjectifActive = $currentController === 'objectif' || $currentAction === 'objectif';
$isDashboardActive = $currentAction === 'nutrition_dashboard';
?>
<nav class="nutrition-subnav" aria-label="Navigation nutrition">
    <a href="/projetwebmalek/index.php?action=suivi" class="nutrition-subnav__link<?= $isSuiviActive ? ' active' : '' ?>">
        <i class="fa-solid fa-fire"></i>
        <span>Suivi</span>
    </a>
    <a href="/projetwebmalek/index.php?action=objectif" class="nutrition-subnav__link<?= $isObjectifActive ? ' active' : '' ?>">
        <i class="fa-solid fa-bullseye"></i>
        <span>Objectifs</span>
    </a>
    <a href="/projetwebmalek/index.php?action=nutrition_dashboard" class="nutrition-subnav__link<?= $isDashboardActive ? ' active' : '' ?>">
        <i class="fa-solid fa-chart-line"></i>
        <span>Dashboard</span>
    </a>
</nav>
