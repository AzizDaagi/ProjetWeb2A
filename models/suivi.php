<?php

class Suivi
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function ajouter($data)
    {
        $alimentId = $data['aliment_id'] ?? null;
        $quantite = $data['quantite'] ?? 0;
        $type = $data['type'] ?? null;
        $date = trim($data['date_consommation'] ?? date('Y-m-d'));

        if (!$alimentId) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT calories, type, unite FROM aliments WHERE id = ?");
        $stmt->execute([(int) $alimentId]);
        $aliment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$aliment) {
            return false;
        }

        $typesAutorises = ['proteine', 'glucide', 'lipide'];

        if (!in_array($type, $typesAutorises, true)) {
            $type = $aliment['type'] ?? null;
        }

        if (!in_array($type, $typesAutorises, true)) {
            return false;
        }

        $baseCalories = (float) $aliment['calories'];
        $unite = $aliment['unite'] ?? 'g';
        $quantite = (float) $quantite;
        $dateObject = DateTime::createFromFormat('Y-m-d', $date);
        $dateErrors = DateTime::getLastErrors();
        $hasDateErrors = is_array($dateErrors)
            && (($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0);

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $date ||
            $hasDateErrors ||
            $date > date('Y-m-d')
        ) {
            return false;
        }

        $calories_calculees = $unite === 'piece'
            ? $baseCalories * $quantite
            : ($baseCalories * $quantite) / 100;

        $stmt = $this->pdo->prepare(
            "INSERT INTO repas_consomme (aliment_id, quantite, calories_calculees, type, date_consommation)
             VALUES (?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            (int) $alimentId,
            $quantite,
            $calories_calculees,
            $type,
            $date,
        ]);
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

    public function getHistory()
    {
        $stmt = $this->pdo->query(
            "SELECT date_consommation, SUM(calories_calculees) as total
             FROM repas_consomme
             GROUP BY date_consommation
             ORDER BY date_consommation DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLast30Days()
    {
        $stmt = $this->pdo->query("
            SELECT date_consommation, SUM(calories_calculees) as total
            FROM repas_consomme
            WHERE date_consommation >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY date_consommation
            ORDER BY date_consommation ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWeeklyStats()
    {
        $stmt = $this->pdo->query("
            SELECT date_consommation, SUM(calories_calculees) as total
            FROM repas_consomme
            WHERE date_consommation >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY date_consommation
        ");
        $days = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $avg = 0;

        if (count($days) > 0) {
            $sum = array_sum(array_column($days, 'total'));
            $avg = $sum / count($days);
        }

        $objStmt = $this->pdo->query("SELECT calories_cible FROM objectif ORDER BY id DESC LIMIT 1");
        $objectif = $objStmt->fetch(PDO::FETCH_ASSOC)['calories_cible'] ?? 2000;

        $success = 0;

        foreach ($days as $day) {
            if ($day['total'] <= $objectif) {
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
        $stmt = $this->pdo->prepare("
            SELECT
                r.id,
                r.aliment_id,
                r.quantite,
                r.calories_calculees,
                COALESCE(r.type, a.type) AS type,
                COALESCE(a.unite, 'g') AS unite,
                r.date_consommation,
                a.nom
            FROM repas_consomme r
            JOIN aliments a ON r.aliment_id = a.id
            WHERE date_consommation = ?
        ");
        $stmt->execute([$date]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, a.nom, a.calories, COALESCE(a.unite, 'g') AS unite
            FROM repas_consomme r
            JOIN aliments a ON r.aliment_id = a.id
            WHERE r.id = ?
        ");
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHistoryByDate($date)
    {
        $stmt = $this->pdo->prepare("
            SELECT date_consommation, SUM(calories_calculees) as total
            FROM repas_consomme
            WHERE date_consommation = ?
            GROUP BY date_consommation
        ");
        $stmt->execute([$date]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
}
