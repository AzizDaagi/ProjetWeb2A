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

    <form method="POST" action="/smart_nutrition/index.php?action=register">
        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>Last Name</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" placeholder="Last name" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-user icon"></i>First Name</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" placeholder="First name" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="Email" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Password</label>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" class="btn-accent"><i class="fa-solid fa-user-plus icon success"></i>Sign up</button>
    </form>

    <p class="link-text">Already registered? <a href="/smart_nutrition/index.php?action=login">Sign in</a></p>
</div>
