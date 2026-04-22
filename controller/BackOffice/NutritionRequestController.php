<?php
require_once __DIR__ . '/../../model/NutritionRequest.php';

class NutritionRequestController {

    public function index() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }

        $requestModel = new NutritionRequest();
        $requests = $requestModel->getAll();
        
        require_once __DIR__ . '/../../View/back/nutrition_request/index.php';
    }

    public function edit() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }

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

        require_once __DIR__ . '/../../model/Activite.php';
        require_once __DIR__ . '/../../model/Exercice.php';
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        $exerciceModel = new Exercice();
        $exercices = $exerciceModel->getAll();

        require_once __DIR__ . '/../../View/back/nutrition_request/edit.php';
    }

    public function update() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            $requestModel = new NutritionRequest();
            $requestModel->id = $id;
            
            $assigned_activities = isset($_POST['assigned_activities']) ? $_POST['assigned_activities'] : [];
            $assigned_exercises = isset($_POST['assigned_exercises']) ? $_POST['assigned_exercises'] : [];
            
            $requestModel->generated_activities = implode(", ", array_map('htmlspecialchars', $assigned_activities));
            $requestModel->selected_exercises = implode(", ", array_map('htmlspecialchars', $assigned_exercises));
            $requestModel->status = $_POST['status'] ?? 'pending';
            
            $requestModel->updateAdmin();
            
            header('Location: index.php?action=admin_requests');
            exit;
        }
    }

    public function delete() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }

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
