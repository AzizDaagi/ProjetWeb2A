<div class="container">
    <?php $selectedRole = ($selectedRole ?? 'user') === 'admin' ? 'admin' : 'user'; ?>
    <h1>Login</h1>
    <p class="subtitle">Access your Smart Nutrition space</p>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars(app_url('index.php?action=login')) ?>">
        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Password</label>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user-shield icon"></i>Login As</label>
            <select name="login_as" required>
                <option value="user" <?= $selectedRole === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <button type="submit"><i class="fa-solid fa-right-to-bracket icon success"></i>Sign in</button>
    </form>

    <p class="link-text">No account yet? <a href="<?= htmlspecialchars(app_url('index.php?action=register')) ?>">Sign up</a></p>
</div>
