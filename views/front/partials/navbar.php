<nav class="navbar">

    <div class="navbar-brand">
        <a href="index.php?controller=aliment&action=index" class="brand-link">
            <img
                src="../backOffice/style/logo.png"
                alt="Smart Nutrition"
                class="brand-logo navbar-preview-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">

            <span class="brand-fallback">
                <i class="fa-solid fa-leaf"></i> Smart Nutrition
            </span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li>
            <a href="index.php?controller=aliment&action=index" class="nav-link">
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
