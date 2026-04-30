<nav class="navbar">

    <div class="navbar-brand">
        <a
            href="index.php?controller=suivi&action=index"
            class="brand-link"
            style="background: transparent !important; box-shadow: none !important; border: 0 !important; border-radius: 0 !important; padding: 0 !important;">
            <img
                src="/projet-web-25-26/public/backoffice/images/smart-nutrition-logo.png"
                alt="Smart Nutrition"
                class="brand-logo navbar-preview-logo"
                style="background: transparent !important; box-shadow: none !important; border: 0 !important; border-radius: 0 !important;"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">

            <span class="brand-fallback">
                <i class="fa-solid fa-leaf"></i> Smart Nutrition
            </span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li>
            <a href="index.php?controller=suivi&action=index" class="nav-link">
                <i class="fa-solid fa-fire"></i> Suivi Nutritionnel
            </a>
        </li>

        <li>
            <a href="index.php?controller=objectif&action=index" class="nav-link">
                <i class="fa-solid fa-bullseye"></i> Objectif
            </a>
        </li>

        <li>
            <a href="index.php?controller=backoffice&action=dashboard" class="nav-link">
                <i class="fa-solid fa-gear"></i> Back Office
            </a>
        </li>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle">
            <i class="fa-solid fa-moon"></i> Dark
        </button>
    </div>

</nav>
