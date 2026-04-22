<?php
$pageTitle = $pageTitle ?? 'Smart Nutrition - FitTrack';
$showNav = $showNav ?? true;
$showFooter = $showFooter ?? true;

require_once __DIR__ . '/layouts/header.php';
?>

<style>
    :root {
        --card-glass: rgba(30, 41, 59, 0.7);
        --card-border: rgba(255, 255, 255, 0.1);
        --primary-hover: #0284c7;
        --error: #ef4444;
    }

    .glass-card {
        background: var(--card-glass); backdrop-filter: blur(12px); border: 1px solid var(--card-border);
        border-radius: 16px; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-outline {
        background: transparent; border: 1px solid var(--primary); color: var(--primary); box-shadow: none;
    }

    .custom-backoffice-wrapper input[type="text"], 
    .custom-backoffice-wrapper input[type="number"], 
    .custom-backoffice-wrapper textarea,
    .custom-backoffice-wrapper select {
        width: 100%; padding: 0.75rem; margin-bottom: 0.5rem; background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--card-border); border-radius: 8px; color: #fff; font-family: inherit;
    }

    .custom-backoffice-wrapper .btn {
        display: inline-block; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600;
        text-decoration: none; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(56, 189, 248, 0.3);
    }

    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<div class="custom-backoffice-wrapper" style="max-width: 1200px; margin: 0 auto; width: 100%; padding: 2rem 1rem;">
    <?php echo $content ?? ''; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
require_once __DIR__ . '/layouts/footer.php';
?>
