<?php ob_start(); ?>

<div class="animate-fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: var(--primary);">Tableau de Bord - Admin</h2>
        <div>
            <a href="index.php?action=admin_requests" class="btn" style="background: #38bdf8; margin-right: 1rem;"><i class="fa-solid fa-file-waveform"></i> Gérer Requêtes Nutrition</a>
            <a href="index.php?action=admin_logout" class="btn" style="background: var(--error);">Déconnexion</a>
        </div>
    </div>

    <!-- Widgets -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="glass-card" style="text-align: center;">
            <h3 style="color: var(--text-muted); font-size: 1rem;"><i class="fa-solid fa-person-running"></i> Total Activités</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?= $stats['total_activities'] ?></p>
        </div>
        <div class="glass-card" style="text-align: center;">
            <h3 style="color: var(--text-muted); font-size: 1rem;"><i class="fa-solid fa-dumbbell"></i> Total Exercices</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: var(--accent);"><?= $stats['total_exercises'] ?></p>
        </div>
        <div class="glass-card" style="text-align: center;">
            <h3 style="color: var(--text-muted); font-size: 1rem;"><i class="fa-solid fa-fire"></i> Total Calories</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #fbbf24;"><?= $stats['total_calories'] ?></p>
        </div>
        <div class="glass-card" style="text-align: center;">
            <h3 style="color: var(--text-muted); font-size: 1rem;"><i class="fa-solid fa-clock"></i> Durée Moyenne</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #a855f7;"><?= $stats['avg_duration'] ?> min</p>
        </div>
        <div class="glass-card" style="text-align: center;">
            <h3 style="color: var(--text-muted); font-size: 1rem;"><i class="fa-solid fa-star"></i> Activité Populaire</h3>
            <p style="font-size: 1.5rem; font-weight: 700; color: #34d399; margin-top: 10px;"><?= htmlspecialchars($stats['popular_activity']) ?></p>
        </div>
        <div class="glass-card" style="text-align: center;">
            <h3 style="color: var(--text-muted); font-size: 1rem;"><i class="fa-solid fa-child"></i> Muscle Cible</h3>
            <p style="font-size: 1.5rem; font-weight: 700; color: #f472b6; margin-top: 10px;"><?= htmlspecialchars($stats['most_targeted_muscle']) ?></p>
        </div>
    </div>

    <!-- Gestion CRUD Activités -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; margin-top: 3rem; border-top: 1px solid var(--card-border); padding-top: 2rem;">
        <h2 style="color: var(--primary);">Gestion des Activités & Exercices</h2>
        <a href="#add-form" class="btn">Nouvelle Activité +</a>
    </div>

    <?php if(isset($error) && !empty($error)): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #fca5a5;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <?php if(empty($activites)): ?>
            <p style="color: var(--text-muted);">Aucune activité enregistrée. Commencez par en ajouter une !</p>
        <?php else: ?>
            <?php foreach($activites as $act): ?>
                <div class="glass-card">
                    <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?= htmlspecialchars($act['nom_activite']) ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.95rem;"><?= htmlspecialchars($act['description']) ?></p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px;">⏱ <?= $act['duree_minutes'] ?> min</span>
                        <span style="background: rgba(244, 63, 94, 0.2); color: var(--accent); padding: 4px 8px; border-radius: 4px;">🔥 <?= $act['calories_brulees'] ?> kcal</span>
                    </div>
                    <a href="index.php?action=admin_show&id=<?= $act['id_activite'] ?>" class="btn btn-outline" style="width: 100%; text-align: center; margin-bottom: 0.5rem;">Gérer Détails & Exercices</a>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="index.php?action=editActivite&id=<?= $act['id_activite'] ?>" class="btn btn-outline" style="flex: 1; text-align: center; padding: 0.5rem; font-size: 0.85rem; border-color: #f59e0b; color: #f59e0b;">✎ Modifier</a>
                        <a href="index.php?action=deleteActivite&id=<?= $act['id_activite'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette activité et tous ses exercices ?');" class="btn btn-outline" style="flex: 1; text-align: center; padding: 0.5rem; font-size: 0.85rem; border-color: var(--error); color: var(--error);">🗑 Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- FORMULAIRE SANS HTML5 VAL -->
    <div class="glass-card" id="add-form" style="max-width: 600px; margin: 0 auto;">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border); padding-bottom: 0.5rem;">[Admin] Ajouter une Activité</h3>
        <form action="index.php?action=createActivite" method="POST" class="js-validate-activite" novalidate>
            <div>
                <label>Nom de l'activité</label>
                <input type="text" name="nom_activite" placeholder="ex: Séance Musculation Haut du Corps">
                <div class="error-message nom-error">Le nom est requis.</div>
            </div>
            <div>
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Détails de la séance..."></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label>Durée (minutes)</label>
                    <input type="text" name="duree_minutes">
                    <div class="error-message duree-error">Doit être un nombre entier positif.</div>
                </div>
                <div>
                    <label>Calories estimées brûlées</label>
                    <input type="text" name="calories_brulees">
                    <div class="error-message cal-error">Doit être un nombre entier positif.</div>
                </div>
            </div>
            <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Enregistrer l'activité</button>
        </form>
    </div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layout.php'; 
?>
