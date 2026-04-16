<?php
require_once '../model/Post.php';
require_once '../model/connection.php';

class PostController {

    private $postModel;
    private $projectRoot;
    private $postImageDirectory;

    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    private const MAX_UPLOAD_SIZE = 5242880;

    public function __construct($db) {
        $this->postModel = new Post($db);
        $this->projectRoot = realpath(__DIR__ . '/..');
        $this->postImageDirectory = $this->projectRoot . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'post_uploads' . DIRECTORY_SEPARATOR . 'posts';
    }

    public function getAll() {
        require_once 'model/Comment.php';
        $commentModel = new Comment($this->postModel->database);
        
        $posts = $this->postModel->getAllPosts();
        require_once 'view/frontOffice/community.php';
    }

    public function create() {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        $userId = 1;

        if (empty($title) || empty($content)) {
            echo json_encode([
                'success' => false,
                'message' => 'Titre et contenu requis'
            ]);
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
            $result = $this->postModel->createPost($userId, $title, $content, $image);
        } catch (PDOException $e) {
            $this->respondToDatabaseImageError($e);
        }

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Post publié avec succès' : 'Erreur lors de la publication'
        ]);
        exit;
    }

    public function update() {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        if (!$id || empty($title) || empty($content)) {
            echo json_encode([
                'success' => false,
                'message' => 'Données invalides'
            ]);
            exit;
        }

        $currentPost = $this->postModel->getPostById($id);
        $image = $currentPost ? $currentPost['image'] : null;

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

        $userId = 1;
        try {
            $success = $this->postModel->updatePost($id, $title, $content, $image, $userId);
        } catch (PDOException $e) {
            $this->respondToDatabaseImageError($e);
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Post modifié' : 'Erreur modification'
        ]);
        exit;
    }

    public function delete() {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'ID manquant'
            ]);
            exit;
        }

        $currentPost = $this->postModel->getPostById($id);
        $success = $this->postModel->deletePost($id);

        if ($success && $currentPost) {
            $this->deleteStoredImage($currentPost['image'] ?? null);
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Post supprimé' : 'Erreur suppression'
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
}
?>

