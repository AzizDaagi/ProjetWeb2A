<?php
$currentAction = $_GET['action'] ?? 'admin.products';
$isCatalogActive = in_array($currentAction, ['admin.products', 'admin.products.create', 'admin.products.edit'], true);
$isPendingActive = $currentAction === 'admin.products.pending';
$isOrdersActive = in_array($currentAction, ['admin.orders', 'admin.orders.edit'], true);
$isPredictionActive = in_array($currentAction, ['admin.prediction.panel', 'admin.prediction.predict'], true);
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
        <a href="<?= htmlspecialchars(route_url('admin.prediction.panel')) ?>" class="admin-side-link<?= $isPredictionActive ? ' active' : '' ?>">
            <i class="fa-solid fa-brain"></i>
            <span>Product Prediction</span>
        </a>
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
