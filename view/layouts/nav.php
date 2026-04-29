<?php $isAdminNavbar = (($_SESSION['user_role'] ?? 'user') === 'admin'); ?>

<nav class="navbar<?= $isAdminNavbar ? ' navbar-compact' : '' ?>">
    <div class="navbar-brand">
        <a href="/smart_nutrition/index.php?action=home">
            <img
                src="/smart_nutrition/2-removebg-preview.png"
                alt="Smart Nutrition"
                class="brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <?php if (!$isAdminNavbar): ?>
    <ul class="navbar-menu">
        <li><a href="/smart_nutrition/index.php?action=profile" class="nav-link">
            <i class="fa-solid fa-user"></i> Mon profile
        </a></li>
        <li><a href="/smart_nutrition/index.php?action=tracking-management" class="nav-link">
            <i class="fa-solid fa-chart-line"></i> Activite sportif
        </a></li>
        <li><a href="/smart_nutrition/index.php?action=recipes-management" class="nav-link">
            <i class="fa-solid fa-book-open"></i> Recette alimentation
        </a></li>
        <li><a href="/smart_nutrition/index.php?action=foods-management" class="nav-link">
            <i class="fa-solid fa-apple-whole"></i> Ecommerce
        </a></li>
        <li><a href="/smart_nutrition/index.php?action=recommendations-management" class="nav-link">
            <i class="fa-solid fa-users"></i> Communaute
        </a></li>
        <li><a href="/smart_nutrition/index.php?action=planner-management" class="nav-link">
            <i class="fa-solid fa-calendar-check"></i> Planning
        </a></li>
    </ul>
    <?php endif; ?>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Sombre
        </button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p class="user-info">Connecte: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></strong></p>
            <a href="/smart_nutrition/index.php?action=logout" class="nav-link logout">
                <i class="fa-solid fa-sign-out-alt"></i> Deconnexion
            </a>
        <?php else: ?>
            <a href="/smart_nutrition/index.php?action=login" class="nav-link">
                <i class="fa-solid fa-lock"></i> Connexion
            </a>
            <a href="/smart_nutrition/index.php?action=register" class="nav-link register">
                <i class="fa-solid fa-user-plus"></i> Inscription
            </a>
        <?php endif; ?>
    </div>
</nav>
