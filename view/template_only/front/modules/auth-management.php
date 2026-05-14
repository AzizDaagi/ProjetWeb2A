<?php $baseUrl = $baseUrl ?? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<div class="container">
    <h1><i class="fa-solid fa-shield-halved"></i> Authentication Management</h1>
    <p class="subtitle">Quick access to authentication actions.</p>

    <div class="actions">
        <a class="btn" href="<?= $baseUrl ?>/index.php?action=login">
            <i class="fa-solid fa-lock"></i> Login
        </a>
        <a class="btn" href="<?= $baseUrl ?>/index.php?action=register">
            <i class="fa-solid fa-user-plus"></i> Register
        </a>
        <a class="btn btn-accent" href="/projet-web-25-26/index.php?action=logout">
            <i class="fa-solid fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
