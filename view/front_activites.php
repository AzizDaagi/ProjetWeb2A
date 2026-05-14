<?php ob_start(); ?>

<style>
    #history-container {
        display: none;
        margin-top: 2rem;
        animation: slideDown 0.4s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .btn-demandes {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.6rem;
        border-radius: 50px;
        border: 1.5px solid rgba(56, 189, 248, 0.6);
        background: rgba(56, 189, 248, 0.08);
        color: var(--primary);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .btn-demandes:hover {
        background: rgba(56, 189, 248, 0.2);
        border-color: var(--primary);
        box-shadow: 0 0 16px rgba(56, 189, 248, 0.25);
    }
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.4rem;
        border-radius: 50px;
        border: 1.5px solid rgba(255,255,255,0.15);
        background: rgba(255,255,255,0.05);
        color: var(--text-muted);
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    .btn-back:hover {
        background: rgba(255,255,255,0.12);
        color: #fff;
    }
</style>

<div class="animate-fade-in" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">

    <!-- ===== SECTION 1: Catalogue des Activités ===== -->
    <div class="glass-card" style="padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h2 style="color: var(--primary); margin: 0;"><i class="fa-solid fa-person-running"></i> Catalogue des Activités</h2>
            
            <form action="index.php" method="GET" style="display: flex; gap: 0.5rem; flex-grow: 1; max-width: 600px;">
                <input type="hidden" name="action" value="activites">
                <input type="text" name="search" placeholder="Rechercher par nom..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                       style="flex-grow: 1; padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff;">
                
                <select name="sort" onchange="this.form.submit()" 
                        style="padding: 0.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff;">
                    <option value="name_asc" <?= ($_GET['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>Nom (A-Z)</option>
                    <option value="name_desc" <?= ($_GET['sort'] ?? '') == 'name_desc' ? 'selected' : '' ?>>Nom (Z-A)</option>
                    <option value="calories_desc" <?= ($_GET['sort'] ?? '') == 'calories_desc' ? 'selected' : '' ?>>Plus de calories</option>
                    <option value="duration_desc" <?= ($_GET['sort'] ?? '') == 'duration_desc' ? 'selected' : '' ?>>Plus longue durée</option>
                </select>
                
                <button type="submit" class="btn" style="padding: 0.5rem 1.2rem; border-radius: 20px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

            <a href="index.php?action=export_activite_pdf&search=<?= urlencode($_GET['search'] ?? '') ?>&sort=<?= urlencode($_GET['sort'] ?? 'name_asc') ?>" 
               class="btn" style="background: var(--secondary); padding: 0.5rem 1.2rem; border-radius: 20px;">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 4rem;">
        <?php if(empty($activites)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <p style="color: var(--text-muted);">Aucune activité trouvée pour votre recherche.</p>
            </div>
        <?php else: ?>
            <?php foreach($activites as $act): ?>
                <div class="glass-card" style="padding: 1.5rem; display: flex; flex-direction: column;">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($act['nom_activite']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; flex-grow: 1;">
                        <?= htmlspecialchars($act['description']) ?>
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px;"><i class="fa-regular fa-clock"></i> <?= $act['duree_minutes'] ?> min</span>
                        <span style="background: rgba(244, 63, 94, 0.2); color: var(--accent); padding: 4px 8px; border-radius: 4px;"><i class="fa-solid fa-fire"></i> <?= $act['calories_brulees'] ?> kcal</span>
                    </div>
                    <a href="index.php?action=showExercices&id=<?= $act['id_activite'] ?>" class="btn btn-outline" style="width: 100%; text-align: center;">Consulter les Exercices</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ===== SECTION 2: Demande de Programme Nutritionnel (Always visible) ===== -->
    <div id="nutrition-section">
        <iframe 
            id="nutrition-iframe"
            src="index.php?action=nutrition_request&embed=true" 
            style="width: 100%; min-height: 950px; border: none; background: transparent; display: block;" 
            allowtransparency="true"
            title="Demande de Programme Nutritionnel">
        </iframe>
    </div>

    <!-- ===== SECTION 3: Mes Demandes (Hidden by default, toggled via JS) ===== -->
    <div id="history-container">
        <iframe 
            id="history-iframe"
            src=""
            data-src="index.php?action=my_nutrition_requests&embed=true"
            style="width: 100%; min-height: 900px; border: none; background: transparent; display: block;" 
            allowtransparency="true"
            title="Mes Demandes de Programme">
        </iframe>
    </div>

</div>

<script>
var _historyScrolled = false; // flag: only scroll once per open

function showHistory() {
    var historyEl  = document.getElementById('history-container');
    var iframe     = document.getElementById('history-iframe');

    // Lazy-load the iframe only on first click
    if (!iframe.src || iframe.src === window.location.href) {
        iframe.src = iframe.dataset.src;
    }

    historyEl.style.display = 'block';

    // Scroll only once when the user first opens the section
    if (!_historyScrolled) {
        _historyScrolled = true;
        setTimeout(function() {
            historyEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 150);
    }
}

function toggleHistory() {
    var historyEl   = document.getElementById('history-container');
    var nutritionEl = document.getElementById('nutrition-section');

    if (historyEl.style.display === 'block') {
        // Hide and reset scroll flag so next open scrolls again
        historyEl.style.display = 'none';
        _historyScrolled = false;
        nutritionEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        showHistory();
    }
}

// Listen for postMessage from the nutrition iframes
window.addEventListener('message', function(event) {
    if (event.data === 'show_history') {
        showHistory();
    }
    // Auto-resize the nutrition iframe — NO scrolling side-effect
    if (event.data && event.data.type === 'resize_nutrition') {
        var nIframe = document.getElementById('nutrition-iframe');
        if (nIframe && event.data.height) {
            nIframe.style.height = (event.data.height + 60) + 'px';
        }
    }
    // Auto-resize the history iframe — NO scrolling side-effect
    if (event.data && event.data.type === 'resize_history') {
        var hIframe = document.getElementById('history-iframe');
        if (hIframe && event.data.height) {
            hIframe.style.height = (event.data.height + 40) + 'px';
        }
    }
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/layout.php'; 
?>
