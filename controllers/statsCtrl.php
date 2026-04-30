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

        $caloriesParJour = $suiviModel->getCaloriesParJour();
        $objectifsParJour = $objectifModel->getObjectifsParJour();
        $chartData = $this->buildSevenDayChartData($caloriesParJour, $objectifsParJour);
        $objectif = $objectifModel->getObjectifDuJour();
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

    private function buildSevenDayChartData(array $caloriesRows, array $objectifRows): array
    {
        $caloriesByDate = $this->indexByDate($caloriesRows, 'jour', 'total');
        $objectifsByDate = $this->indexByDate($objectifRows, 'jour', 'calories_cible');
        $dates = $this->resolveChartDates($objectifRows);
        $chartData = [];

        foreach ($dates as $date) {
            $chartData[] = [
                'jour' => $date,
                'consomme' => (int) round((float) ($caloriesByDate[$date] ?? 0)),
                'objectif' => array_key_exists($date, $objectifsByDate)
                    ? (int) round((float) $objectifsByDate[$date])
                    : null,
            ];
        }

        return $chartData;
    }

    private function indexByDate(array $rows, string $dateKey, string $valueKey): array
    {
        $indexedRows = [];

        foreach ($rows as $row) {
            $date = $this->normalizeDate($row[$dateKey] ?? null);

            if ($date === null) {
                continue;
            }

            $indexedRows[$date] = isset($row[$valueKey]) ? (float) $row[$valueKey] : null;
        }

        return $indexedRows;
    }

    private function resolveChartDates(array $objectifRows): array
    {
        $planDates = [];

        foreach ($objectifRows as $objectifRow) {
            $date = $this->normalizeDate($objectifRow['jour'] ?? null);

            if ($date !== null && !in_array($date, $planDates, true)) {
                $planDates[] = $date;
            }
        }

        sort($planDates);

        if (!empty($planDates)) {
            $startDate = new DateTimeImmutable($planDates[0]);
            $dates = [];

            for ($index = 0; $index < 7; $index++) {
                $dates[] = $startDate->modify('+' . $index . ' day')->format('Y-m-d');
            }

            return $dates;
        }

        $today = new DateTimeImmutable(date('Y-m-d'));
        $dates = [];

        for ($index = 6; $index >= 0; $index--) {
            $dates[] = $today->modify('-' . $index . ' day')->format('Y-m-d');
        }

        return $dates;
    }

    private function normalizeDate($date): ?string
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        $date = trim((string) $date);

        if ($date === '') {
            return null;
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }
}
