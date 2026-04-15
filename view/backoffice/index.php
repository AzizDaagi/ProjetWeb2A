<?php
$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../../controler/RecetteController.php';
require_once __DIR__ . '/../../controler/AlimentController.php';
require_once __DIR__ . '/../../controler/UserController.php';

$recetteController = new RecetteController();
$alimentController = new AlimentController();
$userController = new UserController();

$totalRecettes = $recetteController->countRecettes();
$totalAliments = $alimentController->countAliments();
$totalUsers = $userController->countUsers();
$latestRecettes = $recetteController->getLatestRecettes(5);

require_once __DIR__ . '/../template_only/layouts/header.php'; 
?>

<div class="container admin-dashboard" style="max-width: 1200px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="margin: 0;"><i class="fa-solid fa-gauge-high"></i> Tableau de Bord</h1>
        <a href="/projetwebmalek/view/frontoffice/liste_recettes.php" class="btn btn-primary" style="background: #28a745; text-decoration: none; padding: 10px 15px; border-radius: 8px;">
            <i class="fa-solid fa-earth-europe"></i> Voir le Front-Office
        </a>
    </div>

    <!-- Statistiques -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 40px; margin-bottom: 10px;"><i class="fa-solid fa-utensils"></i></div>
            <h3 style="margin: 0; font-size: 24px;">Recettes : <?= $totalRecettes ?></h3>
        </div>
        <div style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 40px; margin-bottom: 10px;"><i class="fa-solid fa-leaf"></i></div>
            <h3 style="margin: 0; font-size: 24px;">Aliments : <?= $totalAliments ?></h3>
        </div>
        <div style="background: linear-gradient(135deg, #17a2b8, #117a8b); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 40px; margin-bottom: 10px;"><i class="fa-solid fa-users"></i></div>
            <h3 style="margin: 0; font-size: 24px;">Utilisateurs : <?= $totalUsers ?></h3>
        </div>
    </div>

    <!-- Navigation Admin Rapide -->
    <h2 style="margin-bottom: 20px;">Gestion des Modules</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 40px;">
        <a href="manage_recettes.php" class="btn" style="background: #e9ecef; color: #333; font-weight: bold; padding: 15px; text-decoration: none; border-radius: 8px; text-align: center;">
            <i class="fa-solid fa-book-open"></i> Gérer Recettes
        </a>
        <a href="manage_aliments.php" class="btn" style="background: #e9ecef; color: #333; font-weight: bold; padding: 15px; text-decoration: none; border-radius: 8px; text-align: center;">
            <i class="fa-solid fa-apple-whole"></i> Gérer Aliments
        </a>
        <a href="manage_users.php" class="btn" style="background: #e9ecef; color: #333; font-weight: bold; padding: 15px; text-decoration: none; border-radius: 8px; text-align: center;">
            <i class="fa-solid fa-user-gear"></i> Gérer Utilisateurs
        </a>
        <a href="manage_recommandations.php" class="btn" style="background: #e9ecef; color: #333; font-weight: bold; padding: 15px; text-decoration: none; border-radius: 8px; text-align: center;">
            <i class="fa-solid fa-heart-pulse"></i> Recommandations
        </a>
    </div>

    <!-- Dernières Recettes -->
    <h2><i class="fa-solid fa-clock-rotate-left"></i> Dernières Recettes Ajoutées</h2>
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 20px;">
        <?php if (!empty($latestRecettes)): ?>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($latestRecettes as $recette): ?>
                    <li style="padding: 15px 0; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="font-size: 18px; color: #333;"><?= htmlspecialchars((string) $recette['nom']) ?></strong>
                            <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                <i class="fa-solid fa-clock"></i> <?= htmlspecialchars((string) $recette['temps_preparation']) ?> | 
                                <i class="fa-solid fa-chart-bar"></i> <?= htmlspecialchars((string) $recette['niveau_difficulte']) ?>
                            </div>
                        </div>
                        <a href="manage_recettes.php" class="btn" style="background: #ffc107; color: #333; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: bold;">
                            Modifier
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: #666; text-align: center; padding: 20px 0;">Aucune recette ajoutée récemment.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
