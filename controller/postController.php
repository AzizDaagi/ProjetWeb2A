<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/Post.php';
require_once __DIR__ . '/../model/connection.php';
require_once __DIR__ . '/../model/AiModeration.php';
require_once __DIR__ . '/../model/ImageModeration.php';
require_once __DIR__ . '/../model/Notification.php';
require_once __DIR__ . '/../model/ModerationJob.php';
require_once __DIR__ . '/../model/InputValidator.php';

class PostController {

    private $postModel;
    private $aiModeration;
    private $imageModeration;
    private $notificationModel;
    private $moderationJobModel;
    private $projectRoot;
    private $postImageDirectory;

    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    private const MAX_UPLOAD_SIZE = 5242880;
    private const ALLOWED_REACTIONS = ['love', 'laugh', 'sad', 'angry'];
    private const ALLOWED_REPORT_REASONS = ['spam', 'harassment', 'false_information', 'inappropriate_content', 'other'];
    private const ALLOWED_CATEGORIES = ['question', 'recipe', 'progress', 'advice', 'product_review'];

    public function __construct($db) {
        $this->postModel = new Post($db);
        $this->aiModeration = new AiModeration($db);
        $this->imageModeration = new ImageModeration($db);
        $this->notificationModel = new Notification($db);
        $this->moderationJobModel = new ModerationJob($db);
        $this->projectRoot = realpath(__DIR__ . '/..');
        $this->postImageDirectory = $this->projectRoot . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'post_uploads' . DIRECTORY_SEPARATOR . 'posts';
    }

    public function getAll() {
        require_once 'model/Comment.php';
        $commentModel = new Comment($this->postModel->database);

        $posts = $this->postModel->getAllPosts();
        require_once __DIR__ . '/../view/frontoffice/community.php';
    }

