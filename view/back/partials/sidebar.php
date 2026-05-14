<?php $currentSection = $currentSection ?? 'dashboard'; ?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <a href="index.php?action=admin-dashboard" class="admin-brand-link">
            <img
                src="/Web/view/assets/images/logo.png"
                alt="Smart Nutrition"
                class="admin-brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <div class="admin-menu-section">
        <p class="admin-menu-title">Navigation</p>
        <a href="index.php?action=admin-dashboard" class="admin-side-link<?= $currentSection === 'dashboard' ? ' active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>
        <a href="index.php?action=users-list" class="admin-side-link<?= $currentSection === 'users' ? ' active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Utilisateurs</span>
        </a>
    </div>

    <div class="admin-menu-section admin-modules-section">
        <p class="admin-menu-title">Modules</p>
        <a href="index.php?controller=backoffice&action=suivi" class="admin-side-link admin-module-btn<?= $currentSection === 'suivi' ? ' active' : '' ?>" data-module-title="Suivi nutritionnel" data-module-description="Module de suivi nutritionnel pour gerer le catalogue des aliments et les actions associees.">
            <i class="fa-solid fa-apple-whole"></i>
            <span>Suivi nutritionnel</span>
        </a>
        <a href="index.php?controller=backoffice&action=suivi" class="admin-side-link admin-module-btn<?= $currentSection === 'suivi' ? ' active' : '' ?>" data-module-title="Aliments" data-module-description="Acces direct a la gestion officielle des aliments du suivi nutritionnel.">
            <i class="fa-solid fa-carrot"></i>
            <span>Aliments</span>
        </a>
        <a href="index.php?controller=backoffice&action=objectifs" class="admin-side-link admin-module-btn<?= $currentSection === 'objectifs' ? ' active' : '' ?>" data-module-title="Objectifs" data-module-description="Module actif pour la gestion des objectifs caloriques et de la progression utilisateur.">
            <i class="fa-solid fa-bullseye"></i>
            <span>Objectifs</span>
        </a>
        <div id="adminModuleDescription" class="admin-module-description" tabindex="-1">
            <strong id="adminModuleDescriptionTitle"><?= $currentSection === 'suivi' ? 'Suivi nutritionnel' : 'Objectifs' ?></strong>
            <p id="adminModuleDescriptionText"><?= $currentSection === 'suivi'
                ? 'Module de suivi nutritionnel pour gerer le catalogue des aliments et les actions associees.'
                : 'Module actif pour la gestion des objectifs caloriques et de la progression utilisateur.' ?></p>
        </div>
    </div>
</aside>
