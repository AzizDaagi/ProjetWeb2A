<?php

require_once __DIR__ . '/../model/NutritionDashboardService.php';

class NutritionDashboardController
{
    private $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new NutritionDashboardService($pdo);
    }

    public function index(): void
    {
        $this->dashboard();
    }

    public function dashboard(): void
    {
        require __DIR__ . '/../view/front/nutrition/dashboard.php';
    }

    public function nutrition_dashboard(): void
    {
        $this->dashboard();
    }

    public function nutrition_dashboard_summary(): void
    {
        $userId = $this->getCurrentUserId();
        $this->success($this->service->getDashboardSummary($userId), false, $userId);
    }

    public function nutrition_health_score(): void
    {
        $userId = $this->getCurrentUserId();
        $this->success($this->service->getHealthScore($userId), false, $userId);
    }

    public function nutrition_daily_recommendations(): void
    {
        $userId = $this->getCurrentUserId();
        $this->success($this->service->getDailyRecommendations($userId), false, $userId);
    }

    public function nutrition_weekly_analysis(): void
    {
        $userId = $this->getCurrentUserId();
        $days = $this->resolveDaysParameter();
        $this->success($this->service->getWeeklyAnalysis($userId, $days), false, $userId);
    }

    public function nutrition_smart_reminder(): void
    {
        $userId = $this->getCurrentUserId();
        $this->success($this->service->getSmartReminder($userId), false, $userId);
    }

    public function weeklyAnalysis(): void
    {
        $this->nutrition_weekly_analysis();
    }

    public function smartReminder(): void
    {
        $this->nutrition_smart_reminder();
    }

    private function getCurrentUserId(): int
    {
        // TODO: retirer le fallback user_id = 1 lors de l'integration finale de l'authentification.
        return (int) ($_SESSION['user_id'] ?? 1);
    }

    private function resolveDaysParameter(): int
    {
        $days = $_GET['days'] ?? $_POST['days'] ?? 7;
        $days = (int) $days;

        if ($days <= 0) {
            return 7;
        }

        return min($days, 30);
    }

    private function success($data, bool $cached = false, ?int $debugUserId = null): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $payload = [
            'data' => $data,
            'error' => null,
            'cached' => $cached,
        ];

        if ($this->isDevelopment() && $debugUserId !== null) {
            $payload['debug_user_id'] = $debugUserId;
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
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

    private function isDevelopment(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        return $host === '' || stripos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;
    }
}
