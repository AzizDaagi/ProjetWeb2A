<?php

require_once 'models/ChronoNutritionModel.php';
require_once 'models/ChronoNutritionService.php';

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
        require 'views/front/nutrition/chrono.php';
    }

    public function chrono_profile_get() {
        $userId = $_SESSION['user_id'] ?? 1;
        echo json_encode($this->model->getProfile($userId));
    }

    public function chrono_profile_save() {
        $userId = $_SESSION['user_id'] ?? 1;
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($this->model->saveProfile($userId, $data));
    }

    public function chrono_optimal_timing() {
        $userId = $_SESSION['user_id'] ?? 1;
        echo json_encode($this->service->getOptimalTiming($userId));
    }

    public function chrono_fasting_window() {
        $userId = $_SESSION['user_id'] ?? 1;
        echo json_encode($this->service->getFastingWindow($userId));
    }

    public function chrono_nutrient_timing() {
        echo json_encode($this->service->getNutrientTiming());
    }

    public function chrono_sleep_sync() {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($this->service->sleepSync($data));
    }
}
