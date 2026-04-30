<?php

class StatsService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getKpiCalories(int $userId): array
    {
        // The current local schema does not yet expose user_id on these tables.
        // Keep the userId signature so this service can evolve cleanly to multi-user support.
        $sql = "
            SELECT
                COALESCE(SUM(CASE WHEN daily.total > daily.objectif THEN 1 ELSE 0 END), 0) AS jours_over,
                COALESCE(SUM(CASE WHEN daily.total <= daily.objectif THEN 1 ELSE 0 END), 0) AS jours_ok,
                COALESCE(ROUND(AVG(daily.total)), 0) AS moyenne,
                COALESCE(MAX(daily.total), 0) AS pic,
                COALESCE(MAX(daily.objectif), 2000) AS objectif
            FROM (
                SELECT
                    r.date_consommation,
                    SUM(r.calories_calculees) AS total,
                    COALESCE(MAX(o.calories_cible), 2000) AS objectif
                FROM repas_consomme r
                LEFT JOIN objectif o ON r.objectif_id = o.id
                WHERE r.date_consommation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY r.date_consommation
            ) AS daily
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'jours_ok' => (int) ($result['jours_ok'] ?? 0),
            'jours_over' => (int) ($result['jours_over'] ?? 0),
            'moyenne' => (int) ($result['moyenne'] ?? 0),
            'pic' => (int) ($result['pic'] ?? 0),
            'objectif' => (int) ($result['objectif'] ?? 2000),
        ];
    }

    public function getMonthlyGoalProgress(int $userId): array
    {
        $sql = "
            SELECT
                daily.date_consommation,
                daily.total,
                daily.objectif
            FROM (
                SELECT
                    r.date_consommation,
                    SUM(r.calories_calculees) AS total,
                    COALESCE(MAX(o.calories_cible), 2000) AS objectif
                FROM repas_consomme r
                LEFT JOIN objectif o ON r.objectif_id = o.id
                WHERE r.date_consommation >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                  AND r.date_consommation <= CURDATE()
                GROUP BY r.date_consommation
            ) AS daily
        ";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $daysElapsed = (int) date('j');
        $successDays = 0;
        $objectif = 2000;

        foreach ($rows as $row) {
            $dayTotal = (float) ($row['total'] ?? 0);
            $objectif = (int) ($row['objectif'] ?? $objectif);

            if ($dayTotal <= $objectif) {
                $successDays++;
            }
        }

        $missedDays = max(0, $daysElapsed - $successDays);
        $rate = $daysElapsed > 0
            ? (int) round(($successDays / $daysElapsed) * 100)
            : 0;

        return [
            'days_elapsed' => $daysElapsed,
            'success_days' => $successDays,
            'missed_days' => $missedDays,
            'rate' => $rate,
            'objectif' => $objectif,
        ];
    }
}
