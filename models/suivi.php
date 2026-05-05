<?php

class Suivi
{
    private $pdo;
    private $lastError;
    private $columnExistsCache = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function ajouter($data)
    {
        $mealItem = $this->prepareMealItem($data);

        if ($mealItem === false) {
            return false;
        }

        return $this->insertMealItem($mealItem);
    }

    public function prepareMealItem($data)
    {
        $this->lastError = null;
        $alimentId = $data['aliment_id'] ?? null;
        $quantite = $data['quantite'] ?? 0;
        $eauMl = max(0, (int) ($data['eau_ml'] ?? 0));
        $type = $data['type'] ?? null;
        $date = trim((string) ($data['date_consommation'] ?? date('Y-m-d')));

        if (!$alimentId) {
            $this->lastError = "Veuillez selectionner un aliment valide.";
            return false;
        }

        if (!$this->isValidConsumptionDate($date)) {
            $this->lastError = "Date de consommation invalide.";
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT nom, calories, type, unite FROM aliments WHERE id = ?");
        $stmt->execute([(int) $alimentId]);
        $aliment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$aliment) {
            $this->lastError = "L'aliment selectionne est introuvable.";
            return false;
        }

        $typesAutorises = ['proteine', 'glucide', 'lipide'];

        if (!in_array($type, $typesAutorises, true)) {
            $type = $aliment['type'] ?? null;
        }

        if (!in_array($type, $typesAutorises, true)) {
            $this->lastError = "Type de repas invalide.";
            return false;
        }

        $quantite = (float) $quantite;
        $baseCalories = (float) ($aliment['calories'] ?? 0);
        $unite = $aliment['unite'] ?? 'g';
        $caloriesCalculees = $unite === 'piece'
            ? $baseCalories * $quantite
            : ($baseCalories * $quantite) / 100;

        return [
            'aliment_id' => (int) $alimentId,
            'quantite' => $quantite,
            'eau_ml' => $eauMl,
            'calories' => $caloriesCalculees,
            'calories_calculees' => $caloriesCalculees,
            'type' => $type,
            'date_consommation' => $date,
            'nom' => $aliment['nom'] ?? 'Aliment',
            'unite' => $unite,
        ];
    }

