<?php 
$currentAction = $_GET['action'] ?? 'home'; 
?>
<aside class="admin-sidebar">
    <div class="admin-brand" style="margin-bottom: 30px;">
        <a href="<?= $basePath ?>/index.php?action=home" class="admin-brand-link" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
            <img src="<?= $basePath ?>/2-removebg-preview.png" alt="Logo" style="height: 100px; margin-bottom: 10px;">
            <span style="font-size: 1.2rem; font-weight: 700; color: var(--text-main, #ecf0f1);">Smart Nutrition</span>
        </a>
    </div>
    <nav class="admin-nav">
        <a href="<?= $basePath ?>/index.php?action=home" class="admin-nav-link <?= $currentAction == 'home' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> <span>Accueil</span>
        </a>
        <a href="<?= $basePath ?>/index.php?action=activites" class="admin-nav-link <?= $currentAction == 'activites' ? 'active' : '' ?>">
            <i class="fa-solid fa-dumbbell"></i> <span>Activites Sportives</span>
        </a>
        <a href="<?= $basePath ?>/index.php?action=nutrition_request" class="admin-nav-link <?= $currentAction == 'nutrition_request' ? 'active' : '' ?>">
            <i class="fa-solid fa-apple-whole"></i> <span>Bilan Nutritionnel</span>
        </a>
        <a href="#" class="admin-nav-link">
            <i class="fa-solid fa-utensils"></i> <span>Recettes</span>
        </a>
        <a href="#" class="admin-nav-link">
            <i class="fa-solid fa-cart-shopping"></i> <span>E-Commerce</span>
        </a>
        <a href="#" class="admin-nav-link">
            <i class="fa-solid fa-users"></i> <span>Communaute</span>
        </a>
        <a href="#" class="admin-nav-link">
            <i class="fa-solid fa-calendar"></i> <span>Planning</span>
        </a>
        <div class="admin-nav-divider"></div>
        <a href="<?= $basePath ?>/index.php?action=admin_dashboard" class="admin-nav-link admin-nav-backoffice <?= in_array($currentAction, ['admin_dashboard','admin_index','admin_show','editActivite','editExercice']) ? 'active' : '' ?>">
            <i class="fa-solid fa-shield-halved"></i> <span>BackOffice Admin</span>
        </a>
    </nav>
    <div class="admin-sidebar-footer">
        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle color mode">
            <i class="fa-solid fa-moon"></i> <span>Theme</span>
        </button>
    </div>
</aside>
