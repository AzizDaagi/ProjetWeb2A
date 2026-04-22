<?php

require_once __DIR__ . '/../models/objectif.php';
require_once __DIR__ . '/../models/suivi.php';
require_once __DIR__ . '/../services/StatsService.php';

class statsCtrl
{
    private $pdo;
    private $statsService;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->statsService = new StatsService($pdo);
    }

    public function index()
    {
        $suiviModel = new Suivi($this->pdo);
        $objectifModel = new Objectif($this->pdo);
        $userId = !empty($_SESSION['user_id'])
            ? (int) $_SESSION['user_id']
            : (isset($_GET['user_id']) ? (int) $_GET['user_id'] : 1);

        $data30 = $suiviModel->getLast30Days();
        $objectif = $objectifModel->getLatest();
        $monthlyGoal = $this->statsService->getMonthlyGoalProgress($userId);

        require __DIR__ . '/../views/front/stats/index.php';
    }

    public function kpiJson()
    {
        $userId = !empty($_SESSION['user_id'])
            ? (int) $_SESSION['user_id']
            : (isset($_GET['user_id']) ? (int) $_GET['user_id'] : 1);

        $kpis = $this->statsService->getKpiCalories($userId);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($kpis);
        exit;
    }
}
