<?php

require_once __DIR__ . '/../services/PredictionService.php';

class PredictionController
{
    private $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new PredictionService($pdo);
    }

    public function index(): void
    {
        $this->prediction_dashboard();
    }

    public function prediction_dashboard(): void
    {
        require __DIR__ . '/../views/front/nutrition/prediction.php';
    }

    public function prediction_weekly_trend(): void
    {
        $this->success($this->service->getWeeklyTrend($this->getCurrentUserId()));
    }

    public function prediction_scenarios(): void
    {
        $this->success($this->service->getScenarios($this->getCurrentUserId()));
    }

    public function prediction_goal_date(): void
    {
        $this->success($this->service->getGoalDate($this->getCurrentUserId()));
    }

    public function prediction_confidence(): void
    {
        $this->success($this->service->getConfidence($this->getCurrentUserId()));
    }

    public function prediction_what_if(): void
    {
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            $this->error('Methode non autorisee.', 405);
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $dailyCalorieChange = $payload['daily_calorie_change'] ?? null;
        $validatedValue = filter_var($dailyCalorieChange, FILTER_VALIDATE_INT);

        if ($validatedValue === false || $validatedValue < -1000 || $validatedValue > 1000) {
            $this->error("daily_calorie_change doit \u{00EA}tre entre -1000 et +1000");
        }

        $this->success($this->service->simulateWhatIf($this->getCurrentUserId(), (int) $validatedValue));
    }

    private function getCurrentUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 1);
    }

    private function success(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'data' => $data,
            'error' => null,
            'cached' => false,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function error(string $message, int $statusCode = 400): void
    {
        header('Content-Type: application/json; charset=UTF-8', true, $statusCode);
        echo json_encode([
            'data' => null,
            'error' => $message,
            'cached' => false,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