    public function validerRepas(array $items, $date, int $mealWaterMl = 0)
    {
        $this->lastError = null;
        $date = trim((string) $date);
        $mealWaterMl = max(0, $mealWaterMl);

        if (empty($items)) {
            $this->lastError = "Aucun aliment n'a ete ajoute a ce repas.";
            return false;
        }

        if (!$this->isValidConsumptionDate($date)) {
            $this->lastError = "Date de consommation invalide.";
            return false;
        }

        $objectifId = $this->getObjectifIdByDate($date);

        if ($objectifId === null) {
            $this->lastError = "Aucun objectif n'existe pour cette date. Genere d'abord un plan nutritionnel avant de valider ce repas.";
            return false;
        }

        $hasWaterColumn = $this->columnExists('repas_consomme', 'eau_ml');
        $hasIsWaterColumn = $this->columnExists('repas_consomme', 'is_water');
        $stmt = $this->pdo->prepare($this->buildMealInsertQuery($hasWaterColumn, $hasIsWaterColumn));

        try {
            $this->pdo->beginTransaction();

            foreach (array_values($items) as $item) {
                $mealItem = $this->prepareMealItem([
                    'aliment_id' => $item['aliment_id'] ?? null,
                    'quantite' => $item['quantite'] ?? 0,
                    'eau_ml' => 0,
                    'type' => $item['type'] ?? null,
                    'date_consommation' => $date,
                ]);

                if ($mealItem === false) {
                    throw new RuntimeException($this->lastError ?: "Impossible de preparer un aliment du repas.");
                }

                $params = [
                    (int) $mealItem['aliment_id'],
                    (float) $mealItem['quantite'],
                    (float) $mealItem['calories_calculees'],
                    $mealItem['type'],
                    $date,
                    $objectifId,
                ];

                if ($hasWaterColumn) {
                    $params[] = 0;
                }

                if ($hasIsWaterColumn) {
                    $params[] = 0;
                }

                $isInserted = $stmt->execute($params);

                if (!$isInserted) {
                    throw new RuntimeException("Impossible d'enregistrer un aliment du repas.");
                }
            }

            if ($mealWaterMl > 0 && $hasWaterColumn) {
                $waterStmt = $this->pdo->prepare($this->buildWaterInsertQuery($hasIsWaterColumn));
                $waterParams = [
                    $mealWaterMl,
                    $date,
                    $objectifId,
                    $mealWaterMl,
                ];

                if ($hasIsWaterColumn) {
                    $waterParams[] = 1;
                }

                if (!$waterStmt->execute($waterParams)) {
                    throw new RuntimeException("Impossible d'enregistrer l'hydratation du repas.");
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($this->lastError === null) {
                $this->lastError = $exception->getMessage() ?: "Impossible d'enregistrer ce repas pour le moment.";
            }

            return false;
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getTodayTotal()
    {
        $stmt = $this->pdo->query(
            "SELECT SUM(calories_calculees) as total
             FROM repas_consomme
             WHERE date_consommation = CURDATE()"
        );

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function countAllMeals()
    {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM repas_consomme")->fetchColumn();
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function getTotalCaloriesTracked()
    {
        try {
            return (float) $this->pdo->query("SELECT COALESCE(SUM(calories_calculees), 0) FROM repas_consomme")->fetchColumn();
        } catch (PDOException $exception) {
            return 0;
        }
    }

    public function getEvolutionData($days = 7)
    {
        $days = max(1, (int) $days);
        $startDate = (new DateTime())->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
        $rowsByDate = [];

        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    date_consommation,
                    COUNT(*) AS repas_count,
                    COALESCE(SUM(calories_calculees), 0) AS total_calories
                FROM repas_consomme
                WHERE date_consommation >= ?
                GROUP BY date_consommation
                ORDER BY date_consommation ASC
            ");
            $stmt->execute([$startDate]);

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $rowsByDate[$row['date_consommation']] = [
                    'repas_count' => (int) ($row['repas_count'] ?? 0),
                    'total_calories' => (float) ($row['total_calories'] ?? 0),
                ];
            }
        } catch (PDOException $exception) {
            $rowsByDate = [];
        }

        $series = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = (new DateTime())->modify('-' . $offset . ' days')->format('Y-m-d');
            $dayData = $rowsByDate[$date] ?? ['repas_count' => 0, 'total_calories' => 0];

            $series[] = [
                'date' => $date,
                'label' => date('d/m', strtotime($date)),
                'repas_count' => (int) $dayData['repas_count'],
                'total_calories' => (float) $dayData['total_calories'],
            ];
        }

        return $series;
    }

    public function getTotalByDate($date)
    {
        $stmt = $this->pdo->prepare(
            "SELECT SUM(calories_calculees) as total
             FROM repas_consomme
             WHERE date_consommation = ?"
        );
        $stmt->execute([$date]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function getTodayMacros()
    {
        $stmt = $this->pdo->query(
            "SELECT
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(a.unite, 'g') = 'piece' THEN a.proteines * r.quantite
                        ELSE a.proteines * r.quantite / 100
                    END
                ), 0) AS proteines,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(a.unite, 'g') = 'piece' THEN a.glucides * r.quantite
                        ELSE a.glucides * r.quantite / 100
                    END
                ), 0) AS glucides,
                COALESCE(SUM(
                    CASE
                        WHEN COALESCE(a.unite, 'g') = 'piece' THEN a.lipides * r.quantite
                        ELSE a.lipides * r.quantite / 100
                    END
                ), 0) AS lipides
             FROM repas_consomme r
             JOIN aliments a ON r.aliment_id = a.id
             WHERE r.date_consommation = CURDATE()"
        );

        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'proteines' => round((float) ($result['proteines'] ?? 0), 1),
            'glucides' => round((float) ($result['glucides'] ?? 0), 1),
            'lipides' => round((float) ($result['lipides'] ?? 0), 1),
        ];
    }

