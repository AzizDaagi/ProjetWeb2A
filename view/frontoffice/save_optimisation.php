<?php
/**
 * save_optimisation.php
 * Fichier dédié à l'enregistrement des quantités optimisées.
 * Aucune sortie HTML - logique pure + redirection.
 */

// Démarrer l'output buffering immédiatement pour garantir la redirection
ob_start();

require_once __DIR__ . '/../../controler/RecetteController.php';

// Sécurité : on n'accepte que les POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    header('Location: liste_recettes.php');
    exit;
}

$id_recette       = (int)($_POST['id_recette'] ?? 0);
$objectif         = $_POST['objectif'] ?? 'equilibre_global';
$nouvellesQuantites = $_POST['nouvelles_quantites'] ?? [];

if ($id_recette <= 0 || empty($nouvellesQuantites)) {
    ob_end_clean();
    header("Location: optimiser_recette.php?id={$id_recette}&objectif=" . urlencode($objectif) . "&error=empty");
    exit;
}

// Cast strict des types (clés int, valeurs float)
$quantitesCastes = [];
foreach ($nouvellesQuantites as $al_id => $qte) {
    $al_id_int = (int)$al_id;
    $qte_float = (float)$qte;
    if ($al_id_int > 0 && $qte_float >= 0) {
        $quantitesCastes[$al_id_int] = $qte_float;
    }
}

if (!empty($quantitesCastes)) {
    $controller = new RecetteController();
    $controller->appliquerOptimisation($id_recette, $quantitesCastes);
}

// Redirection propre après sauvegarde
ob_end_clean();
header("Location: details_recette.php?id={$id_recette}&optimised=1");
exit;
