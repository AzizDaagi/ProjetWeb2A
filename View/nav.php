<nav class="navbar">
    <div class="navbar-brand">
        <a href="<?= htmlspecialchars(route_url('home')) ?>">
            <img
                src="<?= htmlspecialchars(asset_url('images/brand-logo.png')) ?>"
                alt="Smart Nutrition"
                class="brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li><a href="<?= htmlspecialchars(route_url('home')) ?>" class="nav-link">
            <i class="fa-solid fa-house"></i> Home
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('cart.view')) ?>" class="nav-link" style="position: relative;">
            <i class="fa-solid fa-cart-shopping"></i> Cart
            <?php 
                $cartCount = count($_SESSION['shopping_cart']['items'] ?? []);
                if ($cartCount > 0): 
            ?>
            <span style="position: absolute; top: -8px; right: -8px; background: var(--accent); color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
                <?= $cartCount ?>
            </span>
            <?php endif; ?>
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('product.create')) ?>" class="nav-link">
            <i class="fa-solid fa-plus"></i> Submit Product
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('order.list')) ?>" class="nav-link">
            <i class="fa-solid fa-receipt"></i> My Orders
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('admin.products')) ?>" class="nav-link">
            <i class="fa-solid fa-boxes-stacked"></i> Admin Products
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('admin.orders')) ?>" class="nav-link">
            <i class="fa-solid fa-clipboard-list"></i> Admin Orders
        </a></li>
        <li><a href="<?= htmlspecialchars(route_url('admin.products.pending')) ?>" class="nav-link">
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
