<?php

class PredictionModel
{
    private $pdo;
    private $tableExistsCache = [];
    private $columnExistsCache = [];
    private $targetWeightColumns = [
        'poids_cible',
        'objectif_poids',
        'poids_objectif',
        'target_weight',
        'goal_weight',
    ];
    private $startWeightColumns = [
        'poids_depart',
        'poids_initial',
        'start_weight',
        'initial_weight',
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getCurrentObjective(int $userId): ?array
    {
        if (!$this->tableExists('objectif')) {
            return null;
        }

        $row = $this->fetchObjectiveRow($userId, 'DATE(date_creation) = CURDATE()');

        if ($row === null) {
            $row = $this->fetchObjectiveRow($userId, 'DATE(date_creation) <= CURDATE()');
        }

        if ($row === null) {
            $row = $this->fetchObjectiveRow($userId);
        }

        if ($row === null) {
            return null;
        }

        return $this->normalizeObjectiveRow($userId, $row);
    }

    public function getSelectedProjectionObjectiveId(int $userId, int $days = 28): ?int
    {
        if (
            !$this->tableExists('repas_consomme') ||
            !$this->tableExists('objectif') ||
            !$this->columnExists('repas_consomme', 'objectif_id')
        ) {
            return null;
        }

        $days = max(1, (int) $days);
        $startDate = (new DateTimeImmutable('today'))
            ->modify('-' . ($days - 1) . ' days')
            ->format('Y-m-d');
        $hasRepasUserId = $this->columnExists('repas_consomme', 'user_id');
        $hasObjectifUserId = $this->columnExists('objectif', 'user_id');

        try {
            $query = "
                SELECT r.objectif_id
                FROM repas_consomme r
                INNER JOIN objectif o ON o.id = r.objectif_id
                WHERE DATE(r.date_consommation) >= :startDate
                  AND r.objectif_id IS NOT NULL
            ";
            $params = [
                'startDate' => $startDate,
            ];

            if ($hasRepasUserId) {
                $query .= " AND r.user_id = :userId ";
                $params['userId'] = $userId;
            } elseif ($hasObjectifUserId) {
                $query .= " AND o.user_id = :userId ";
                $params['userId'] = $userId;
            }

            $query .= "
                GROUP BY r.objectif_id
                ORDER BY r.objectif_id DESC
                LIMIT 1
            ";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $value = $stmt->fetchColumn();

            return is_numeric($value) ? (int) $value : null;
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return null;
        }
    }

    public function getObjectiveById(int $userId, int $objectiveId): ?array
    {
        if (!$this->tableExists('objectif')) {
            return null;
        }

        try {
            $query = "SELECT * FROM objectif WHERE id = :objectiveId";
            $params = [
                'objectiveId' => $objectiveId,
            ];

            if ($this->columnExists('objectif', 'user_id')) {
                $query .= " AND user_id = :userId";
                $params['userId'] = $userId;
            }

            $query .= " LIMIT 1";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->normalizeObjectiveRow($userId, $row);
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return null;
        }
    }

    public function getRecentDailyLogs(int $userId, int $days = 28): array
    {
        if (!$this->tableExists('repas_consomme')) {
            return [];
        }

        $days = max(1, (int) $days);
        $startDate = (new DateTimeImmutable('today'))
            ->modify('-' . ($days - 1) . ' days')
            ->format('Y-m-d');
        $hasRepasUserId = $this->columnExists('repas_consomme', 'user_id');
        $hasObjectifId = $this->columnExists('repas_consomme', 'objectif_id');
        $hasObjectifTable = $this->tableExists('objectif');
        $hasObjectifUserId = $hasObjectifTable && $this->columnExists('objectif', 'user_id');

        try {
            $query = "
                SELECT
                    DATE(r.date_consommation) AS log_date,
                    COALESCE(SUM(r.calories_calculees), 0) AS consumed_calories,
            ";

            if ($hasObjectifId && $hasObjectifTable) {
                $query .= "
                    COALESCE(MAX(o.calories_cible), 0) AS target_calories,
                    MAX(r.objectif_id) AS objectif_id
                ";
            } else {
                $query .= "
                    0 AS target_calories,
                    NULL AS objectif_id
                ";
            }

            $query .= "
                FROM repas_consomme r
            ";

            if ($hasObjectifId && $hasObjectifTable) {
                $query .= " LEFT JOIN objectif o ON o.id = r.objectif_id ";
            }

            $query .= "
                WHERE DATE(r.date_consommation) >= :startDate
            ";

            $params = [
                'startDate' => $startDate,
            ];

            if ($hasRepasUserId) {
                $query .= " AND r.user_id = :userId ";
                $params['userId'] = $userId;
            } elseif ($hasObjectifId && $hasObjectifUserId) {
                $query .= " AND o.user_id = :userId ";
                $params['userId'] = $userId;
            }

            $query .= "
                GROUP BY DATE(r.date_consommation)
                ORDER BY DATE(r.date_consommation) ASC
            ";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return array_map(function (array $row) {
                return [
                    'log_date' => (string) ($row['log_date'] ?? ''),
                    'consumed_calories' => round((float) ($row['consumed_calories'] ?? 0), 1),
                    'target_calories' => round((float) ($row['target_calories'] ?? 0), 1),
                    'objectif_id' => isset($row['objectif_id']) ? (int) $row['objectif_id'] : null,
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }

    public function getRecentDailyLogsForObjective(int $userId, int $objectiveId, int $days = 28): array
    {
        if (
            !$this->tableExists('repas_consomme') ||
            !$this->columnExists('repas_consomme', 'objectif_id')
        ) {
            return [];
        }

        $days = max(1, (int) $days);
        $startDate = (new DateTimeImmutable('today'))
            ->modify('-' . ($days - 1) . ' days')
            ->format('Y-m-d');
        $hasRepasUserId = $this->columnExists('repas_consomme', 'user_id');
        $hasObjectifTable = $this->tableExists('objectif');
        $hasObjectifUserId = $hasObjectifTable && $this->columnExists('objectif', 'user_id');

        try {
            $query = "
                SELECT
                    DATE(r.date_consommation) AS log_date,
                    COALESCE(SUM(r.calories_calculees), 0) AS consumed_calories,
            ";

            if ($hasObjectifTable) {
                $query .= "
                    COALESCE(MAX(o.calories_cible), 0) AS target_calories,
                    MAX(r.objectif_id) AS objectif_id
                ";
            } else {
                $query .= "
                    0 AS target_calories,
                    MAX(r.objectif_id) AS objectif_id
                ";
            }

            $query .= "
                FROM repas_consomme r
            ";

            if ($hasObjectifTable) {
                $query .= " LEFT JOIN objectif o ON o.id = r.objectif_id ";
            }

            $query .= "
                WHERE DATE(r.date_consommation) >= :startDate
                  AND r.objectif_id = :objectiveId
            ";

            $params = [
                'startDate' => $startDate,
                'objectiveId' => $objectiveId,
            ];

            if ($hasRepasUserId) {
                $query .= " AND r.user_id = :userId ";
                $params['userId'] = $userId;
            } elseif ($hasObjectifTable && $hasObjectifUserId) {
                $query .= " AND o.user_id = :userId ";
                $params['userId'] = $userId;
            }

            $query .= "
                GROUP BY DATE(r.date_consommation)
                ORDER BY DATE(r.date_consommation) ASC
            ";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return array_map(function (array $row) {
                return [
                    'log_date' => (string) ($row['log_date'] ?? ''),
                    'consumed_calories' => round((float) ($row['consumed_calories'] ?? 0), 1),
                    'target_calories' => round((float) ($row['target_calories'] ?? 0), 1),
                    'objectif_id' => isset($row['objectif_id']) ? (int) $row['objectif_id'] : null,
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }

    private function fetchObjectiveRow(int $userId, ?string $extraCondition = null): ?array
    {
        try {
            $query = "SELECT * FROM objectif";
            $clauses = [];
            $params = [];

            if ($this->columnExists('objectif', 'user_id')) {
                $clauses[] = 'user_id = :userId';
                $params['userId'] = $userId;
            }

            if ($extraCondition !== null) {
                $clauses[] = $extraCondition;
            }

            if (!empty($clauses)) {
                $query .= ' WHERE ' . implode(' AND ', $clauses);
            }

            $query .= ' ORDER BY date_creation DESC, id DESC LIMIT 1';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            return null;
        }
    }

    private function normalizeObjectiveRow(int $userId, array $row): array
    {
        $row['objective_id'] = isset($row['id']) ? (int) $row['id'] : null;
        $row['target_calories'] = isset($row['calories_cible']) ? (float) $row['calories_cible'] : null;
        $row['poids'] = isset($row['poids']) && is_numeric($row['poids']) ? (float) $row['poids'] : null;
        $row['poids_cible'] = $this->extractPositiveNumericValue($row, ['poids_cible']);
        $row['current_weight'] = $this->resolveCurrentWeight($userId, $row);
        $row['target_weight'] = $this->resolveTargetWeight($userId, $row);
        $row['start_weight'] = $this->resolveStartWeight($userId, $row);
        $row['objective_type'] = trim((string) ($row['objectif_type'] ?? ''));

        return $row;
    }

    private function resolveCurrentWeight(int $userId, array $objectiveRow): ?float
    {
        $rowValue = $this->extractPositiveNumericValue($objectiveRow, ['poids', 'current_weight', 'weight']);

        if ($rowValue !== null) {
            return $rowValue;
        }

        return $this->fetchUserValue($userId, ['poids', 'current_weight', 'weight']);
    }

    private function resolveTargetWeight(int $userId, array $objectiveRow): ?float
    {
        $rowValue = $this->extractPositiveNumericValue($objectiveRow, $this->targetWeightColumns);

        if ($rowValue !== null) {
            return $rowValue;
        }

        return $this->fetchUserValue($userId, $this->targetWeightColumns);
    }

    private function resolveStartWeight(int $userId, array $objectiveRow): ?float
    {
        $rowValue = $this->extractPositiveNumericValue($objectiveRow, $this->startWeightColumns);

        if ($rowValue !== null) {
            return $rowValue;
        }

        return $this->fetchUserValue($userId, $this->startWeightColumns);
    }

    private function extractPositiveNumericValue(array $source, array $columns): ?float
    {
        foreach ($columns as $column) {
            if (!array_key_exists($column, $source)) {
                continue;
            }

            if (!is_numeric($source[$column])) {
                continue;
            }

            $value = (float) $source[$column];

            if ($value > 0) {
                return $value;
            }
        }

        return null;
    }

    private function fetchUserValue(int $userId, array $candidateColumns): ?float
    {
        foreach (['users', 'utilisateur'] as $tableName) {
            if (!$this->tableExists($tableName)) {
                continue;
            }

            foreach ($candidateColumns as $column) {
                if (!$this->columnExists($tableName, $column)) {
                    continue;
                }

                try {
                    $stmt = $this->pdo->prepare("
                        SELECT {$column}
                        FROM {$tableName}
                        WHERE id = :userId
                        LIMIT 1
                    ");
                    $stmt->execute([
                        'userId' => $userId,
                    ]);

                    $value = $stmt->fetchColumn();

                    if (is_numeric($value) && (float) $value > 0) {
                        return (float) $value;
                    }
                } catch (Exception $exception) {
                    error_log($exception->getMessage());
                    return null;
                }
            }
        }

        return null;
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
        } catch (Exception $exception) {
            error_log($exception->getMessage());
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
        } catch (Exception $exception) {
            error_log($exception->getMessage());
            $exists = false;
        }

        $this->columnExistsCache[$cacheKey] = $exists;
        return $exists;
    }
}
