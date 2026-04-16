<?php
require_once '../model/Comment.php';
require_once '../model/connection.php';

class CommentController {

    private $commentModel;

    public function __construct($db) {
        $this->commentModel = new Comment($db);
    }

    // CREATE
    public function add() {
        $postId = $_POST['post_id'] ?? null;
        $content = $_POST['content'] ?? '';
        $userId = 1; // temporary (replace with session later)

        if (!$postId || empty($content)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Contenu requis'
            ]);
            exit;
        }

        $success = $this->commentModel->addComment($postId, $userId, $content);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire ajouté' : 'Erreur ajout'
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
                'message' => 'Données invalides'
            ]);
            exit;
        }

        $success = $this->commentModel->updateComment($id, $content, $userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Commentaire modifié' : 'Erreur modification'
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
            'message' => $success ? 'Commentaire supprimé' : 'Erreur suppression'
        ]);
        exit;
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
}
