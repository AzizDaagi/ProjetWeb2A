<?php
$currentAction = $_GET['action'] ?? '';
$adminName = trim((string) ($_SESSION['user_name'] ?? ''));
if ($adminName === '') {
    $adminName = 'Administrateur';
}

$adminNameParts = preg_split('/\s+/', $adminName);
$adminInitials = '';
foreach ($adminNameParts as $part) {
    if ($part !== '') {
        $adminInitials .= strtoupper(substr($part, 0, 1));
        if (strlen($adminInitials) >= 2) {
            break;
        }
    }
}

if ($adminInitials === '') {
    $adminInitials = 'AD';
}

$isActivitiesAction = in_array($currentAction, [
    'admin_dashboard',
    'admin_index',
    'admin_show',
    'createActivite',
    'addExercice',
    'editExercice',
    'updateExercice',
    'deleteExercice',
    'editActivite',
    'updateActivite',
    'deleteActivite',
], true);
$isNutritionAction = in_array($currentAction, [
    'admin_requests',
    'admin_edit_request',
    'admin_update_request',
    'admin_delete_request',
], true);
?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="<?= $basePath ?>/index.php?action=admin_dashboard" class="admin-brand-link">
                <img
                    src="<?= $basePath ?>/View/assets/images/logo.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title">Navigation</p>
            <a href="<?= $basePath ?>/index.php?action=admin_dashboard" class="admin-side-link<?= $currentAction === 'admin_dashboard' ? ' active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= $basePath ?>/index.php?action=admin_dashboard#add-form" class="admin-side-link<?= $isActivitiesAction ? ' active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i>
                <span>Activite sportif</span>
            </a>
            <a href="<?= $basePath ?>/index.php?action=admin_requests" class="admin-side-link<?= $isNutritionAction ? ' active' : '' ?>">
                <i class="fa-solid fa-file-waveform"></i>
                <span>Demandes nutrition</span>
            </a>
            <a href="<?= $basePath ?>/index.php?action=home" class="admin-side-link">
                <i class="fa-solid fa-house"></i>
                <span>Front office</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title">Modules</p>
            <button type="button" class="admin-side-link admin-module-btn" data-module-title="Recette alimentation" data-module-description="Module en cours de developpement pour creer, modifier et supprimer des recettes alimentaires.">
                <i class="fa-solid fa-book-open"></i>
                <span>Recette alimentation</span>
            </button>
            <button type="button" class="admin-side-link admin-module-btn" data-module-title="Ecommerce" data-module-description="Module ecommerce pour gerer les produits, le panier, les commandes et le suivi de vente.">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Ecommerce</span>
            </button>
            <button type="button" class="admin-side-link admin-module-btn" data-module-title="Communaute" data-module-description="Module communaute pour publier des recommandations, echanger et moderer les contenus.">
                <i class="fa-solid fa-users"></i>
                <span>Communaute</span>
            </button>
            <button type="button" class="admin-side-link admin-module-btn<?= $isActivitiesAction ? ' active' : '' ?>" data-module-title="Activite sportif" data-module-description="Module activite sportif pour gerer les activites, exercices, calories et durees.">
                <i class="fa-solid fa-chart-line"></i>
                <span>Activite sportif</span>
            </button>
            <button type="button" class="admin-side-link admin-module-btn" data-module-title="Planning" data-module-description="Module planning pour organiser les objectifs, les rappels et les taches hebdomadaires.">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Planning</span>
            </button>

            <div id="adminModuleDescription" class="admin-module-description" tabindex="-1">
                <strong id="adminModuleDescriptionTitle">Description module</strong>
                <p id="adminModuleDescriptionText">Cliquez sur un bouton de gestion pour afficher sa description ici.</p>
            </div>
        </div>
    </aside>

    <header class="admin-topbar">
        <div class="admin-top-actions">
            <button type="button" class="admin-icon-btn" aria-label="Notifications">
                <i class="fa-solid fa-bell"></i>
            </button>

            <button type="button" id="themeToggle" class="admin-icon-btn theme-toggle admin-theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
                <i class="fa-solid fa-moon"></i>
            </button>

            <div class="admin-user-chip">
                <span class="admin-user-avatar"><?= htmlspecialchars($adminInitials) ?></span>
                <div class="admin-user-meta">
                    <strong><?= htmlspecialchars($adminName) ?></strong>
                    <span>Administrator</span>
                </div>
            </div>

            <a href="<?= $basePath ?>/index.php?action=admin_logout" class="admin-logout-btn" title="Deconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </header>
</div>
