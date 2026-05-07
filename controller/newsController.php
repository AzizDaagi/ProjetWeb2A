<?php
require_once '../model/News.php';
require_once '../model/Connection.php';
require_once '../model/NewsGenerationService.php';
require_once '../model/InputValidator.php';

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
                'message' => 'Erreur lors du chargement des actualites : ' . $e->getMessage()
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
                throw new Exception('Categorie invalide');
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
                'message' => 'Erreur : ' . $e->getMessage()
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
                throw new Exception('ID requis');
            }

            $news = $this->newsModel->getNewsById($id);
            
            if (!$news) {
                throw new Exception('Article introuvable');
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
                throw new Exception('Non autorise');
            }

            $keywords = InputValidator::cleanText($_POST['keywords'] ?? '');
            $limit = (int) ($_POST['limit'] ?? 4);
            if ($limit < 1 || $limit > 10) {
                throw new Exception('La limite doit etre comprise entre 1 et 10');
            }
            if ($keywords !== '' && mb_strlen($keywords) > 160) {
                throw new Exception('Les mots-cles ne doivent pas depasser 160 caracteres');
            }

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
                'message' => 'Erreur lors de la synchronisation des actualites : ' . $e->getMessage()
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
                throw new Exception('Non autorise');
            }

            $title = InputValidator::cleanText($_POST['title'] ?? '');
            $content = InputValidator::cleanMultiline($_POST['content'] ?? '');
            $summary = InputValidator::cleanMultiline($_POST['summary'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $category = $_POST['category'] ?? 'health_tips';
            
            $validCategories = ['nutrition', 'fitness', 'wellness', 'health_tips'];
            $validationError = InputValidator::firstError([
                InputValidator::validateNewsTitle($title),
                InputValidator::validateNewsContent($content),
                $summary !== '' && mb_strlen($summary) > 500 ? 'Le resume ne doit pas depasser 500 caracteres' : null,
                InputValidator::validateUrl($image_url, 'URL de l image'),
                in_array($category, $validCategories, true) ? null : 'Categorie invalide'
            ]);

            if ($validationError) {
                throw new Exception($validationError);
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
                throw new Exception('Impossible de creer l article');
            }

            echo json_encode([
                'success' => true,
                'id' => $newsId,
                'message' => 'Article cree avec succes'
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
                throw new Exception('Non autorise');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $title = InputValidator::cleanText($_POST['title'] ?? '');
            $content = InputValidator::cleanMultiline($_POST['content'] ?? '');
            $summary = InputValidator::cleanMultiline($_POST['summary'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $category = $_POST['category'] ?? 'health_tips';
            $is_published = isset($_POST['is_published']) ? (int) $_POST['is_published'] : 1;

            $validCategories = ['nutrition', 'fitness', 'wellness', 'health_tips'];
            $validationError = InputValidator::firstError([
                InputValidator::validateId($id, 'Article'),
                InputValidator::validateNewsTitle($title),
                InputValidator::validateNewsContent($content),
                $summary !== '' && mb_strlen($summary) > 500 ? 'Le resume ne doit pas depasser 500 caracteres' : null,
                InputValidator::validateUrl($image_url, 'URL de l image'),
                in_array($category, $validCategories, true) ? null : 'Categorie invalide',
                in_array($is_published, [0, 1], true) ? null : 'Statut de publication invalide'
            ]);

            if ($validationError) {
                throw new Exception($validationError);
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
                'message' => $success ? 'Article modifie' : 'Echec de la modification'
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
                throw new Exception('Non autorise');
            }

            $id = (int) ($_POST['id'] ?? 0);

            $validationError = InputValidator::validateId($id, 'Article');
            if ($validationError) {
                throw new Exception($validationError);
            }

            $success = $this->newsModel->deleteNews($id);

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Article supprime' : 'Echec de la suppression'
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
        'message' => 'Action introuvable'
    ]);
}
?>
