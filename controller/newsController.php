<?php
require_once '../model/News.php';
require_once '../model/Connection.php';
require_once '../model/NewsGenerationService.php';

class NewsController {

    private $newsModel;
    private $newsGenerationService;
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? config::getConnexion();
        $this->newsModel = new News($this->db);
        $this->newsGenerationService = new NewsGenerationService($this->db);
        
        // Create table if not exists
        News::createTableIfNotExists($this->db);
    }

    /**
     * Get featured news articles
     */
    public function getFeatured() {
        try {
            $news = $this->newsModel->getFeaturedNews(6);
            
            echo json_encode([
                'success' => true,
                'data' => $news,
                'total' => count($news)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching news: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get news by category
     */
    public function getByCategory() {
        try {
            $category = $_GET['category'] ?? 'health_tips';
            $limit = (int) ($_GET['limit'] ?? 10);
            
            $validCategories = ['nutrition', 'fitness', 'wellness', 'health_tips'];
            if (!in_array($category, $validCategories)) {
                throw new Exception('Invalid category');
            }

            $news = $this->newsModel->getNewsByCategory($category, $limit);
            
            echo json_encode([
                'success' => true,
                'category' => $category,
                'data' => $news,
                'total' => count($news)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get single news article
     */
    public function getById() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID required');
            }

            $news = $this->newsModel->getNewsById($id);
            
            if (!$news) {
                throw new Exception('Article not found');
            }

            echo json_encode([
                'success' => true,
                'data' => $news
            ]);
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Fetch and store news from API
     */
    public function syncNewsFromAPI() {
        try {
            // Optional authentication check - customize as needed
            if (!$this->isAdminOrAuthorized()) {
                throw new Exception('Unauthorized');
            }

            $keywords = trim($_POST['keywords'] ?? '');
            $limit = (int) ($_POST['limit'] ?? 4);

            if ($keywords !== '') {
                $result = $this->newsGenerationService->fetchAndStoreNews(
                    $this->newsModel,
                    $keywords,
                    $limit
                );
            } else {
                $result = $this->newsGenerationService->fetchAndStoreHealthyNewsMix(
                    $this->newsModel,
                    $limit
                );
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error syncing news: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Create manual news article (for admins)
     */
    public function create() {
        try {
            if (!$this->isAdminOrAuthorized()) {
                throw new Exception('Unauthorized');
            }

            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $summary = trim($_POST['summary'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $category = $_POST['category'] ?? 'health_tips';
            
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content required');
            }

            $validCategories = ['nutrition', 'fitness', 'wellness', 'health_tips'];
            if (!in_array($category, $validCategories)) {
                throw new Exception('Invalid category');
            }

            if (empty($summary)) {
                $summary = substr(strip_tags($content), 0, 150) . '...';
            }

            $newsId = $this->newsModel->createNews(
                $title,
                $content,
                $summary,
                $image_url,
                null,
                $category,
                'Admin',
                null,
                false
            );

            if (!$newsId) {
                throw new Exception('Failed to create article');
            }

            echo json_encode([
                'success' => true,
                'id' => $newsId,
                'message' => 'Article created successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Update news article
     */
    public function update() {
        try {
            if (!$this->isAdminOrAuthorized()) {
                throw new Exception('Unauthorized');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $summary = trim($_POST['summary'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $category = $_POST['category'] ?? 'health_tips';
            $is_published = isset($_POST['is_published']) ? (int) $_POST['is_published'] : 1;

            if (!$id || empty($title) || empty($content)) {
                throw new Exception('Invalid data');
            }

            $success = $this->newsModel->updateNews(
                $id,
                $title,
                $content,
                $summary,
                $image_url,
                $category,
                (bool) $is_published
            );

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Article updated' : 'Update failed'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Delete news article
     */
    public function delete() {
        try {
            if (!$this->isAdminOrAuthorized()) {
                throw new Exception('Unauthorized');
            }

            $id = (int) ($_POST['id'] ?? 0);

            if (!$id) {
                throw new Exception('ID required');
            }

            $success = $this->newsModel->deleteNews($id);

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Article deleted' : 'Delete failed'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Check authorization (customize as needed)
     */
    private function isAdminOrAuthorized() {
        // Customize based on your auth system
        // For now, check if user_id = 1 (admin)
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1;
    }
}

// Route requests
$action = $_GET['action'] ?? $_POST['action'] ?? 'getFeatured';
$controller = new NewsController();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Action not found'
    ]);
}
?>
