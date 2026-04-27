<?php $currentAction = $_GET['action'] ?? 'admin-dashboard'; ?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <a href="#" class="admin-brand-link">
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <div class="admin-menu-section">
        <p class="admin-menu-title">Navigation</p>
        <a href="#" class="admin-side-link<?= $currentAction === 'admin-dashboard' ? ' active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="admin-side-link<?= $currentAction === 'users-list' ? ' active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Utilisateurs</span>
        </a>
    </div>

    <div class="admin-menu-section admin-modules-section">
        <p class="admin-menu-title">Modules</p>
        <button type="button" class="admin-side-link admin-module-btn active" data-module-title="Recette alimentation" data-module-description="Module en cours de developpement.">
            <i class="fa-solid fa-book-open"></i>
            <span>Recette alimentation</span>
        </button>
        <button type="button" class="admin-side-link admin-module-btn" data-module-title="Ecommerce" data-module-description="Module ecommerce pour gerer les produits et les commandes.">
            <i class="fa-solid fa-apple-whole"></i>
            <span>Ecommerce</span>
        </button>
        <div id="adminModuleDescription" class="admin-module-description" tabindex="-1">
            <strong id="adminModuleDescriptionTitle">Recette alimentation</strong>
            <p id="adminModuleDescriptionText">Module en cours de developpement.</p>
        </div>
    </div>
</aside>

<header class="admin-topbar">
    <div class="admin-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search" aria-label="Search">
    </div>

    <div class="admin-top-actions">
        <button type="button" id="themeToggle" class="admin-icon-btn theme-toggle admin-theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
            <i class="fa-solid fa-moon"></i>
        </button>
        <div class="admin-user-chip">
            <span class="admin-user-avatar">AD</span>
            <div class="admin-user-meta">
                <strong>Administrateur</strong>
                <span>Administrator</span>
            </div>
        </div>
    </div>
</header>
