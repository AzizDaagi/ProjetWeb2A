<?php $baseUrl = $baseUrl ?? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<div class="public-brand-block">
    <a href="<?= $baseUrl ?>/index.php?action=login" class="public-brand-link">
        <img
            src="<?= $baseUrl ?>/view/assets/images/logo.png"
            alt="Smart Nutrition"
            class="public-brand-logo"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
        >
        <span class="public-brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
    </a>
    <p class="public-brand-tagline">Votre plateforme de nutrition intelligente</p>
</div>
