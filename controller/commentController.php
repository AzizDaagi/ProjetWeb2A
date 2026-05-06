<?php
require_once '../model/Comment.php';
require_once '../model/connection.php';
require_once '../model/AiModeration.php';

class CommentController {

    private $commentModel;
    private $aiModeration;

    public function __construct($db) {
        $this->commentModel = new Comment($db);
        $this->aiModeration = new AiModeration($db);
    }

    // CREATE
    public function add() {
        $postId = $_POST['post_id'] ?? null;
        $content = $_POST['content'] ?? '';
        $parentCommentId = $_POST['parent_comment_id'] ?? null;
        $userId = 1; // temporary (replace with session later)

        if ($parentCommentId === '') {
            $parentCommentId = null;
        }

        if ($parentCommentId !== null) {
            $parentCommentId = (int) $parentCommentId;
        }

        if (!$postId || empty($content)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Contenu requis'
            ]);
            exit;
        }

        $result = $this->commentModel->addComment($postId, $userId, $content, $parentCommentId);
        $moderation = null;

        if ($result) {
            $moderation = $this->moderateCommentText((int) $result, $content);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Commentaire ajoute' : 'Impossible d\'ajouter la reponse ou le commentaire',
            'moderation' => $moderation
        ]);
        exit;
    }

    // UPDATE
    public function update() {
        $id = $_POST['id'] ?? null;
        $content = $_POST['content'] ?? '';
        $userId = 1; // temporary

        if (!$id || empty($content)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Donnees invalides'
            ]);
            exit;
        }

        $success = $this->commentModel->updateComment($id, $content, $userId);
        $moderation = null;

        if ($success) {
            $moderation = $this->moderateCommentText((int) $id, $content);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire modifie' : 'Erreur modification',
            'moderation' => $moderation
        ]);
        exit;
    }

    // DELETE
    public function delete() {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID manquant'
            ]);
            exit;
        }

        $success = $this->commentModel->deleteComment($id);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire supprime' : 'Erreur suppression'
        ]);
        exit;
    }

    public function adminUpdate() {
        $id = $_POST['id'] ?? null;
        $content = $_POST['content'] ?? '';

        if (!$id || empty($content)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Donnees invalides'
            ]);
            exit;
        }

        $success = $this->commentModel->updateCommentAsAdmin($id, $content);
        $moderation = null;

        if ($success) {
            $moderation = $this->moderateCommentText((int) $id, $content);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire modifie' : 'Erreur modification',
            'moderation' => $moderation
        ]);
        exit;
    }

    public function adminDelete() {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID manquant'
            ]);
            exit;
        }

        $success = $this->commentModel->deleteCommentAsAdmin($id);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire supprime' : 'Erreur suppression'
        ]);
        exit;
    }

    private function moderateCommentText(int $commentId, string $content): ?array {
        try {
            return $this->aiModeration->analyzeAndStore('comment', $commentId, $content);
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}

$database = new config();
$db = $database->getConnexion();

$controller = new CommentController($db);

$action = $_GET['action'] ?? '';

if ($action == 'add') {
    $controller->add();
} elseif ($action == 'update') {
    $controller->update();
} elseif ($action == 'delete') {
    $controller->delete();
} elseif ($action == 'admin_update') {
    $controller->adminUpdate();
} elseif ($action == 'admin_delete') {
    $controller->adminDelete();
}
