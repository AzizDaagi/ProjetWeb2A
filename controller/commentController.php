<?php
require_once __DIR__ . '/../model/Comment.php';
require_once __DIR__ . '/../model/connection.php';
require_once __DIR__ . '/../model/AiModeration.php';
require_once __DIR__ . '/../model/Post.php';
require_once __DIR__ . '/../model/Notification.php';
require_once __DIR__ . '/../model/ModerationJob.php';
require_once __DIR__ . '/../model/InputValidator.php';

class CommentController {

    private $commentModel;
    private $aiModeration;
    private $postModel;
    private $notificationModel;
    private $moderationJobModel;

    public function __construct($db) {
        $this->commentModel = new Comment($db);
        $this->aiModeration = new AiModeration($db);
        $this->postModel = new Post($db);
        $this->notificationModel = new Notification($db);
        $this->moderationJobModel = new ModerationJob($db);
    }

    // CREATE
    public function add() {
        $postId = $_POST['post_id'] ?? null;
        $content = InputValidator::cleanMultiline($_POST['content'] ?? '');
        $parentCommentId = $_POST['parent_comment_id'] ?? null;
        $userId = 1; // temporary (replace with session later)

        if ($parentCommentId === '') {
            $parentCommentId = null;
        }

        if ($parentCommentId !== null) {
            $parentCommentId = (int) $parentCommentId;
        }

        $validationError = InputValidator::firstError([
            InputValidator::validateId($postId, 'Publication'),
            $parentCommentId !== null ? InputValidator::validateId($parentCommentId, 'Commentaire parent') : null,
            InputValidator::validateComment($content)
        ]);

        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        $result = $this->commentModel->addComment($postId, $userId, $content, $parentCommentId);
        if ($result) {
            $this->queueCommentModeration((int) $result, $content);
            $this->notifyCommentActivity((int) $result, (int) $postId, $userId, $content, $parentCommentId);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool) $result,
            'message' => $result ? 'Commentaire ajoute' : 'Impossible d\'ajouter la reponse ou le commentaire',
            'moderation' => $result ? ['status' => 'queued'] : null,
            'moderationQueued' => (bool) $result
        ]);
        exit;
    }

    // UPDATE
    public function update() {
        $id = $_POST['id'] ?? null;
        $content = InputValidator::cleanMultiline($_POST['content'] ?? '');
        $userId = 1; // temporary

        $validationError = InputValidator::firstError([
            InputValidator::validateId($id, 'Commentaire'),
            InputValidator::validateComment($content)
        ]);

        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        $success = $this->commentModel->updateComment($id, $content, $userId);
        if ($success) {
            $this->queueCommentModeration((int) $id, $content);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire modifie' : 'Erreur modification',
            'moderation' => $success ? ['status' => 'queued'] : null,
            'moderationQueued' => (bool) $success
        ]);
        exit;
    }

    // DELETE
    public function delete() {
        $id = $_POST['id'] ?? null;

        $validationError = InputValidator::validateId($id, 'Commentaire');
        if ($validationError) {
            $this->jsonError($validationError);
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

    public function like() {
        $id = $_POST['id'] ?? null;
        $userId = 1;

        $validationError = InputValidator::validateId($id, 'Commentaire');
        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        $success = $this->commentModel->toggleLike((int) $id, $userId);
        $summary = $this->commentModel->getLikeSummary((int) $id, $userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Like mis a jour' : 'Erreur like',
            'likeSummary' => $summary
        ]);
        exit;
    }

    public function adminUpdate() {
        $id = $_POST['id'] ?? null;
        $content = InputValidator::cleanMultiline($_POST['content'] ?? '');

        $validationError = InputValidator::firstError([
            InputValidator::validateId($id, 'Commentaire'),
            InputValidator::validateComment($content)
        ]);

        if ($validationError) {
            $this->jsonError($validationError);
            exit;
        }

        $success = $this->commentModel->updateCommentAsAdmin($id, $content);
        if ($success) {
            $this->queueCommentModeration((int) $id, $content);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire modifie' : 'Erreur modification',
            'moderation' => $success ? ['status' => 'queued'] : null,
            'moderationQueued' => (bool) $success
        ]);
        exit;
    }

    public function adminDelete() {
        $id = $_POST['id'] ?? null;

        $validationError = InputValidator::validateId($id, 'Commentaire');
        if ($validationError) {
            $this->jsonError($validationError);
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

    private function queueCommentModeration(int $commentId, string $content): void {
        $this->moderationJobModel->enqueue('comment', $commentId, 'text', [
            'text' => $content
        ]);
    }

    private function jsonError(string $message): void {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }

    private function notifyCommentActivity(int $commentId, int $postId, int $actorUserId, string $content, $parentCommentId = null): void {
        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            return;
        }

        $actorName = $_SESSION['user_name'] ?? 'Quelqu un';
        $postTitle = $post['title'] ?? 'votre publication';
        $messagePreview = $this->shorten($content);
        $link = '/Web/view/frontoffice/community.php#post-' . $postId;

        if ($parentCommentId !== null) {
            $parentComment = $this->commentModel->getCommentById((int) $parentCommentId);
            if ($parentComment) {
                $this->notificationModel->create(
                    (int) $parentComment['user_id'],
                    $actorUserId,
                    'comment_reply',
                    'Nouvelle reponse a votre commentaire',
                    $actorName . ' a repondu a votre commentaire sur "' . $postTitle . '" : ' . $messagePreview,
                    $link,
                    $postId,
                    $commentId
                );
            }
            return;
        }

        $this->notificationModel->create(
            (int) $post['user_id'],
            $actorUserId,
            'post_comment',
            'Nouveau commentaire sur votre publication',
            $actorName . ' a commente "' . $postTitle . '" : ' . $messagePreview,
            $link,
            $postId,
            $commentId
        );
    }

    private function shorten(string $text, int $maxLength = 120): string {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        return substr($text, 0, $maxLength - 3) . '...';
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
} elseif ($action == 'like') {
    $controller->like();
} elseif ($action == 'admin_update') {
    $controller->adminUpdate();
} elseif ($action == 'admin_delete') {
    $controller->adminDelete();
}
