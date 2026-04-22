<?php ob_start(); ?>

<div class="glass-card animate-fade-in" style="max-width: 400px; margin: 5rem auto; padding: 2rem;">
    <h2 style="text-align: center; color: var(--accent); margin-bottom: 1.5rem;"><i class="fa-solid fa-lock"></i> Accès BackOffice</h2>
    
    <?php if (isset($error)): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); color: var(--error); padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
            <?php 
                if ($error === 'empty') echo "Le code est obligatoire.";
                elseif ($error === 'invalid_format') echo "Le code doit contenir exactement 6 chiffres.";
                elseif ($error === 'incorrect') echo "Code incorrect.";
            ?>
        </div>
    <?php endif; ?>

    <form id="adminLoginForm" action="index.php?action=admin_authenticate" method="POST">
        <label for="admin_code">Admin Code :</label>
        <input type="text" id="admin_code" name="admin_code" placeholder="000000" maxlength="6">
        <div id="codeError" class="error-message"></div>

        <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Valider</button>
    </form>
</div>

<script>
document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
    let isValid = true;
    const codeInput = document.getElementById('admin_code');
    const codeError = document.getElementById('codeError');
    let codeValue = codeInput.value.trim();
    
    codeInput.value = codeValue; // trim spaces before submission
    
    // Clear previous errors
    codeError.style.display = 'none';
    codeInput.classList.remove('input-error');

    if (codeValue === '') {
        codeError.textContent = "Le champ ne doit pas être vide.";
        codeError.style.display = 'block';
        codeInput.classList.add('input-error');
        isValid = false;
    } else if (!/^\d{6}$/.test(codeValue)) {
        codeError.textContent = "Le code doit contenir exactement 6 chiffres.";
        codeError.style.display = 'block';
        codeInput.classList.add('input-error');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../layout.php'; 
?>
