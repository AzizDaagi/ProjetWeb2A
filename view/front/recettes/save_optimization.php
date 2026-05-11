<?php
ob_start();

$projectBaseUrl = $baseUrl ?? '/projet-web-25-26';
$routeBase = $projectBaseUrl . '/index.php';

require_once __DIR__ . '/../../../controller/RecetteController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    header('Location: ' . $routeBase . '?action=recipes-management');
    exit;
}

$idRecette = (int) ($_POST['id_recette'] ?? 0);
$objectif = (string) ($_POST['objectif'] ?? 'equilibre_global');
$nouvellesQuantites = $_POST['nouvelles_quantites'] ?? [];

if ($idRecette <= 0 || empty($nouvellesQuantites)) {
    ob_end_clean();
    header('Location: ' . $routeBase . '?action=recipe-optimize&id=' . $idRecette . '&objectif=' . urlencode($objectif) . '&error=empty');
    exit;
}

$quantitesCastes = [];
foreach ($nouvellesQuantites as $idAliment => $quantite) {
    $idAliment = (int) $idAliment;
    $quantite = (float) $quantite;

    if ($idAliment > 0 && $quantite >= 0) {
        $quantitesCastes[$idAliment] = $quantite;
    }
}

if (!empty($quantitesCastes)) {
    $controller = new RecetteController();
    $controller->appliquerOptimisation($idRecette, $quantitesCastes);
}

ob_end_clean();
header('Location: ' . $routeBase . '?action=recipe-details&id=' . $idRecette . '&optimised=1');
exit;
