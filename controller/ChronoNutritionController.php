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
        require __DIR__ . '/../view/front/nutrition/chrono.php';
    }

    public function chrono_profile_get() {
        $userId = $_SESSION['user_id'] ?? 1;
        $this->respondJson($this->model->getProfile($userId));
    }

    public function chrono_profile_save() {
        $userId = $_SESSION['user_id'] ?? 1;
        $data = json_decode(file_get_contents('php://input'), true);
        $this->respondJson($this->model->saveProfile($userId, is_array($data) ? $data : []));
    }

    public function chrono_optimal_timing() {
        $userId = $_SESSION['user_id'] ?? 1;
        $this->respondJson($this->service->getOptimalTiming($userId));
    }

    public function chrono_fasting_window() {
        $userId = $_SESSION['user_id'] ?? 1;
        $this->respondJson($this->service->getFastingWindow($userId));
    }

    public function chrono_nutrient_timing() {
        $this->respondJson($this->service->getNutrientTiming());
    }

    public function chrono_sleep_sync() {
        $userId = $_SESSION['user_id'] ?? 1;
        $data = json_decode(file_get_contents('php://input'), true);
        $this->respondJson($this->service->sleepSync($userId, is_array($data) ? $data : []));
    }

    private function respondJson(array $payload): void {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
    }
}
