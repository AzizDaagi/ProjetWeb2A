<?php $currentSection = $currentSection ?? 'dashboard'; ?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <a href="index.php?controller=backoffice&action=dashboard" class="admin-brand-link">
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <div class="admin-menu-section">
        <p class="admin-menu-title">Navigation</p>
        <a href="index.php?controller=backoffice&action=dashboard" class="admin-side-link<?= $currentSection === 'dashboard' ? ' active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>
        <a href="index.php?controller=adminAliment&action=index" class="admin-side-link<?= $currentSection === 'aliments' ? ' active' : '' ?>">
            <i class="fa-solid fa-apple-whole"></i>
            <span>Aliments</span>
        </a>
        <a href="index.php?controller=backoffice&action=users" class="admin-side-link<?= $currentSection === 'users' ? ' active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Utilisateurs</span>
        </a>
    </div>

    <div class="admin-menu-section admin-modules-section">
        <p class="admin-menu-title">Modules</p>
        <button type="button" class="admin-side-link admin-module-btn active" data-module-title="Suivi nutritionnel" data-module-description="Administration des aliments, repas et indicateurs de suivi.">
            <i class="fa-solid fa-chart-line"></i>
            <span>Suivi</span>
        </button>
        <button type="button" class="admin-side-link admin-module-btn" data-module-title="Objectifs" data-module-description="Module lie aux objectifs caloriques et a la progression utilisateur.">
            <i class="fa-solid fa-bullseye"></i>
            <span>Objectifs</span>
        </button>
        <div id="adminModuleDescription" class="admin-module-description" tabindex="-1">
            <strong id="adminModuleDescriptionTitle">Suivi nutritionnel</strong>
            <p id="adminModuleDescriptionText">Administration des aliments, repas et indicateurs de suivi.</p>
        </div>
    </div>
</aside>
