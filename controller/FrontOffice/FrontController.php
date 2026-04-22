<?php
require_once __DIR__ . '/../../model/Activite.php';
require_once __DIR__ . '/../../model/Exercice.php';

class FrontController {

    public function home() {
        require_once __DIR__ . '/../../View/front/home.php';
    }

    public function listActivites() {
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        
        require_once __DIR__ . '/../../View/front/activites.php';
    }

    public function showExercices() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=activites');
            exit;
        }

        $id = $_GET['id'];
        $activiteModel = new Activite();
        $activite = $activiteModel->getById($id);

        if (!$activite) {
            header('Location: index.php?action=activites');
            exit;
        }

        $exerciceModel = new Exercice();
        $exercices = $exerciceModel->getByActiviteId($id);

        require_once __DIR__ . '/../../View/front/exercices.php';
    }

    public function nutritionRequest() {
        require_once __DIR__ . '/../../View/front/nutrition_request/form.php';
    }

    public function processNutritionRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['user_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $weight = $_POST['current_weight'] ?? '';
            $goal = $_POST['current_goal'] ?? '';
            $height = $_POST['height'] ?? '';
            $message = trim($_POST['message'] ?? '');

            // Strict Validation
            if (empty($name) || empty($email) || empty($weight) || empty($goal)) {
                header('Location: index.php?action=nutrition_request&error=empty_fields');
                exit;
            }
            if (!is_numeric($weight) || $weight < 1 || $weight > 300) {
                header('Location: index.php?action=nutrition_request&error=invalid_weight');
                exit;
            }
            if (!in_array($goal, ['lose weight', 'gain muscle', 'maintain weight'])) {
                header('Location: index.php?action=nutrition_request&error=invalid_goal');
                exit;
            }
            if (!empty($height) && (!is_numeric($height) || $height < 50 || $height > 250)) {
                header('Location: index.php?action=nutrition_request&error=invalid_height');
                exit;
            }

            require_once __DIR__ . '/../../model/NutritionRequest.php';
            $requestModel = new NutritionRequest();
            $requestModel->user_name = htmlspecialchars($name);
            $requestModel->email = htmlspecialchars($email);
            $requestModel->current_weight = (float)$weight;
            $requestModel->current_goal = $goal;
            $requestModel->height = !empty($height) ? (float)$height : null;
            $requestModel->message = htmlspecialchars($message);
            
            $requestModel->generated_activities = '';
            $requestModel->generated_exercises = '';
            $requestModel->status = 'pending';
            $requestModel->selected_exercises = '';

            $requestId = $requestModel->create();

            if ($requestId) {
                header("Location: index.php?action=nutrition_success");
                exit;
            } else {
                header('Location: index.php?action=nutrition_request&error=db_error');
                exit;
            }
        }
    }

    public function nutritionSuccess() {
        require_once __DIR__ . '/../../View/front/nutrition_request/success.php';
    }
}
?>
