<div class="container">
    <h1><i class="fa-solid fa-shield-halved"></i> Authentication Management</h1>
    <p class="subtitle">Quick access to authentication actions.</p>

    <div class="actions">
        <a class="btn" href="<?= htmlspecialchars(app_url('index.php?action=login')) ?>">
            <i class="fa-solid fa-lock"></i> Login
        </a>
        <a class="btn" href="<?= htmlspecialchars(app_url('index.php?action=register')) ?>">
            <i class="fa-solid fa-user-plus"></i> Register
        </a>
        <a class="btn btn-accent" href="<?= htmlspecialchars(app_url('index.php?action=logout')) ?>">
            <i class="fa-solid fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
