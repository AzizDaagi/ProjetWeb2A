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

    <form method="POST" action="/smart_nutrition/index.php?action=login" id="login-form" novalidate>
        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>Email</label>
            <input type="email" name="email" id="email-input" placeholder="Enter your email">
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Password</label>
            <input type="password" name="password" id="password-input" placeholder="Password">
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user-shield icon"></i>Login As</label>
            <select name="login_as" id="loginas-input">
                <option value="user" <?= $selectedRole === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <button type="submit"><i class="fa-solid fa-right-to-bracket icon success"></i>Sign in</button>
    </form>

    <p class="link-text">No account yet? <a href="/smart_nutrition/index.php?action=register">Sign up</a></p>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
    let email = document.getElementById('email-input').value.trim();
    let pwd = document.getElementById('password-input').value.trim();
    let role = document.getElementById('loginas-input').value;
    
    let errors = [];

    if (email === "") {
        errors.push("L'email est requis.");
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push("L'email n'est pas valide.");
    }
    
    if (pwd === "") {
        errors.push("Le mot de passe est requis.");
    }

    if (role === "") {
        errors.push("Veuillez sélectionner un rôle.");
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>
