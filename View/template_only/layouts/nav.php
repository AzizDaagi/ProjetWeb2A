<nav class="navbar">
    <div class="navbar-brand">
        <a href="<?= htmlspecialchars(route_url('template.preview', ['page' => 'front-home'])) ?>">
            <img
                src="<?= htmlspecialchars(app_url('View/template_only/2-removebg-preview.png')) ?>"
                alt="Smart Nutrition"
                class="brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li><a href="<?= htmlspecialchars(route_url('template.preview', ['page' => 'front-home'])) ?>" class="nav-link">
            <i class="fa-solid fa-user"></i> User
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('template.preview', ['page' => 'front-login'])) ?>" class="nav-link">
            <i class="fa-solid fa-chart-line"></i> Tracking
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('template.preview', ['page' => 'front-register'])) ?>" class="nav-link">
            <i class="fa-solid fa-book-open"></i> Recipes
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('template.preview', ['page' => 'back-dashboard'])) ?>" class="nav-link">
            <i class="fa-solid fa-apple-whole"></i> Fridge
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('template.preview', ['page' => 'back-users-list'])) ?>" class="nav-link">
            <i class="fa-solid fa-calendar-check"></i> Planner
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('template.preview', ['page' => 'back-users-edit'])) ?>" class="nav-link">
            <i class="fa-solid fa-user-shield"></i> Admin
        </a></li>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Toggle color mode" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Dark
        </button>
        <p class="user-info">Template preview mode</p>
    </div>
</nav>
