<nav class="navbar" style="padding: 10px 16px; min-height: 100px;">
    <div class="navbar-brand">
        <a href="/smart_nutrition/index.php?action=home" style="display: flex; align-items: center;">
            <img
                src="/smart_nutrition/2-removebg-preview.png"
                alt="Smart Nutrition"
                class="brand-logo"
                style="height: 100px; width: auto; object-fit: contain; margin-right: 20px; transition: 0.3s;"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback" style="font-size: 1.5rem;"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li><a href="/smart_nutrition/index.php?action=activites" class="nav-link">
            <i class="fa-solid fa-dumbbell"></i> Activité Sportif
        </a></li>
        <li><a href="/smart_nutrition/index.php?action=nutrition_request" class="nav-link" style="color: #34d399;">
            <i class="fa-solid fa-file-waveform"></i> Bilan Nutritionnel
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-utensils"></i> Recettes
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-cart-shopping"></i> eCommerce
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-users"></i> Communauté
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-calendar"></i> Planning
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-user-gear"></i> Utilisateurs
        </a></li>
        <li><a href="/smart_nutrition/index.php?action=admin_login" class="nav-link" style="color: var(--accent); border-color: var(--accent);">
            <i class="fa-solid fa-shield-halved"></i> Admin
        </a></li>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Toggle color mode" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Dark
        </button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p class="user-info">Signed in: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong></p>
            <a href="/smart_nutrition/index.php?action=logout" class="nav-link logout">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="/smart_nutrition/index.php?action=login" class="nav-link">
                <i class="fa-solid fa-lock"></i> Login
            </a>
            <a href="/smart_nutrition/index.php?action=register" class="nav-link register">
                <i class="fa-solid fa-user-plus"></i> Register
            </a>
        <?php endif; ?>
    </div>
</nav>