    public function getHistory(array $filters = [])
    {
        $whereClauses = [];
        $whereParams = [];
        $havingClause = '';

        $whereClauses[] = "DATE(o.date_creation) <= CURDATE()";

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $whereClauses[] = "DATE(o.date_creation) BETWEEN ? AND ?";
            $whereParams[] = $filters['start_date'];
            $whereParams[] = $filters['end_date'];
        } else {
            $whereClauses[] = "DATE(o.date_creation) BETWEEN
                (
                    SELECT DATE_SUB(MAX(DATE(date_creation)), INTERVAL 6 DAY)
                    FROM objectif
                )
                AND
                (
                    CURDATE()
                )";
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'aucune') {
                $havingClause = "HAVING COUNT(r.id) = 0";
            } elseif ($filters['status'] === 'depasse') {
                $havingClause = "HAVING COUNT(r.id) > 0 AND COALESCE(SUM(r.calories_calculees), 0) > o.calories_cible";
            } elseif ($filters['status'] === 'sous') {
                $havingClause = "HAVING COUNT(r.id) > 0 AND COALESCE(SUM(r.calories_calculees), 0) < o.calories_cible";
            } elseif ($filters['status'] === 'ok') {
                $havingClause = "HAVING COUNT(r.id) > 0 AND COALESCE(SUM(r.calories_calculees), 0) = o.calories_cible";
            }
        }

        $whereSql = '';

        if (!empty($whereClauses)) {
            $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
        }

        $stmt = $this->pdo->prepare(
            "SELECT
                DATE(o.date_creation) AS date_consommation,
                o.id AS objectif_id,
                COALESCE(SUM(r.calories_calculees), 0) AS total_calories,
                o.calories_cible AS objectif,
                CASE
                    WHEN COUNT(r.id) = 0 THEN 'aucune'
                    WHEN COALESCE(SUM(r.calories_calculees), 0) > o.calories_cible THEN 'depasse'
                    WHEN COALESCE(SUM(r.calories_calculees), 0) < o.calories_cible THEN 'sous'
                    ELSE 'ok'
                END AS statut
             FROM objectif o
             LEFT JOIN repas_consomme r ON r.objectif_id = o.id
             $whereSql
             GROUP BY o.id, DATE(o.date_creation), o.calories_cible
             $havingClause
             ORDER BY DATE(o.date_creation) DESC, o.id DESC"
        );
        $stmt->execute($whereParams);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLast30Days()
    {
        $stmt = $this->pdo->query("
            SELECT
                r.date_consommation,
                SUM(r.calories_calculees) as total,
                COALESCE(MAX(o.calories_cible), 2000) AS objectif
            FROM repas_consomme r
            LEFT JOIN objectif o ON r.objectif_id = o.id
            WHERE date_consommation >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY r.date_consommation
            ORDER BY r.date_consommation ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCaloriesParJour()
    {
        $stmt = $this->pdo->prepare("
            SELECT
                DATE(date_consommation) AS jour,
                COALESCE(SUM(calories_calculees), 0) AS total
            FROM repas_consomme
            WHERE date_consommation >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(date_consommation)
            ORDER BY jour ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWeeklyStats()
    {
        $stmt = $this->pdo->query("
            SELECT
                r.date_consommation,
                SUM(r.calories_calculees) as total,
                COALESCE(MAX(o.calories_cible), 2000) AS objectif
            FROM repas_consomme r
            LEFT JOIN objectif o ON r.objectif_id = o.id
            WHERE r.date_consommation >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY r.date_consommation
        ");
        $days = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $avg = 0;

        if (count($days) > 0) {
            $sum = array_sum(array_column($days, 'total'));
            $avg = $sum / count($days);
        }

        $success = 0;

        foreach ($days as $day) {
            $objectif = (float) ($day['objectif'] ?? 2000);

            if ((float) $day['total'] <= $objectif) {
                $success++;
            }
        }

        $stmt2 = $this->pdo->query("
            SELECT a.nom, COUNT(*) as total_count
            FROM repas_consomme r
            JOIN aliments a ON r.aliment_id = a.id
            WHERE r.date_consommation >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY a.nom
            ORDER BY total_count DESC
            LIMIT 1
        ");
        $top = $stmt2->fetch(PDO::FETCH_ASSOC)['nom'] ?? 'Aucun';

        return [
            'average' => round($avg),
            'success' => $success,
            'top_aliment' => $top
        ];
    }

    public function getByDate($date)
    {
        $hasWaterColumn = $this->columnExists('repas_consomme', 'eau_ml');
        $hasIsWaterColumn = $this->columnExists('repas_consomme', 'is_water');
        $stmt = $this->pdo->prepare("
            SELECT
                r.id,
                r.aliment_id,
                r.quantite,
                r.calories_calculees,
                " . ($hasWaterColumn ? "COALESCE(r.eau_ml, 0)" : "0") . " AS eau_ml,
                " . ($hasIsWaterColumn ? "COALESCE(r.is_water, 0)" : "0") . " AS is_water,
                COALESCE(r.type, a.type) AS type,
                CASE
                    WHEN " . ($hasIsWaterColumn ? "COALESCE(r.is_water, 0) = 1" : "0 = 1") . " THEN 'ml'
                    ELSE COALESCE(a.unite, 'g')
                END AS unite,
                r.date_consommation,
                CASE
                    WHEN " . ($hasIsWaterColumn ? "COALESCE(r.is_water, 0) = 1" : "0 = 1") . " THEN 'Consommation d''eau'
                    ELSE COALESCE(a.nom, 'Aliment')
                END AS nom
            FROM repas_consomme r
            LEFT JOIN aliments a ON r.aliment_id = a.id
            WHERE date_consommation = ?
            ORDER BY r.id ASC
        ");
        $stmt->execute([$date]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $hasIsWaterColumn = $this->columnExists('repas_consomme', 'is_water');
        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                CASE
                    WHEN " . ($hasIsWaterColumn ? "COALESCE(r.is_water, 0) = 1" : "0 = 1") . " THEN 'Consommation d''eau'
                    ELSE COALESCE(a.nom, 'Aliment')
                END AS nom,
                CASE
                    WHEN " . ($hasIsWaterColumn ? "COALESCE(r.is_water, 0) = 1" : "0 = 1") . " THEN 0
                    ELSE COALESCE(a.calories, 0)
                END AS calories,
                CASE
                    WHEN " . ($hasIsWaterColumn ? "COALESCE(r.is_water, 0) = 1" : "0 = 1") . " THEN 'ml'
                    ELSE COALESCE(a.unite, 'g')
                END AS unite
            FROM repas_consomme r
            LEFT JOIN aliments a ON r.aliment_id = a.id
            WHERE r.id = ?
        ");
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $id = $data['id'] ?? null;
        $quantite = $data['quantite'] ?? 0;

        if (!$id) {
            return false;
        }

        $repas = $this->getById($id);

        if (!$repas) {
            return false;
        }

        $quantite = (float) $quantite;
        $isWaterEntry = (int) ($repas['is_water'] ?? 0) === 1;
        $hasWaterColumn = $this->columnExists('repas_consomme', 'eau_ml');

        if ($isWaterEntry) {
            $stmt = $this->pdo->prepare(
                $hasWaterColumn
                    ? "UPDATE repas_consomme SET quantite = ?, calories_calculees = 0, eau_ml = ? WHERE id = ?"
                    : "UPDATE repas_consomme SET quantite = ?, calories_calculees = 0 WHERE id = ?"
            );

            $params = [$quantite];

            if ($hasWaterColumn) {
                $params[] = (int) round($quantite);
            }

            $params[] = (int) $id;
            $stmt->execute($params);
        } else {
            $caloriesCalculees = ($repas['unite'] ?? 'g') === 'piece'
                ? ((float) $repas['calories']) * $quantite
                : (((float) $repas['calories']) * $quantite) / 100;

            $stmt = $this->pdo->prepare("
                UPDATE repas_consomme
                SET quantite = ?, calories_calculees = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $quantite,
                $caloriesCalculees,
                (int) $id
            ]);
        }

        return $repas['date_consommation'];
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM repas_consomme
            WHERE id = ?
        ");

        return $stmt->execute([(int) $id]);
    }

    private function insertMealItem(array $mealItem)
    {
        $objectifId = $this->getObjectifIdByDate($mealItem['date_consommation'] ?? '');

        if ($objectifId === null) {
            $this->lastError = "Aucun objectif n'existe pour cette date. Genere d'abord un plan nutritionnel avant d'ajouter un repas.";
            return false;
        }

        $hasWaterColumn = $this->columnExists('repas_consomme', 'eau_ml');
        $hasIsWaterColumn = $this->columnExists('repas_consomme', 'is_water');
        $stmt = $this->pdo->prepare($this->buildMealInsertQuery($hasWaterColumn, $hasIsWaterColumn));

        $params = [
            (int) ($mealItem['aliment_id'] ?? 0),
            (float) ($mealItem['quantite'] ?? 0),
            (float) ($mealItem['calories_calculees'] ?? 0),
            $mealItem['type'] ?? null,
            $mealItem['date_consommation'] ?? date('Y-m-d'),
            $objectifId,
        ];

        if ($hasWaterColumn) {
            $params[] = (int) ($mealItem['eau_ml'] ?? 0);
        }

        if ($hasIsWaterColumn) {
            $params[] = 0;
        }

        $isInserted = $stmt->execute($params);

        if (!$isInserted) {
            $this->lastError = "Impossible d'ajouter cette consommation pour le moment.";
        }

        return $isInserted;
    }

    private function buildMealInsertQuery(bool $hasWaterColumn, bool $hasIsWaterColumn): string
    {
        $columns = [
            'aliment_id',
            'quantite',
            'calories_calculees',
            'type',
            'date_consommation',
            'objectif_id',
        ];
        $placeholders = ['?', '?', '?', '?', '?', '?'];

        if ($hasWaterColumn) {
            $columns[] = 'eau_ml';
            $placeholders[] = '?';
        }

        if ($hasIsWaterColumn) {
            $columns[] = 'is_water';
            $placeholders[] = '?';
        }

        return "INSERT INTO repas_consomme (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
    }

    private function buildWaterInsertQuery(bool $hasIsWaterColumn): string
    {
        $columns = [
            'aliment_id',
            'quantite',
            'calories_calculees',
            'type',
            'date_consommation',
            'objectif_id',
            'eau_ml',
        ];
        $placeholders = [
            'NULL',
            '?',
            '0',
            "'eau'",
            '?',
            '?',
            '?',
        ];

        if ($hasIsWaterColumn) {
            $columns[] = 'is_water';
            $placeholders[] = '?';
        }

        return "INSERT INTO repas_consomme (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
    }

    private function isValidConsumptionDate($date)
    {
        $dateObject = DateTime::createFromFormat('Y-m-d', $date);
        $dateErrors = DateTime::getLastErrors();
        $hasDateErrors = is_array($dateErrors)
            && (($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0);

        return $dateObject
            && $dateObject->format('Y-m-d') === $date
            && !$hasDateErrors;
    }

    private function getObjectifIdByDate($date)
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM objectif
            WHERE DATE(date_creation) = ?
            ORDER BY date_creation DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([(string) $date]);

        $objectifId = $stmt->fetchColumn();

        return $objectifId !== false ? (int) $objectifId : null;
    }

    private function columnExists($tableName, $columnName)
    {
        $cacheKey = $tableName . '.' . $columnName;

        if (array_key_exists($cacheKey, $this->columnExistsCache)) {
            return $this->columnExistsCache[$cacheKey];
        }

        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
            $stmt->execute([$columnName]);
            $exists = (bool) $stmt->fetchColumn();
        } catch (Exception $exception) {
            $exists = false;
        }

        $this->columnExistsCache[$cacheKey] = $exists;
        return $exists;
    }
}
