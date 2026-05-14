<?php
/**
 * Point de test pour deboguer le fil d actualites.
 * Acces : http://localhost/Web/controller/testNewsController.php
 */

require_once '../model/Connection.php';
require_once '../model/News.php';
require_once '../model/NewsGenerationService.php';

echo "<h2>Test et debug du fil d actualites</h2>";

try {
    echo "<h3>1. Connexion base de donnees et environnement</h3>";
    $db = config::getConnexion();
    echo "OK - Connecte a la base de donnees<br>";
    echo "OK - Variables d environnement chargees depuis .env<br>";

    echo "<h3>2. Creation de la table des actualites</h3>";
    News::createTableIfNotExists($db);
    echo "OK - Table des actualites verifiee/creee<br>";

    echo "<h3>3. Articles actuels dans la base</h3>";
    $newsModel = new News($db);
    $currentCount = $newsModel->getTotalNewsCount();
    echo "Articles dans la base : <strong>$currentCount</strong><br>";

    echo "<h3>4. Configuration API</h3>";
    $newsApiKey = getenv('NEWSAPI_KEY');
    $unsplashKey = getenv('UNSPLASH_ACCESS_KEY');

    if (empty($newsApiKey)) {
        echo "NEWSAPI_KEY n est pas definie dans .env<br>";
    } else {
        echo "OK - NEWSAPI_KEY : " . substr($newsApiKey, 0, 10) . "...<br>";
    }

    if (empty($unsplashKey)) {
        echo "UNSPLASH_ACCESS_KEY n est pas definie (optionnelle, images de secours utilisees)<br>";
    } else {
        echo "OK - UNSPLASH_ACCESS_KEY : " . substr($unsplashKey, 0, 10) . "...<br>";
    }

    echo "<h3>5. Recuperation des actualites depuis l API</h3>";
    if (empty($newsApiKey)) {
        echo "<strong style='color: red;'>Impossible de continuer : NEWSAPI_KEY est requise</strong><br>";
        echo "Ajoutez NEWSAPI_KEY dans votre fichier .env puis reessayez.";
    } else {
        $service = new NewsGenerationService($db, $newsApiKey, $unsplashKey);

        $topics = $service->getHealthyNewsTopics();
        echo "Recuperation du melange de sujets :<br>";
        foreach ($topics as $topic => $query) {
            echo "<code>" . htmlspecialchars($topic) . "</code>: " . htmlspecialchars($query) . "<br>";
        }

        $result = $service->fetchAndStoreHealthyNewsMix($newsModel, 4);

        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px;'>";
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "</pre>";

        $newCount = $newsModel->getTotalNewsCount();
        echo "Articles apres synchronisation : <strong>$newCount</strong> (avant : $currentCount)<br>";

        if ($newCount > $currentCount) {
            echo "Succes ! Ouvrez maintenant <a href='/Web/index.php?action=community'>la page communaute</a>";
        }
    }
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Erreur</h3>";
    echo "<pre style='color: red;'>";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
