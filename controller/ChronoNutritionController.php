<?php

require_once __DIR__ . '/../model/ChronoNutritionModel.php';
require_once __DIR__ . '/../model/ChronoNutritionService.php';

class ChronoNutritionController {
    private $model;
    private $service;

    public function __construct(PDO $pdo) {
        $this->model = new ChronoNutritionModel($pdo);
        $this->service = new ChronoNutritionService($pdo);

        // Automatically create the table if it does not exist
        ChronoNutritionModel::createTableIfNotExists($pdo);
    }

    public function chrono_nutrition() {
        $pageTitle = 'Chrono-Nutrition';
        $showFooter = true;
        $bodyClass = 'chrono-nutrition-page chrono-page-shell';
        $chronoStylesheet = __DIR__ . '/../view/front/assets/css/chrono-nutrition.css';
        $chronoScript = __DIR__ . '/../view/front/assets/js/chrono-nutrition.js';
        $additionalStylesheets = ['/projet-web-25-26/view/front/assets/css/chrono-nutrition.css?v=' . filemtime($chronoStylesheet)];
        $additionalScripts = ['/projet-web-25-26/view/front/assets/js/chrono-nutrition.js?v=' . filemtime($chronoScript)];

        require __DIR__ . '/../view/layouts/header.php';
        require __DIR__ . '/../view/front/nutrition/chrono.php';
        require __DIR__ . '/../view/layouts/footer.php';
    }

    public function chrono_profile_get() {
        $userId = $this->requireFrontUserId();
        $this->respondJson($this->model->getProfile($userId));
    }

    public function chrono_profile_save() {
        $userId = $this->requireFrontUserId();
        $data = json_decode(file_get_contents('php://input'), true);
        $this->respondJson($this->model->saveProfile($userId, is_array($data) ? $data : []));
    }

    public function chrono_optimal_timing() {
        $userId = $this->requireFrontUserId();
        $this->respondJson($this->service->getOptimalTiming($userId));
    }

    public function chrono_fasting_window() {
        $userId = $this->requireFrontUserId();
        $this->respondJson($this->service->getFastingWindow($userId));
    }

    public function chrono_nutrient_timing() {
        $this->respondJson($this->service->getNutrientTiming());
    }

    public function chrono_sleep_sync() {
        $userId = $this->requireFrontUserId();
        $data = json_decode(file_get_contents('php://input'), true);
        $this->respondJson($this->service->sleepSync($userId, is_array($data) ? $data : []));
    }

    private function requireFrontUserId(): int {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $role = (string) ($_SESSION['user_role'] ?? 'user');

        if ($userId <= 0) {
            $this->respondJson([
                'success' => false,
                'message' => 'Session invalide.',
            ]);
        }

        if ($role === 'admin') {
            $this->respondJson([
                'success' => false,
                'message' => 'Acces front office refuse.',
            ]);
        }

        return $userId;
    }

    private function respondJson(array $payload): void {
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
    }
}
