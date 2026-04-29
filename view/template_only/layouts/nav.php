<nav class="navbar">
    <div class="navbar-brand">
        <a href="/projetwebmalek/view/frontoffice/liste_recettes.php">
            <img
                src="/projetwebmalek/view/template_only/2-removebg-preview.png"
                alt="Smart Nutrition"
                class="brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li><a href="/projetwebmalek/view/frontoffice/liste_recettes.php" class="nav-link">
            <i class="fa-solid fa-house"></i> Home
        </a></li>
        <li><a href="/projetwebmalek/view/backoffice/manage_aliments.php" class="nav-link">
            <i class="fa-solid fa-plus"></i> Submit Product
        </a></li>
        <li><a href="/projetwebmalek/view/frontoffice/liste_recettes.php" class="nav-link">
            <i class="fa-solid fa-book-open"></i> My Orders
        </a></li>
        <?php if (($_SESSION['user_role'] ?? 'user') === 'admin'): ?>
        <li><a href="/projetwebmalek/view/backoffice/manage_aliments.php" class="nav-link">
            <i class="fa-solid fa-apple-whole"></i> Admin Products
        </a></li>
        <li><a href="/projetwebmalek/view/backoffice/index.php" class="nav-link">
            <i class="fa-solid fa-user-shield"></i> Admin Orders
        </a></li>
        <li><a href="/projetwebmalek/view/backoffice/manage_recommandations.php" class="nav-link">
            <i class="fa-solid fa-hourglass-half"></i> Pending
        </a></li>
        <?php endif; ?>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle"
                aria-label="Toggle dark/light mode" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Dark
        </button>
        <span class="nav-workspace-badge">
            <i class="fa-solid fa-layer-group"></i> Product management workspace
        </span>
    </div>
</nav>
