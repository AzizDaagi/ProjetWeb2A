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
        <li><a href="/projetwebmalek/index.php?action=profile" class="nav-link">
            <i class="fa-solid fa-user"></i> User
        </a></li>
        <li><a href="/projetwebmalek/index.php?action=tracking-management" class="nav-link">
            <i class="fa-solid fa-chart-line"></i> Activite sportif
        </a></li>
        <li><a href="/projetwebmalek/view/frontoffice/liste_recettes.php" class="nav-link">
            <i class="fa-solid fa-book-open"></i> Recette alimentation
        </a></li>
        <li><a href="/projetwebmalek/index.php?action=foods-management" class="nav-link">
            <i class="fa-solid fa-apple-whole"></i> Ecommerce
        </a></li>
        <li><a href="/projetwebmalek/index.php?action=recommendations-management" class="nav-link">
            <i class="fa-solid fa-users"></i> Communaute
        </a></li>
        <li><a href="/projetwebmalek/index.php?action=planner-management" class="nav-link">
            <i class="fa-solid fa-calendar-check"></i> Planning
        </a></li>
        <?php if (($_SESSION['user_role'] ?? 'user') === 'admin'): ?>
        <li><a href="/projetwebmalek/view/backoffice/index.php" class="nav-link" style="color: #ffc107;">
            <i class="fa-solid fa-user-shield"></i> Admin Backoffice
        </a></li>
        <li><a href="/projetwebmalek/index.php?action=roles-list" class="nav-link">
            <i class="fa-solid fa-user-tag"></i> Roles
        </a></li>
        <?php endif; ?>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Sombre
        </button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p class="user-info">Connecte: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong></p>
            <a href="/projetwebmalek/index.php?action=logout" class="nav-link logout">
                <i class="fa-solid fa-sign-out-alt"></i> Deconnexion
            </a>
        <?php else: ?>
            <a href="/projetwebmalek/index.php?action=login" class="nav-link">
                <i class="fa-solid fa-lock"></i> Connexion
            </a>
            <a href="/projetwebmalek/index.php?action=register" class="nav-link register">
                <i class="fa-solid fa-user-plus"></i> Inscription
            </a>
        <?php endif; ?>
    </div>
</nav>
