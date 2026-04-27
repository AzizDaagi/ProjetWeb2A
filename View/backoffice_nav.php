<?php
$currentAction = $_GET['action'] ?? 'admin.products';
$isCatalogActive = in_array($currentAction, ['admin.products', 'admin.products.create', 'admin.products.edit'], true);
$isPendingActive = $currentAction === 'admin.products.pending';
$isOrdersActive = in_array($currentAction, ['admin.orders', 'admin.orders.edit'], true);
?>
<nav class="admin-sidebar">
    <div class="admin-brand">
        <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="admin-brand-link">
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <div class="admin-menu-section">
        <p class="admin-menu-title">Navigation</p>
        <a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="admin-side-link<?= $isCatalogActive ? ' active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= htmlspecialchars(route_url('admin.products.pending')) ?>" class="admin-side-link<?= $isPendingActive ? ' active' : '' ?>">
            <i class="fa-solid fa-hourglass-half"></i>
            <span>Pending</span>
        </a>
        <a href="<?= htmlspecialchars(route_url('admin.orders')) ?>" class="admin-side-link<?= $isOrdersActive ? ' active' : '' ?>">
            <i class="fa-solid fa-clipboard-list"></i>
            <span>Orders</span>
        </a>
    </div>

    <div class="admin-menu-section admin-modules-section">
        <p class="admin-menu-title">Modules</p>
        <button type="button" class="admin-side-link admin-module-btn<?= $isCatalogActive ? ' active' : '' ?>" data-module-title="Product catalog" data-module-description="Manage products, approvals, and publishing from one place.">
            <i class="fa-solid fa-apple-whole"></i>
            <span>Catalog</span>
        </button>
        <button type="button" class="admin-side-link admin-module-btn<?= $isOrdersActive ? ' active' : '' ?>" data-module-title="Order management" data-module-description="Review and process customer orders from the back office.">
            <i class="fa-solid fa-receipt"></i>
            <span>Orders</span>
        </button>
        <div id="adminModuleDescription" class="admin-module-description" tabindex="-1">
            <strong id="adminModuleDescriptionTitle"><?= htmlspecialchars($isOrdersActive ? 'Order management' : 'Product catalog') ?></strong>
            <p id="adminModuleDescriptionText"><?= htmlspecialchars($isOrdersActive ? 'Review and process customer orders from the back office.' : 'Manage products, approvals, and publishing from one place.') ?></p>
        </div>
    </div>
</nav>

<header class="admin-topbar">
    <div class="admin-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search products or orders" aria-label="Search">
    </div>

    <div class="admin-top-actions">
        <a href="<?= htmlspecialchars(route_url('home')) ?>" class="admin-icon-btn" title="Front office" aria-label="Front office">
            <i class="fa-solid fa-globe"></i>
        </a>
        <button type="button" id="themeToggle" class="admin-icon-btn theme-toggle admin-theme-toggle" aria-label="Toggle color mode" aria-pressed="false">
            <i class="fa-solid fa-moon"></i>
        </button>
        <div class="admin-user-chip">
            <span class="admin-user-avatar">AD</span>
            <div class="admin-user-meta">
                <strong>Administrateur</strong>
                <span>Back office</span>
            </div>
        </div>
    </div>
</header>
