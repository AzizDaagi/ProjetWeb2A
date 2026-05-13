<?php
$pageTitle = 'Backoffice - Tableau de bord';

require_once __DIR__ . '/../../controler/RecetteController.php';
require_once __DIR__ . '/../../controler/AlimentController.php';
require_once __DIR__ . '/../../controler/UserController.php';

$recetteController = new RecetteController();
$alimentController = new AlimentController();
$userController = new UserController();

// Handle deletions from the dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_recette' && isset($_POST['id'])) {
        $recetteController->deleteRecette($_POST['id']);
        header('Location: index.php');
        exit;
    } elseif ($_POST['action'] === 'delete_aliment' && isset($_POST['id'])) {
        $alimentController->deleteAliment($_POST['id']);
        header('Location: index.php');
        exit;
    }
}

$totalRecettes = $recetteController->countRecettes();
$totalAliments = $alimentController->countAliments();
$totalUsers = $userController->countUsers();

$allUsers = $userController->listUsers();
$recentUsers = array_slice(array_reverse($allUsers), 0, 5);

$allRecettes = $recetteController->listRecettes();
$allAliments = $alimentController->listAliments();

require_once __DIR__ . '/../template_only/layouts/admin_header.php'; 
?>

<div class="admin-page">
    <div class="admin-page-head">
        <h1><i class="fa-solid fa-gauge-high icon"></i> Dashboard</h1>
        <p class="subtitle">Vue d'ensemble de l'activité admin avec le nouveau template WeTransfer.</p>
    </div>

    <div class="admin-dashboard-layout">
        <section class="admin-widget admin-kpi-widget admin-kpi-primary">
            <div class="admin-kpi-grid">
                <article class="kpi-card">
                    <p>Utilisateurs</p>
                    <strong><?= htmlspecialchars((string) $totalUsers) ?></strong>
                    <i class="fa-solid fa-users" style="color: #3498db;"></i>
                </article>
                <article class="kpi-card">
                    <p>Recettes</p>
                    <strong><?= htmlspecialchars((string) $totalRecettes) ?></strong>
                    <i class="fa-solid fa-book-open" style="color: #2ecc71;"></i>
                </article>
                <article class="kpi-card">
                    <p>Aliments</p>
                    <strong><?= htmlspecialchars((string) $totalAliments) ?></strong>
                    <i class="fa-solid fa-apple-whole" style="color: #e74c3c;"></i>
                </article>
                <article class="kpi-card">
                    <p>Recommandations</p>
                    <strong>--</strong>
                    <i class="fa-solid fa-heart-pulse" style="color: #f39c12;"></i>
                </article>
            </div>
        </section>

        <section class="admin-widget admin-widget-wide admin-evolution-widget">
            <div class="admin-widget-head">
                <h2>Répartition du Contenu</h2>
                <i class="fa-solid fa-ellipsis"></i>
            </div>
            <div class="admin-pie-widget">
                <!-- Using conic gradient to represent the breakdown visually -->
                <div class="admin-pie-chart" style="--pie-gradient: conic-gradient(#3498db 0 33%, #2ecc71 33% 66%, #e74c3c 66% 100%);">
                    <div class="admin-pie-core">
                        <strong>Total</strong>
                        <span><?= htmlspecialchars((string) ($totalUsers + $totalRecettes + $totalAliments)) ?></span>
                    </div>
                </div>
                <div class="chart-legend admin-pie-legend">
                    <span>
                        <i class="legend-dot" style="background: #3498db;"></i>
                        Utilisateurs (<?= htmlspecialchars((string) $totalUsers) ?>)
                    </span>
                    <span>
                        <i class="legend-dot" style="background: #2ecc71;"></i>
                        Recettes (<?= htmlspecialchars((string) $totalRecettes) ?>)
                    </span>
                    <span>
                        <i class="legend-dot" style="background: #e74c3c;"></i>
                        Aliments (<?= htmlspecialchars((string) $totalAliments) ?>)
                    </span>
                </div>
            </div>
        </section>
    </div>

    <!-- Utilisateurs -->
    <section class="admin-widget admin-recent-widget" style="margin-top: 30px;">
        <div class="admin-widget-head">
            <h2>Utilisateurs récents</h2>
            <a href="manage_users.php" class="btn-edit">Voir tout</a>
        </div>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>E-mail</th>
                    <th>Rôle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $userRow): ?>
                        <tr>
                            <td>#<?= htmlspecialchars((string) ($userRow['id'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($userRow['nom'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($userRow['email'] ?? '')) ?></td>
                            <td>
                                <span style="padding: 3px 8px; border-radius: 4px; font-size: 0.85em; background: <?= ($userRow['role'] === 'admin') ? '#dc3545' : '#2ecc71' ?>; color: white;">
                                    <?= htmlspecialchars((string) ($userRow['role'] ?? 'user')) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 30px; color: #7f8c8d;">Aucun utilisateur récent</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Recettes -->
    <section class="admin-widget admin-recent-widget" style="margin-top: 30px;">
        <div class="admin-widget-head">
            <h2>Liste des Recettes</h2>
            <div style="display: flex; gap: 10px;">
                <input type="text" id="searchRecettes" placeholder="Rechercher une recette..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: white;">
                <button type="button" id="sortRecettesCalories" class="btn-admin-secondary" style="font-size:13px;">
                    <i class="fa-solid fa-arrow-down-short-wide"></i> Tri Calories
                </button>
                <a href="manage_recettes.php" class="btn-admin" style="background: #2ecc71; color: white;"><i class="fa-solid fa-plus"></i> Ajouter</a>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="users-table">
                <thead>
                    <tr>
                        <th data-sort="id">ID</th>
                        <th data-sort="nom">Nom</th>
                        <th data-sort="diff">Difficulté</th>
                        <th data-sort="temps">Temps</th>
                        <th data-sort="calories">Calories</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="recettesTableBody">
                    <?php if (!empty($allRecettes)): ?>
                        <?php foreach ($allRecettes as $recette): 
                            // Calculate total calories
                            $alimentsRecette = $recetteController->getAlimentsByRecette($recette['id']);
                            $totalCal = 0;
                            foreach ($alimentsRecette as $al) {
                                $totalCal += floatval($al['calories']);
                            }
                        ?>
                            <tr class="recette-row" data-nom="<?= htmlspecialchars(strtolower((string)$recette['nom'])) ?>" data-calories="<?= $totalCal ?>">
                                <td>#<?= htmlspecialchars((string) $recette['id']) ?></td>
                                <td><?= htmlspecialchars((string) $recette['nom']) ?></td>
                                <td>
                                    <span style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 4px 8px; border-radius: 4px; font-size: 0.85em;">
                                        <?= htmlspecialchars((string) $recette['niveau_difficulte']) ?>
                                    </span>
                                </td>
                                <td><i class="fa-regular fa-clock" style="opacity:0.5;margin-right:5px;"></i><?= htmlspecialchars((string) $recette['temps_preparation']) ?></td>
                                <td><i class="fa-solid fa-fire" style="color: #e74c3c; opacity:0.8;margin-right:5px;"></i><?= $totalCal ?> kcal</td>
                                <td style="text-align:right;">
                                    <a href="manage_recettes.php?edit_id=<?= $recette['id'] ?>" class="btn-edit" style="border: none; cursor: pointer; padding: 8px 12px; margin-right: 5px;">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('Supprimer cette recette ?');">
                                        <input type="hidden" name="action" value="delete_recette">
                                        <input type="hidden" name="id" value="<?= $recette['id'] ?>">
                                        <button type="submit" class="btn-delete-user" style="background: transparent; color: #e74c3c; border: none; cursor: pointer; padding: 8px 12px;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 30px; color: #7f8c8d;">Aucune recette enregistrée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Aliments -->
    <section class="admin-widget admin-recent-widget" style="margin-top: 30px;">
        <div class="admin-widget-head">
            <h2>Liste des Aliments</h2>
            <div style="display: flex; gap: 10px;">
                <input type="text" id="searchAliments" placeholder="Rechercher un aliment..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: white;">
                <button type="button" id="sortAlimentsCategory" class="btn-admin-secondary" style="font-size:13px;">
                    <i class="fa-solid fa-layer-group"></i> Tri Catégorie
                </button>
                <a href="manage_aliments.php" class="btn-admin" style="background: #2ecc71; color: white;"><i class="fa-solid fa-plus"></i> Ajouter</a>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Calories</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="alimentsTableBody">
                    <?php if (!empty($allAliments)): ?>
                        <?php foreach ($allAliments as $aliment): ?>
                            <tr class="aliment-row" data-nom="<?= htmlspecialchars(strtolower((string)$aliment['nom'])) ?>" data-category="<?= htmlspecialchars(strtolower((string)$aliment['type'])) ?>">
                                <td>#<?= htmlspecialchars((string) $aliment['id']) ?></td>
                                <td><?= htmlspecialchars((string) $aliment['nom']) ?></td>
                                <td>
                                    <span style="background: rgba(52, 152, 219, 0.2); color: #3498db; padding: 4px 8px; border-radius: 4px; font-size: 0.85em;">
                                        <?= htmlspecialchars((string) $aliment['type']) ?>
                                    </span>
                                </td>
                                <td><i class="fa-solid fa-fire" style="color: #e74c3c; opacity:0.8;margin-right:5px;"></i><?= htmlspecialchars((string) $aliment['calories']) ?> kcal</td>
                                <td style="text-align:right;">
                                    <a href="manage_aliments.php?edit_id=<?= $aliment['id'] ?>" class="btn-edit" style="border: none; cursor: pointer; padding: 8px 12px; margin-right: 5px;">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('Supprimer cet aliment ?');">
                                        <input type="hidden" name="action" value="delete_aliment">
                                        <input type="hidden" name="id" value="<?= $aliment['id'] ?>">
                                        <button type="submit" class="btn-delete-user" style="background: transparent; color: #e74c3c; border: none; cursor: pointer; padding: 8px 12px;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 30px; color: #7f8c8d;">Aucun aliment enregistré</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- Recherche Recettes ---
    const searchRecettes = document.getElementById("searchRecettes");
    const recettesRows = document.querySelectorAll(".recette-row");
    
    searchRecettes.addEventListener("input", function(e) {
        const term = e.target.value.toLowerCase();
        recettesRows.forEach(row => {
            const nom = row.getAttribute("data-nom");
            if (nom.includes(term)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    // --- Tri Recettes par Calories (décroissant puis croissant) ---
    const sortRecettesCalories = document.getElementById("sortRecettesCalories");
    const recettesTableBody = document.getElementById("recettesTableBody");
    let recettesSortDesc = true;

    sortRecettesCalories.addEventListener("click", function() {
        let rowsArray = Array.from(recettesRows);
        rowsArray.sort((a, b) => {
            let calA = parseFloat(a.getAttribute("data-calories")) || 0;
            let calB = parseFloat(b.getAttribute("data-calories")) || 0;
            return recettesSortDesc ? (calB - calA) : (calA - calB);
        });
        
        // Re-append in sorted order
        rowsArray.forEach(row => recettesTableBody.appendChild(row));
        recettesSortDesc = !recettesSortDesc;
        sortRecettesCalories.innerHTML = `<i class="fa-solid fa-arrow-${recettesSortDesc ? 'down' : 'up'}-short-wide"></i> Tri Calories`;
    });

    // --- Recherche Aliments ---
    const searchAliments = document.getElementById("searchAliments");
    const alimentsRows = document.querySelectorAll(".aliment-row");
    
    searchAliments.addEventListener("input", function(e) {
        const term = e.target.value.toLowerCase();
        alimentsRows.forEach(row => {
            const nom = row.getAttribute("data-nom");
            if (nom.includes(term)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    // --- Tri Aliments par Catégorie (Alphabétique) ---
    const sortAlimentsCategory = document.getElementById("sortAlimentsCategory");
    const alimentsTableBody = document.getElementById("alimentsTableBody");
    let alimentsSortAsc = true;

    sortAlimentsCategory.addEventListener("click", function() {
        let rowsArray = Array.from(alimentsRows);
        rowsArray.sort((a, b) => {
            let catA = a.getAttribute("data-category");
            let catB = b.getAttribute("data-category");
            if(catA < catB) return alimentsSortAsc ? -1 : 1;
            if(catA > catB) return alimentsSortAsc ? 1 : -1;
            return 0;
        });
        
        rowsArray.forEach(row => alimentsTableBody.appendChild(row));
        alimentsSortAsc = !alimentsSortAsc;
    });
});
</script>

<?php require_once __DIR__ . '/../template_only/layouts/admin_footer.php'; ?>
