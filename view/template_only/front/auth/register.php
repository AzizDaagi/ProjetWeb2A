<div class="container">
    <h1>Registration</h1>
    <p class="subtitle">Create an account to track your goals</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/smart_nutrition/index.php?action=register" id="register-form" novalidate>
        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Last Name</label>
            <input type="text" name="nom" id="nom-input" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" placeholder="Last name">
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>First Name</label>
            <input type="text" name="prenom" id="prenom-input" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" placeholder="First name">
        </div>

        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>Email</label>
            <input type="email" name="email" id="email-input" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="Email">
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Password</label>
            <input type="password" name="password" id="password-input" placeholder="Password">
        </div>

        <button type="submit" class="btn-accent"><i class="fa-solid fa-user-plus icon success"></i>Sign up</button>
    </form>

    <p class="link-text">Already registered? <a href="/smart_nutrition/index.php?action=login">Sign in</a></p>
</div>

<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
    let nom = document.getElementById('nom-input').value.trim();
    let prenom = document.getElementById('prenom-input').value.trim();
    let email = document.getElementById('email-input').value.trim();
    let pwd = document.getElementById('password-input').value.trim();
    
    let errors = [];

    if (nom === "") {
        errors.push("Le nom est requis.");
    }
    if (prenom === "") {
        errors.push("Le prénom est requis.");
    }
    if (email === "") {
        errors.push("L'email est requis.");
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push("L'email n'est pas valide.");
    }
    if (pwd === "") {
        errors.push("Le mot de passe est requis.");
    } else if (pwd.length < 6) {
        errors.push("Le mot de passe doit contenir au moins 6 caractères.");
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>
