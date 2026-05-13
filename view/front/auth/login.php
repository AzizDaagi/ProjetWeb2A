<?php include __DIR__ . '/../../layouts/public_brand.php'; ?>

<div class="container">
    <h1>Connexion</h1>
    <p class="subtitle">Accedez a votre espace Smart Nutrition</p>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/projetwebmalek/index.php?action=login" novalidate id="passwordLoginForm">
        <div class="field">
            <label><i class="fa-solid fa-envelope icon"></i>E-mail</label>
            <input type="email" id="loginEmail" name="email" placeholder="Entrez votre e-mail" required>
        </div>

        <div class="field">
            <label><i class="fa-solid fa-lock icon"></i>Mot de passe</label>
            <input type="password" name="password" placeholder="Mot de passe" required>
        </div>

        <div class="login-actions">
            <button type="submit"><i class="fa-solid fa-right-to-bracket icon success"></i>Se connecter</button>
            <a class="btn btn-link forgot-link" href="/projetwebmalek/index.php?action=forgot">Mot de passe oubli&eacute; ?</a>
        </div>
    </form>

    <section class="google-auth-card">
        <h2 class="google-auth-title"><i class="fa-brands fa-google icon"></i>Connexion Google</h2>
        <p class="google-auth-text">Connectez-vous en un clic avec votre compte Google.</p>
        <?php if (!empty($firebaseGoogleEnabled)): ?>
            <button type="button" id="googleLoginBtn" class="google-login-btn">
                <i class="fa-brands fa-google"></i>Continuer avec Google
            </button>
            <p id="googleLoginStatus" class="google-login-status" aria-live="polite"></p>
        <?php else: ?>
            <button type="button" class="google-login-btn" disabled aria-disabled="true">
                <i class="fa-brands fa-google"></i>Continuer avec Google
            </button>
            <p class="google-login-status"><?= htmlspecialchars($firebaseUnavailableMessage ?? 'Connexion Google indisponible en environnement local.') ?></p>
        <?php endif; ?>
    </section>

    <section
        class="face-auth-card"
        data-face-auth-mode="login"
        data-endpoint="/projetwebmalek/index.php?action=face-login"
    >
        <h2 class="face-auth-title"><i class="fa-solid fa-camera icon"></i>Connexion faciale</h2>
        <p class="face-auth-text">Saisissez votre e-mail, activez la camera puis lancez la verification faciale. L'apercu reste masque.</p>
        <p class="google-login-status">Si la camera ou la reconnaissance faciale est indisponible, utilisez simplement le mot de passe classique.</p>

        <div class="face-preview-wrap is-hidden">
            <video class="face-video" autoplay playsinline muted></video>
            <canvas class="face-canvas" aria-hidden="true"></canvas>
        </div>

        <p class="face-status" aria-live="polite">Camera inactive.</p>

        <div class="face-actions">
            <button type="button" class="face-btn face-btn-secondary js-face-start">
                <i class="fa-solid fa-video"></i>Activer la camera
            </button>
            <button type="button" class="face-btn js-face-submit" disabled>
                <i class="fa-solid fa-face-smile"></i>Se connecter avec mon visage
            </button>
        </div>
    </section>

    <p class="link-text">Pas encore de compte ? <a href="/projetwebmalek/index.php?action=register">S'inscrire</a></p>
</div>

<?php if (!empty($firebaseGoogleEnabled)): ?>
<script>
window.SMART_NUTRITION_FIREBASE_CONFIG = <?= json_encode($firebaseConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js';
import { getAuth, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/10.12.4/firebase-auth.js';

const config = window.SMART_NUTRITION_FIREBASE_CONFIG || null;
const loginButton = document.getElementById('googleLoginBtn');
const statusEl = document.getElementById('googleLoginStatus');

if (config && loginButton && statusEl) {
    const app = initializeApp(config);
    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();

    const setStatus = (message, isError = false) => {
        statusEl.textContent = message;
        statusEl.classList.toggle('is-error', isError);
    };

    loginButton.addEventListener('click', async () => {
        loginButton.disabled = true;
        setStatus('Connexion Google en cours...');

        try {
            const result = await signInWithPopup(auth, provider);
            const idToken = await result.user.getIdToken(true);

            const response = await fetch('/projetwebmalek/index.php?action=google-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ idToken })
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Connexion Google echouee.');
            }

            window.location.href = payload.redirect || '/projetwebmalek/index.php?action=home';
        } catch (error) {
            setStatus(error && error.message ? error.message : 'Connexion Google echouee.', true);
            loginButton.disabled = false;
        }
    });
}
</script>
<?php endif; ?>
