<?php
/**
 * Test endpoint for News Feed debugging
 * Access: http://localhost/Web/controller/testNewsController.php
 */

require_once '../model/Connection.php';
require_once '../model/News.php';
require_once '../model/NewsGenerationService.php';

echo "<h2>News Feed Test & Debug</h2>";

try {
    // 1. Check database connection (this loads .env too)
    echo "<h3>1. Database Connection & Environment</h3>";
    $db = config::getConnexion();
    echo "✅ Connected to database<br>";
    echo "✅ Environment variables loaded from .env<br>";
    
    // 2. Create/verify table
    echo "<h3>2. Create News Table</h3>";
    News::createTableIfNotExists($db);
    echo "✅ News table verified/created<br>";
    
    // 3. Check current articles
    echo "<h3>3. Current Articles in Database</h3>";
    $newsModel = new News($db);
    $currentCount = $newsModel->getTotalNewsCount();
    echo "Articles in DB: <strong>$currentCount</strong><br>";
    
    // 4. Check API keys
    echo "<h3>4. API Configuration</h3>";
    $newsApiKey = getenv('NEWSAPI_KEY');
    $unsplashKey = getenv('UNSPLASH_ACCESS_KEY');
    
    if (empty($newsApiKey)) {
        echo "❌ NEWSAPI_KEY not set in .env<br>";
    } else {
        echo "✅ NEWSAPI_KEY: " . substr($newsApiKey, 0, 10) . "...<br>";
    }
    
    if (empty($unsplashKey)) {
        echo "⚠️ UNSPLASH_ACCESS_KEY not set (optional, fallback images will be used)<br>";
    } else {
        echo "✅ UNSPLASH_ACCESS_KEY: " . substr($unsplashKey, 0, 10) . "...<br>";
    }
    
    // 5. Attempt to fetch news
    echo "<h3>5. Fetching News from API</h3>";
    if (empty($newsApiKey)) {
        echo "<strong style='color: red;'>❌ Cannot proceed: NEWSAPI_KEY is required</strong><br>";
        echo "Please add NEWSAPI_KEY to your .env file and try again.";
    } else {
        $service = new NewsGenerationService($db, $newsApiKey, $unsplashKey);
        
        $topics = $service->getHealthyNewsTopics();
        echo "Fetching topic mix:<br>";
        foreach ($topics as $topic => $query) {
            echo "<code>" . htmlspecialchars($topic) . "</code>: " . htmlspecialchars($query) . "<br>";
        }

        $result = $service->fetchAndStoreHealthyNewsMix($newsModel, 4);
        
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px;'>";
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "</pre>";
        
        // Check articles again
        $newCount = $newsModel->getTotalNewsCount();
        echo "Articles after sync: <strong>$newCount</strong> (was $currentCount)<br>";
        
        if ($newCount > $currentCount) {
            echo "✅ Success! Now visit <a href='/Web/view/frontoffice/community.php'>community page</a>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error</h3>";
    echo "<pre style='color: red;'>";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
