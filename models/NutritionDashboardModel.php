<?php

class NutritionDashboardModel
{
    private $pdo;
    private $tableExistsCache = [];
    private $columnExistsCache = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUserProfile(int $userId): array
    {
        if (!$this->tableExists('utilisateur')) {
            return $this->getFallbackProfileFromObjectif($userId);
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    id,
                    nom,
                    email,
                    age,
                    poids,
                    taille,
                    objectif_calories
                FROM utilisateur
                WHERE id = :userId
                LIMIT 1
            ");
            $stmt->execute([
                'userId' => $userId,
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'id' => (int) ($row['id'] ?? $userId),
                'name' => trim((string) ($row['nom'] ?? 'Utilisateur')),
                'email' => trim((string) ($row['email'] ?? '')),
                'age' => isset($row['age']) ? (int) $row['age'] : null,
                'weight_kg' => isset($row['poids']) ? (float) $row['poids'] : null,
                'height_cm' => isset($row['taille']) ? (float) $row['taille'] : null,
                'target_calories' => isset($row['objectif_calories']) ? (float) $row['objectif_calories'] : 2000.0,
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());

            return [
                'id' => $userId,
                'name' => 'Utilisateur',
                'email' => '',
                'age' => null,
                'weight_kg' => null,
                'height_cm' => null,
                'target_calories' => 2000.0,
            ];
        }
    }

    public function getDailyObjective(int $userId, ?string $date = null): array
    {
        $profile = $this->getUserProfile($userId);
        $date = $date ?: date('Y-m-d');

        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    id,
                    calories_cible,
                    proteines
                FROM objectif
                WHERE DATE(date_creation) = :targetDate
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([
                'targetDate' => $date,
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log($e->getMessage());
            $row = [];
        }

        $weightKg = (float) ($profile['weight_kg'] ?? 70);

        return [
            'objective_id' => isset($row['id']) ? (int) $row['id'] : null,
            'target_calories' => isset($row['calories_cible'])
                ? (float) $row['calories_cible']
                : (float) ($profile['target_calories'] ?? 2000),
            'protein_target_g' => isset($row['proteines']) && (float) $row['proteines'] > 0
                ? (float) $row['proteines']
                : round(max(50, $weightKg * 1.2), 1),
        ];
    }

    public function getTodayNutritionData(int $userId): array
    {
        $hasUserIdColumn = $this->columnExists('repas_consomme', 'user_id');
        $row = $this->getTodayMealsAggregate($userId, $hasUserIdColumn);
        $waterMl = $this->getTodayHydration($userId);

        $objective = $this->getDailyObjective($userId);
        $profile = $this->getUserProfile($userId);
        $weightKg = (float) ($profile['weight_kg'] ?? 70);

        return [
            'date' => date('Y-m-d'),
            // TODO: replace meal_entries with a true meal/session count if a meal_group_id is introduced later.
            'meal_count' => (int) ($row['meal_entries'] ?? 0),
            'total_calories' => round((float) ($row['total_calories'] ?? 0), 1),
            'proteins_g' => round((float) ($row['proteins_g'] ?? 0), 1),
            'carbs_g' => round((float) ($row['carbs_g'] ?? 0), 1),
            'fats_g' => round((float) ($row['fats_g'] ?? 0), 1),
            'water_ml' => $waterMl,
            'target_calories' => (float) ($objective['target_calories'] ?? 2000),
            'protein_target_g' => (float) ($objective['protein_target_g'] ?? max(50, $weightKg * 1.2)),
            'hydration_target_ml' => 2000,
        ];
    }

    public function getTodayHydration(int $userId): int
    {
        $hasUserIdColumn = $this->columnExists('repas_consomme', 'user_id');
        $hasIsWaterColumn = $this->columnExists('repas_consomme', 'is_water');
        $hasObjectifColumn = $this->columnExists('repas_consomme', 'objectif_id');

        if (!$this->columnExists('repas_consomme', 'eau_ml')) {
            return 0;
        }

        try {
            $objective = $this->getDailyObjective($userId);
            $query = "
                SELECT COALESCE(SUM(eau_ml), 0) AS total_eau_ml
                FROM repas_consomme
                WHERE DATE(date_consommation) = CURDATE()
            ";
            $params = [];

            if ($hasIsWaterColumn) {
                $query .= " AND is_water = 1";
            }

            if ($hasUserIdColumn) {
                $query .= " AND user_id = :userId";
                $params['userId'] = $userId;
            } elseif ($hasObjectifColumn && !empty($objective['objective_id'])) {
                $query .= " AND objectif_id = :objectiveId";
                $params['objectiveId'] = (int) $objective['objective_id'];
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return (int) ($stmt->fetchColumn() ?: 0);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    private function getTodayMealsAggregate(int $userId, bool $hasUserIdColumn): array
    {
        try {
            $query = "
                SELECT
                    COUNT(*) AS meal_entries,
                    COALESCE(SUM(r.calories_calculees), 0) AS total_calories,
                    COALESCE(SUM(
                        CASE
                            WHEN COALESCE(a.unite, 'g') = 'piece' THEN a.proteines * r.quantite
                            ELSE a.proteines * r.quantite / 100
                        END
                    ), 0) AS proteins_g,
                    COALESCE(SUM(
                        CASE
                            WHEN COALESCE(a.unite, 'g') = 'piece' THEN a.glucides * r.quantite
                            ELSE a.glucides * r.quantite / 100
                        END
                    ), 0) AS carbs_g,
                    COALESCE(SUM(
                        CASE
                            WHEN COALESCE(a.unite, 'g') = 'piece' THEN a.lipides * r.quantite
                            ELSE a.lipides * r.quantite / 100
                        END
                    ), 0) AS fats_g
                FROM repas_consomme r
                JOIN aliments a ON a.id = r.aliment_id
                WHERE DATE(r.date_consommation) = CURDATE()
            ";

            $params = [];

            if ($hasUserIdColumn) {
                $query .= " AND r.user_id = :userId";
                $params['userId'] = $userId;
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getWeeklyNutritionData(int $userId, int $days = 7): array
    {
        $days = max(1, (int) $days);
        $startDate = (new DateTimeImmutable('today'))
            ->modify('-' . ($days - 1) . ' days')
            ->format('Y-m-d');
        $hasUserIdColumn = $this->columnExists('repas_consomme', 'user_id');

        try {
            $query = "
                SELECT
                    DATE(r.date_consommation) AS log_date,
                    COALESCE(SUM(r.calories_calculees), 0) AS total_calories,
                    COALESCE(SUM(
                        CASE
                            WHEN COALESCE(a.unite, 'g') = 'piece' THEN a.proteines * r.quantite
                            ELSE a.proteines * r.quantite / 100
                        END
                    ), 0) AS total_protein,
                    COUNT(*) AS meal_items
                FROM repas_consomme r
                JOIN aliments a ON a.id = r.aliment_id
            ";

            $params = [
                'startDate' => $startDate,
            ];

            if ($hasUserIdColumn) {
                $query .= " WHERE r.user_id = :userId AND DATE(r.date_consommation) >= :startDate";
                $params['userId'] = $userId;
            } else {
                $query .= " WHERE DATE(r.date_consommation) >= :startDate";
            }

            $query .= "
                GROUP BY DATE(r.date_consommation)
                ORDER BY DATE(r.date_consommation) ASC
            ";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getLastLoggedConsumptionDate(int $userId): ?string
    {
        $hasUserIdColumn = $this->columnExists('repas_consomme', 'user_id');

        try {
            $query = "
                SELECT MAX(DATE(date_consommation))
                FROM repas_consomme
            ";

            $params = [];

            if ($hasUserIdColumn) {
                $query .= " WHERE user_id = :userId";
                $params['userId'] = $userId;
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            $value = $stmt->fetchColumn();

            return $value !== false && $value !== null ? (string) $value : null;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function hasReminderBeenSentToday(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 1
                FROM reminder_logs
                WHERE user_id = :userId
                  AND sent_date = CURDATE()
                LIMIT 1
            ");
            $stmt->execute([
                'userId' => $userId,
            ]);

            return (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function ensureReminderLogsTable(): bool
    {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS reminder_logs (
                    user_id INT NOT NULL,
                    sent_date DATE NOT NULL,
                    PRIMARY KEY (user_id, sent_date)
                )
            ");

            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function markReminderSent(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO reminder_logs (user_id, sent_date)
                VALUES (:userId, CURDATE())
                ON DUPLICATE KEY UPDATE sent_date = VALUES(sent_date)
            ");

            return $stmt->execute([
                'userId' => $userId,
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function getFallbackProfileFromObjectif(int $userId): array
    {
        try {
            if (!$this->tableExists('objectif')) {
                throw new RuntimeException('Table objectif introuvable');
            }

            $stmt = $this->pdo->query("
                SELECT
                    calories_cible,
                    poids,
                    taille,
                    age
                FROM objectif
                ORDER BY id DESC
                LIMIT 1
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'id' => $userId,
                'name' => 'Utilisateur test',
                'email' => '',
                'age' => isset($row['age']) ? (int) $row['age'] : null,
                'weight_kg' => isset($row['poids']) ? (float) $row['poids'] : null,
                'height_cm' => isset($row['taille']) ? (float) $row['taille'] : null,
                'target_calories' => isset($row['calories_cible']) ? (float) $row['calories_cible'] : 2000.0,
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());

            return [
                'id' => $userId,
                'name' => 'Utilisateur',
                'email' => '',
                'age' => null,
                'weight_kg' => null,
                'height_cm' => null,
                'target_calories' => 2000.0,
            ];
        }
    }

    private function tableExists(string $tableName): bool
    {
        if (array_key_exists($tableName, $this->tableExistsCache)) {
            return $this->tableExistsCache[$tableName];
        }

        try {
            $stmt = $this->pdo->prepare('SHOW TABLES LIKE ?');
            $stmt->execute([$tableName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $exists = false;
        }

        $this->tableExistsCache[$tableName] = $exists;
        return $exists;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $cacheKey = $tableName . '.' . $columnName;

        if (array_key_exists($cacheKey, $this->columnExistsCache)) {
            return $this->columnExistsCache[$cacheKey];
        }

        if (!$this->tableExists($tableName)) {
            $this->columnExistsCache[$cacheKey] = false;
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
            $stmt->execute([$columnName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $exists = false;
        }

        $this->columnExistsCache[$cacheKey] = $exists;
        return $exists;
    }
}
