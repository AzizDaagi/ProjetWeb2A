<nav class="navbar">
    <div class="navbar-brand">
        <a href="<?= $basePath ?>/index.php?action=home">
            <img
                src="<?= $basePath ?>/View/assets/images/logo.png"
                alt="Smart Nutrition"
                class="brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li><a href="<?= $basePath ?>/index.php?action=activites" class="nav-link">
            <i class="fa-solid fa-chart-line"></i> Activite sportif
        </a></li>
        <li><a href="<?= $basePath ?>/index.php?action=nutrition_request" class="nav-link">
            <i class="fa-solid fa-file-waveform"></i> Bilan nutritionnel
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-book-open"></i> Recette alimentation
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-apple-whole"></i> Ecommerce
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-users"></i> Communaute
        </a></li>
        <li><a href="#" class="nav-link">
            <i class="fa-solid fa-calendar-check"></i> Suivi nutritionnel
        </a></li>
        <li><a href="<?= $basePath ?>/index.php?action=admin_login" class="nav-link">
            <i class="fa-solid fa-shield-halved"></i> Admin
        </a></li>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Sombre
        </button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p class="user-info">Connecte: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></strong></p>
            <a href="<?= $basePath ?>/index.php?action=logout" class="nav-link logout">
                <i class="fa-solid fa-sign-out-alt"></i> Deconnexion
            </a>
        <?php else: ?>
            <a href="<?= $basePath ?>/index.php?action=login" class="nav-link">
                <i class="fa-solid fa-lock"></i> Connexion
            </a>
            <a href="<?= $basePath ?>/index.php?action=register" class="nav-link register">
                <i class="fa-solid fa-user-plus"></i> Inscription
            </a>
        <?php endif; ?>
    </div>
</nav>
