<?php ob_start(); ?>

<div class="animate-fade-in text-center" style="text-align: center; margin-top: 4rem;">
    <h2 style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">Bienvenue sur Smart Nutrition</h2>
    <p style="font-size: 1.2rem; color: var(--text-muted); max-width: 600px; margin: 0 auto 2rem;">
        Découvrez notre catalogue d'activités sportives intelligentes. Consultez les exercices associés et prenez votre santé en main !
    </p>
    <a href="index.php?action=activites" class="btn" style="font-size: 1.2rem; padding: 1rem 2rem;">Voir le Catalogue Public</a>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
