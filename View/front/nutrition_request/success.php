<?php ob_start(); ?>

<div class="animate-fade-in" style="max-width: 800px; margin: 0 auto; padding: 5rem 1rem; text-align: center;">
    <div class="glass-card" style="padding: 3rem;">
        <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: #10b981; margin-bottom: 1.5rem;"></i>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">Demande soumise avec succès !</h2>
        <p style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 2rem;">
            Votre demande a bien été reçue. Notre administrateur va l'examiner et vous assigner un programme personnalisé (Activités & Exercices).
        </p>
        <a href="index.php?action=home" class="btn" style="display: inline-block; padding: 0.75rem 2rem; font-size: 1.1rem;">Retour à l'accueil</a>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../../View/layout.php'; 
?>
