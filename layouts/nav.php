<nav class="navbar">
    <div class="navbar-brand">
        <a href="<?= htmlspecialchars(app_url('index.php')) ?>">
            <img
                src="<?= htmlspecialchars(app_url('2-removebg-preview.png')) ?>"
                alt="Smart Nutrition"
                class="brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li><a href="<?= htmlspecialchars(app_url('index.php')) ?>" class="nav-link">
            <i class="fa-solid fa-house"></i> Home
        </a></li>
        <li><a href="<?= htmlspecialchars(app_url('index.php?action=frontCreate')) ?>" class="nav-link">
            <i class="fa-solid fa-plus"></i> Submit Product
        </a></li>
        <li><a href="<?= htmlspecialchars(app_url('index.php?action=backList')) ?>" class="nav-link">
            <i class="fa-solid fa-boxes-stacked"></i> Admin Products
        </a></li>
        <li><a href="<?= htmlspecialchars(app_url('index.php?action=pending')) ?>" class="nav-link">
            <i class="fa-solid fa-hourglass-half"></i> Pending
        </a></li>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Toggle color mode" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Dark
        </button>
        <p class="user-info">Product management workspace</p>
    </div>
</nav>