    private function currentUserId(): int {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    private function currentRole(): string {
        return (($_SESSION['user_role'] ?? 'user') === 'admin') ? 'admin' : 'user';
    }

    private function requireClientSession(): int {
        $userId = $this->currentUserId();
        if ($userId <= 0 || $this->currentRole() === 'admin') {
            $this->jsonError('Acces front office refuse.');
            http_response_code($userId <= 0 ? 401 : 403);
            exit;
        }

        return $userId;
    }

    private function requireAuthenticatedSession(): int {
        $userId = $this->currentUserId();
        if ($userId <= 0) {
            $this->jsonError('Session invalide.');
            http_response_code(401);
            exit;
        }

        return $userId;
    }

    public function create() {
        $userId = $this->requireClientSession();
        $title = InputValidator::cleanText($_POST['title'] ?? '');
        $content = InputValidator::cleanMultiline($_POST['content'] ?? '');
        $category = $this->sanitizePostCategory($_POST['post_category'] ?? 'advice');
        $productAnalysisJson = $this->sanitizeProductAnalysisJson($_POST['product_analysis_json'] ?? '');
        $location = $this->sanitizePostLocation($_POST['latitude'] ?? null, $_POST['longitude'] ?? null, $_POST['location_accuracy'] ?? null);

        $validationError = InputValidator::firstError([
            InputValidator::validatePostTitle($title),
            InputValidator::validatePostContent($content),
            InputValidator::validatePostCategory($category)
        ]);

        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        try {
            $image = $this->storeUploadedImage($_FILES['image'] ?? null);
        } catch (RuntimeException $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }

        try {
            $result = $this->postModel->createPost(
                $userId,
                $title,
                $content,
                $image,
                $productAnalysisJson,
                $location['latitude'],
                $location['longitude'],
                $location['accuracy'],
                $category
            );
        } catch (PDOException $e) {
            $this->respondToDatabaseImageError($e);
        }

        if ($result) {
            $this->queuePostModeration((int) $result, $title, $content, $image);
        }

        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Publication publiee avec succes' : 'Erreur lors de la publication',
            'moderation' => $result ? ['status' => 'queued'] : null,
            'imageModeration' => $result ? ['status' => 'queued'] : null,
            'moderationQueued' => (bool) $result
        ]);
        exit;
    }

    public function update() {
        $userId = $this->requireAuthenticatedSession();
        $id = $_POST['id'] ?? null;
        $title = InputValidator::cleanText($_POST['title'] ?? '');
        $content = InputValidator::cleanMultiline($_POST['content'] ?? '');
        $category = $this->sanitizePostCategory($_POST['post_category'] ?? 'advice');

        $validationError = InputValidator::firstError([
            InputValidator::validateId($id, 'Publication'),
            InputValidator::validatePostTitle($title),
            InputValidator::validatePostContent($content),
            InputValidator::validatePostCategory($category)
        ]);

        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        $currentPost = $this->postModel->getPostById($id);
        $image = $currentPost ? $currentPost['image'] : null;
        $productAnalysisJson = $currentPost ? ($currentPost['product_analysis_json'] ?? null) : null;
        if (array_key_exists('product_analysis_json', $_POST)) {
            $productAnalysisJson = $this->sanitizeProductAnalysisJson($_POST['product_analysis_json'] ?? '');
        }

        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            $this->deleteStoredImage($image);
            $image = null;
        } elseif (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $newImage = $this->storeUploadedImage($_FILES['image']);
                $this->deleteStoredImage($image);
                $image = $newImage;
            } catch (RuntimeException $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }

        try {
            $success = $this->currentRole() === 'admin'
                ? $this->postModel->updatePostAsAdmin($id, $title, $content, $image, $productAnalysisJson, $category)
                : $this->postModel->updatePost($id, $title, $content, $image, $userId, $productAnalysisJson, $category);
        } catch (PDOException $e) {
            $this->respondToDatabaseImageError($e);
        }

        if ($success) {
            $this->queuePostModeration((int) $id, $title, $content, $image);
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Publication modifiee' : 'Erreur modification',
            'moderation' => $success ? ['status' => 'queued'] : null,
            'imageModeration' => $success ? ['status' => 'queued'] : null,
            'moderationQueued' => (bool) $success
        ]);
        exit;
    }

    public function delete() {
        $userId = $this->requireAuthenticatedSession();
        $id = $_POST['id'] ?? null;

        $validationError = InputValidator::validateId($id, 'Publication');
        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        $currentPost = $this->postModel->getPostById($id);
        $success = $this->currentRole() === 'admin'
            ? $this->postModel->deletePost($id)
            : $this->postModel->deletePostForUser($id, $userId);

        if ($success && $currentPost) {
            $this->postModel->deleteReactionsForPost($id);
            $this->deleteStoredImage($currentPost['image'] ?? null);
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Publication supprimee' : 'Erreur suppression'
        ]);
        exit;
    }

    public function react() {
        $userId = $this->requireClientSession();
        $postId = $_POST['post_id'] ?? null;
        $reactionType = $_POST['reaction_type'] ?? '';

        $validationError = InputValidator::validateId($postId, 'Publication');
        if ($validationError || !in_array($reactionType, self::ALLOWED_REACTIONS, true)) {
            $this->jsonError($validationError ?: 'Reaction invalide.');
            exit;
        }

        $success = $this->postModel->reactToPost($postId, $userId, $reactionType);
        $summary = $this->postModel->getReactionSummary($postId, $userId);

        if ($success && ($summary['user_reaction'] ?? null) === $reactionType) {
            $this->notifyReaction((int) $postId, $userId, $reactionType);
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Reaction mise a jour' : 'Erreur reaction',
            'reactionSummary' => $summary
        ]);
        exit;
    }

    public function report() {
        $userId = $this->requireClientSession();
        $postId = $_POST['post_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        $details = InputValidator::cleanMultiline($_POST['details'] ?? '');

        $validationError = InputValidator::firstError([
            InputValidator::validateId($postId, 'Publication'),
            in_array($reason, self::ALLOWED_REPORT_REASONS, true) ? null : 'Raison de signalement invalide.',
            InputValidator::validateReportDetails($details)
        ]);

        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        $success = $this->postModel->reportPost($postId, $userId, $reason, $details);
        $report = $this->postModel->getUserReportForPost($postId, $userId);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Publication signalee avec succes' : 'Erreur lors du signalement',
            'report' => $report ?: null
        ]);
        exit;
    }

    private function storeUploadedImage($file) {
        if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erreur lors du televersement de l\'image');
        }

        if (($file['size'] ?? 0) > self::MAX_UPLOAD_SIZE) {
            throw new RuntimeException('Image trop volumineuse. Taille maximum: 5 Mo');
        }

        $fileTmp = $file['tmp_name'] ?? '';
        $imageInfo = @getimagesize($fileTmp);
        $fileType = $imageInfo['mime'] ?? null;

        if (!$imageInfo || !$fileType || !isset(self::ALLOWED_IMAGE_MIME_TYPES[$fileType])) {
            throw new RuntimeException('Format image invalide. Utilisez JPG, PNG, GIF ou WEBP');
        }

        if (!is_dir($this->postImageDirectory) && !mkdir($this->postImageDirectory, 0775, true) && !is_dir($this->postImageDirectory)) {
            throw new RuntimeException('Impossible de preparer le dossier des images');
        }

        $extension = self::ALLOWED_IMAGE_MIME_TYPES[$fileType];
        $filename = sprintf('post_%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(8)), $extension);
        $destination = $this->postImageDirectory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($fileTmp, $destination)) {
            throw new RuntimeException('Impossible d\'enregistrer l\'image sur le serveur');
        }

        return '/Web/view/post_uploads/posts/' . $filename;
    }

    private function jsonError(string $message): void {
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }

    private function sanitizeProductAnalysisJson($value): ?string {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (strlen($value) > 12000) {
            return null;
        }

        $data = json_decode($value, true);
        if (!is_array($data)) {
            return null;
        }

        $allowed = [
            'name',
            'brand',
            'image',
            'nutriScore',
            'novaGroup',
            'calories',
            'sugar',
            'fat',
            'salt',
            'allergens',
            'ingredients',
            'sourceUrl'
        ];

        $clean = [];
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            if ($key === 'allergens') {
                $clean[$key] = array_values(array_slice(array_filter(array_map('strval', (array) $data[$key])), 0, 8));
                continue;
            }

            if (in_array($key, ['calories', 'sugar', 'fat', 'salt'], true)) {
                $clean[$key] = is_numeric($data[$key]) ? round((float) $data[$key], 2) : null;
                continue;
            }

            $clean[$key] = substr(trim((string) $data[$key]), 0, $key === 'ingredients' ? 1200 : 255);
        }

        return json_encode($clean);
    }

    private function sanitizePostCategory($value): string {
        $category = strtolower(trim((string) $value));
        return in_array($category, self::ALLOWED_CATEGORIES, true) ? $category : 'advice';
    }

    private function sanitizePostLocation($latitude, $longitude, $accuracy): array {
        $lat = filter_var($latitude, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($longitude, FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return [
                'latitude' => null,
                'longitude' => null,
                'accuracy' => null
            ];
        }

        $cleanAccuracy = filter_var($accuracy, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);

        return [
            'latitude' => round((float) $lat, 8),
            'longitude' => round((float) $lng, 8),
            'accuracy' => $cleanAccuracy === false ? null : (int) $cleanAccuracy
        ];
    }

    private function respondToDatabaseImageError(PDOException $e) {
        $message = $e->getMessage();

        if (stripos($message, 'max_allowed_packet') !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Image trop lourde pour votre configuration MySQL. Essayez une image plus petite ou convertissez-la en WEBP'
            ]);
            exit;
        }

        throw $e;
    }

    private function queuePostModeration(int $postId, string $title, string $content, ?string $image): void {
        $this->moderationJobModel->enqueue('post', $postId, 'text', [
            'text' => trim($title . "\n\n" . $content)
        ]);

        $this->moderationJobModel->enqueue('post', $postId, 'image', [
            'image_path' => $this->resolveManagedImagePath($image)
        ]);
    }

    private function resolveManagedImagePath(?string $image): ?string {
        if (!$this->isManagedUploadPath($image)) {
            return null;
        }

        $relativePath = ltrim(str_replace('/Web/', '', $image), '/');
        return $this->projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function deleteStoredImage($image) {
        if (!$this->isManagedUploadPath($image)) {
            return;
        }

        $relativePath = ltrim(str_replace('/Web/', '', $image), '/');
        $absolutePath = $this->projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function isManagedUploadPath($image) {
        return is_string($image) && strpos($image, '/Web/view/post_uploads/posts/') === 0;
    }

    private function notifyReaction(int $postId, int $actorUserId, string $reactionType): void {
        $post = $this->postModel->getPostById($postId);
        if (!$post || (int) ($post['user_id'] ?? 0) === $actorUserId) {
            return;
        }

        $actorName = $_SESSION['user_name'] ?? 'Quelqu un';
        $reactionLabel = ucfirst($reactionType);
        $postTitle = $post['title'] ?? 'votre publication';

        $this->notificationModel->create(
            (int) $post['user_id'],
            $actorUserId,
            'post_reaction',
            'Nouvelle reaction sur votre publication',
            $actorName . ' a reagi "' . $reactionLabel . '" a votre publication "' . $postTitle . '".',
            '/Web/index.php?action=community#post-' . $postId,
            $postId
        );
    }
}

$database = new config();
$db = $database->getConnexion();

$controller = new PostController($db);
$action = $_GET['action'] ?? '';

if ($action == 'create') {
    $controller->create();
} elseif ($action == 'delete') {
    $controller->delete();
} elseif ($action == 'update') {
    $controller->update();
} elseif ($action == 'react') {
    $controller->react();
} elseif ($action == 'report') {
    $controller->report();
}
?>
