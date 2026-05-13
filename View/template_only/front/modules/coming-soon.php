<div class="container">
    <h1><i class="fa-solid fa-gears"></i> <?= htmlspecialchars($moduleTitle ?? 'Module') ?></h1>
    <p class="subtitle">Feature roadmap</p>

    <p style="text-align:center; line-height:1.8; margin-bottom:18px;">
        <?= htmlspecialchars($moduleDescription ?? 'This module will be available soon.') ?>
    </p>

    <div class="actions">
        <a class="btn" href="/smart_nutrition/index.php?action=home">
            <i class="fa-solid fa-home"></i> Back to Home
        </a>
    </div>
</div>
