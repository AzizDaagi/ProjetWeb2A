<?php
require_once __DIR__ . '/../model/NutritionRequest.php';

class NutritionRequestController {
    private function startSessionIfNeeded() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function requireAdmin() {
        $this->startSessionIfNeeded();
        if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? 'user') !== 'admin')) {
            header('Location: /smart_nutritionn/gestionActiviteesportive/index.php?action=home');
            exit;
        }
    }

    public function index() {
        $this->requireAdmin();

        $requestModel = new NutritionRequest();
        $requests = $requestModel->getAll();
        
        require_once __DIR__ . '/../view/admin_nutrition_index.php';
    }

    public function edit() {
        $this->requireAdmin();

        if (!isset($_GET['id'])) {
            header('Location: index.php?action=admin_requests');
            exit;
        }

        $id = (int)$_GET['id'];
        $requestModel = new NutritionRequest();
        $requestData = $requestModel->getById($id);

        if (!$requestData) {
            header('Location: index.php?action=admin_requests');
            exit;
        }

        require_once __DIR__ . '/../model/Activite.php';
        require_once __DIR__ . '/../model/Exercice.php';
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        $exerciceModel = new Exercice();
        $exercices = $exerciceModel->getAll();

        require_once __DIR__ . '/../view/admin_nutrition_edit.php';
    }

    public function update() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            $requestModel = new NutritionRequest();
            $requestModel->id = $id;
            
            // Store the admin's written nutrition programme in generated_activities field
            $requestModel->generated_activities = htmlspecialchars(trim($_POST['nutrition_programme'] ?? ''));
            // selected_exercises is no longer managed from the admin view — preserve existing or clear safely
            $requestModel->selected_exercises = '';
            $requestModel->status = $_POST['status'] ?? 'pending';
            
            $requestModel->updateAdmin();
            
            header('Location: index.php?action=admin_requests');
            exit;
        }
    }

    public function delete() {
        $this->requireAdmin();

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $requestModel = new NutritionRequest();
            $requestModel->delete($id);
        }
        
        header('Location: index.php?action=admin_requests');
        exit;
    }
}
?>
