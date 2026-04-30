<?php

class Objectif {

    private $pdo;
    private $lastError;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function save($data) {
        $this->lastError = null;
        $dateCreation = $this->normalizeDateValue($data['date_creation'] ?? date('Y-m-d'));

        if ($dateCreation === null) {
            $this->lastError = "Date d'objectif invalide.";
            return false;
        }

        $stmt = $this->prepareInsertStatement();
        $isSaved = $stmt->execute($this->buildInsertParameters($data, $dateCreation));

        if (!$isSaved) {
            $this->lastError = "Impossible d'enregistrer l'objectif.";
            return false;
        }

        return (int) $this->pdo->lastInsertId();
    }

    public function createSevenDayPlan(array $planRows) {
        $this->lastError = null;

        if (!$this->validateSevenDayPlanRows($planRows)) {
            return false;
        }

        $activePlan = $this->getActivePlanStatus();

        if (!empty($activePlan['is_locked'])) {
            $this->lastError = $this->buildActivePlanMessage($activePlan);
            return false;
        }
        $planDates = $this->extractPlanDates($planRows);
        $startDate = $planDates[0];
        $endDate = $planDates[6];

        try {
            $this->pdo->beginTransaction();

            $duplicateDates = $this->getDuplicateDatesBetween($startDate, $endDate);

            if (!empty($duplicateDates)) {
                throw new RuntimeException("Des objectifs dupliques existent deja sur la periode du plan.");
            }

            $existingRows = $this->getExistingRowsByDate($startDate, $endDate);
            $insertStmt = $this->prepareInsertStatement();
            $updateStmt = $this->preparePlanUpdateStatement();
            $todayDate = date('Y-m-d');
            $todayObjectifId = null;

            foreach ($planRows as $planRow) {
                $planDate = $this->normalizeDateValue($planRow['date_creation'] ?? null);

                if ($planDate === null) {
                    throw new RuntimeException("Une date du plan est invalide.");
                }

                if (isset($existingRows[$planDate])) {
                    $objectifId = (int) $existingRows[$planDate]['id'];
                    $isSaved = $updateStmt->execute($this->buildPlanUpdateParameters($planRow, $objectifId, $planDate));
                } else {
                    $isSaved = $insertStmt->execute($this->buildInsertParameters($planRow, $planDate));
                    $objectifId = (int) $this->pdo->lastInsertId();
                }

                if (!$isSaved) {
                    throw new RuntimeException("Impossible d'enregistrer l'objectif du " . $planDate . ".");
                }

                if ($planDate === $todayDate) {
                    $todayObjectifId = $objectifId;
                }
            }

            if ($todayObjectifId !== null && !$this->assignMealsToObjectifForDate($todayObjectifId, $todayDate)) {
                throw new RuntimeException($this->lastError ?: "Impossible de rattacher les repas du jour au nouveau plan.");
            }

            $this->pdo->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log('[ObjectifPlan] ' . $exception->getMessage());
            $this->lastError = $this->lastError ?: "Impossible de creer le plan nutritionnel sur 7 jours.";

            return false;
        }
    }

    public function replaceLatestPlan(array $planRows) {
        $this->lastError = null;

        if (!$this->validateSevenDayPlanRows($planRows)) {
            return false;
        }

        $activePlan = $this->getActivePlanStatus();

        if (empty($activePlan['can_modify_today'])) {
            $this->lastError = "Le plan ne peut etre modifie que le jour 1.";
            return false;
        }

        $existingPlanRows = $this->getLatestPlanRows();

        if (empty($existingPlanRows)) {
            $this->lastError = "Aucun plan actif n'est disponible pour une modification.";
            return false;
        }

        $todayDate = date('Y-m-d');

        try {
            $this->pdo->beginTransaction();

            foreach ($existingPlanRows as $existingPlanRow) {
                $existingDate = $this->normalizeDateValue($existingPlanRow['date_creation'] ?? null);
                $linkedMeals = $this->countLinkedMeals((int) ($existingPlanRow['id'] ?? 0));

                if ($existingDate !== $todayDate && $linkedMeals > 0) {
                    throw new RuntimeException("Impossible de remplacer le plan car des repas sont deja lies a un autre jour.");
                }
            }

            $insertStmt = $this->prepareInsertStatement();
            $newTodayObjectifId = null;

            foreach ($planRows as $planRow) {
                $planDate = $this->normalizeDateValue($planRow['date_creation'] ?? null);

                if ($planDate === null) {
                    throw new RuntimeException("Une date du plan est invalide.");
                }

                $isSaved = $insertStmt->execute($this->buildInsertParameters($planRow, $planDate));

                if (!$isSaved) {
                    throw new RuntimeException("Impossible d'enregistrer le nouveau plan.");
                }

                $newObjectifId = (int) $this->pdo->lastInsertId();

                if ($planDate === $todayDate) {
                    $newTodayObjectifId = $newObjectifId;
                }
            }

            if ($newTodayObjectifId !== null && !$this->assignMealsToObjectifForDate($newTodayObjectifId, $todayDate)) {
                throw new RuntimeException($this->lastError ?: "Impossible de rattacher les repas du jour au nouveau plan.");
            }

            if (!$this->deleteRowsByIds(array_column($existingPlanRows, 'id'))) {
                throw new RuntimeException($this->lastError ?: "Impossible de supprimer l'ancien plan.");
            }

            $this->pdo->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log('[ObjectifPlanReplace] ' . $exception->getMessage());
            $this->lastError = $this->lastError ?: "Impossible de modifier le plan nutritionnel sur 7 jours.";

            return false;
        }
    }

    public function getLatest() {
        return $this->getObjectifDuJour();
    }

    public function getLatestPlanRows() {
        $stmt = $this->pdo->query("
            SELECT *
            FROM objectif
            WHERE date_creation >= (
                SELECT DATE_SUB(MAX(date_creation), INTERVAL 6 DAY)
                FROM objectif
            )
            ORDER BY date_creation ASC, id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getObjectifsParJour() {
        $stmt = $this->pdo->prepare("
            SELECT
                DATE(date_creation) AS jour,
                calories_cible
            FROM objectif
            WHERE date_creation >= (
                SELECT DATE_SUB(MAX(date_creation), INTERVAL 6 DAY)
                FROM objectif
            )
            ORDER BY date_creation ASC, id ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getObjectifDuJour() {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM objectif
            WHERE DATE(date_creation) = CURDATE()
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLatestForToday() {
        return $this->getObjectifDuJour();
    }

    public function getByDate($date) {
        $normalizedDate = $this->normalizeDateValue($date);

        if ($normalizedDate === null) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM objectif
            WHERE DATE(date_creation) = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$normalizedDate]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActivePlanStatus() {
        $planRows = $this->getLatestPlanRows();

        if (empty($planRows)) {
            return null;
        }

        $normalizedPlanDates = [];

        foreach ($planRows as $planRow) {
            $planDate = $this->normalizeDateValue($planRow['date_creation'] ?? null);

            if ($planDate !== null && !in_array($planDate, $normalizedPlanDates, true)) {
                $normalizedPlanDates[] = $planDate;
            }
        }

        if (empty($normalizedPlanDates)) {
            return null;
        }

        $today = new DateTimeImmutable(date('Y-m-d'));
        $startDate = new DateTimeImmutable($normalizedPlanDates[0]);
        $endDate = new DateTimeImmutable($normalizedPlanDates[count($normalizedPlanDates) - 1]);
        $daysSinceStart = (int) $startDate->diff($today)->format('%r%a');
        $isLocked = $daysSinceStart >= 0 && $daysSinceStart < 7;
        $isActive = $isLocked && $today <= $endDate;
        $remainingDays = $isLocked ? max(0, 7 - $daysSinceStart) : 0;
        $startDateValue = $startDate->format('Y-m-d');
        $todayValue = $today->format('Y-m-d');

        return [
            'start_date' => $startDateValue,
            'end_date' => $endDate->format('Y-m-d'),
            'total_days' => count($normalizedPlanDates),
            'row_count' => count($planRows),
            'days_since_start' => $daysSinceStart,
            'remaining_days' => $remainingDays,
            'is_active' => $isActive,
            'is_locked' => $isLocked,
            'can_modify_today' => $isLocked && $startDateValue === $todayValue,
            'can_create_on' => $startDate->modify('+7 days')->format('Y-m-d'),
        ];
    }

    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT
                o.*,
                COALESCE(rc.repas_count, 0) AS repas_count
            FROM objectif o
            LEFT JOIN (
                SELECT objectif_id, COUNT(*) AS repas_count
                FROM repas_consomme
                GROUP BY objectif_id
            ) rc ON rc.objectif_id = o.id
            ORDER BY o.date_creation DESC, o.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM objectif WHERE id = ?");
        $stmt->execute([(int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data) {
        $this->lastError = null;
        $stmt = $this->pdo->prepare("
            UPDATE objectif
            SET calories_cible = ?, objectif_type = ?, poids = ?, taille = ?, age = ?, sexe = ?, activite = ?, proteines = ?, lipides = ?, glucides = ?
            WHERE id = ?
        ");

        $isUpdated = $stmt->execute([
            (int) $data['calories_cible'],
            $data['objectif_type'] ?? 'maintien',
            (float) $data['poids'],
            (float) $data['taille'],
            (int) $data['age'],
            $data['sexe'] ?? 'homme',
            $data['activite'] ?? null,
            (float) $data['proteines'],
            (float) $data['lipides'],
            (float) $data['glucides'],
            (int) $data['id']
        ]);

        if (!$isUpdated) {
            $this->lastError = "Impossible de mettre a jour l'objectif.";
        }

        return $isUpdated;
    }

    public function delete($id) {
        $this->lastError = null;
        $objectif = $this->getById($id);

        if (!$objectif) {
            $this->lastError = "Objectif introuvable.";
            return false;
        }

        $objectifDate = $this->normalizeDateValue($objectif['date_creation'] ?? null);
        $activePlan = $this->getActivePlanStatus();

        if (
            $objectifDate !== null &&
            !empty($activePlan['is_active']) &&
            $objectifDate >= ($activePlan['start_date'] ?? '') &&
            $objectifDate <= ($activePlan['end_date'] ?? '')
        ) {
            $this->lastError = "Impossible de supprimer un objectif appartenant au plan actif.";
            return false;
        }

        $repasCount = $this->countLinkedMeals($id);

        if ($repasCount > 0) {
            $this->lastError = "Impossible de supprimer un objectif deja lie a des repas.";
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM objectif WHERE id = ?");
        $isDeleted = $stmt->execute([(int) $id]);

        if (!$isDeleted) {
            $this->lastError = "Impossible de supprimer l'objectif.";
        }

        return $isDeleted;
    }

    public function assignTodayMealsToObjectif($objectifId) {
        return $this->assignMealsToObjectifForDate($objectifId, date('Y-m-d'));
    }

    public function assignMealsToObjectifForDate($objectifId, $date) {
        $this->lastError = null;
        $normalizedDate = $this->normalizeDateValue($date);

        if ($normalizedDate === null) {
            $this->lastError = "Date de rattachement des repas invalide.";
            return false;
        }

        $stmt = $this->pdo->prepare("
            UPDATE repas_consomme
            SET objectif_id = ?
            WHERE date_consommation = ?
        ");

        $isAssigned = $stmt->execute([
            (int) $objectifId,
            $normalizedDate,
        ]);

        if (!$isAssigned) {
            $this->lastError = "Impossible de rattacher les repas a l'objectif du jour.";
        }

        return $isAssigned;
    }

    public function countLinkedMeals($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM repas_consomme WHERE objectif_id = ?");
        $stmt->execute([(int) $id]);

        return (int) $stmt->fetchColumn();
    }

    private function prepareInsertStatement() {
        return $this->pdo->prepare("
            INSERT INTO objectif (
                calories_cible,
                objectif_type,
                poids,
                taille,
                age,
                sexe,
                activite,
                proteines,
                lipides,
                glucides,
                date_creation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
    }

    private function preparePlanUpdateStatement() {
        return $this->pdo->prepare("
            UPDATE objectif
            SET calories_cible = ?,
                objectif_type = ?,
                poids = ?,
                taille = ?,
                age = ?,
                sexe = ?,
                activite = ?,
                proteines = ?,
                lipides = ?,
                glucides = ?,
                date_creation = ?
            WHERE id = ?
        ");
    }

    private function buildInsertParameters(array $data, $dateCreation) {
        return [
            (int) $data['calories_cible'],
            $data['objectif_type'] ?? 'maintien',
            (float) $data['poids'],
            (float) $data['taille'],
            (int) $data['age'],
            $data['sexe'] ?? 'homme',
            $data['activite'] ?? null,
            (float) $data['proteines'],
            (float) $data['lipides'],
            (float) $data['glucides'],
            $dateCreation,
        ];
    }

    private function buildPlanUpdateParameters(array $data, $id, $dateCreation) {
        return [
            (int) $data['calories_cible'],
            $data['objectif_type'] ?? 'maintien',
            (float) $data['poids'],
            (float) $data['taille'],
            (int) $data['age'],
            $data['sexe'] ?? 'homme',
            $data['activite'] ?? null,
            (float) $data['proteines'],
            (float) $data['lipides'],
            (float) $data['glucides'],
            $dateCreation,
            (int) $id,
        ];
    }

    private function getExistingRowsByDate($startDate, $endDate) {
        $stmt = $this->pdo->prepare("
            SELECT id, DATE(date_creation) AS plan_date
            FROM objectif
            WHERE DATE(date_creation) BETWEEN ? AND ?
            ORDER BY date_creation DESC, id DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rowsByDate = [];

        foreach ($rows as $row) {
            $planDate = $row['plan_date'] ?? null;

            if ($planDate !== null && !isset($rowsByDate[$planDate])) {
                $rowsByDate[$planDate] = $row;
            }
        }

        return $rowsByDate;
    }

    private function getDuplicateDatesBetween($startDate, $endDate) {
        $stmt = $this->pdo->prepare("
            SELECT DATE(date_creation) AS plan_date
            FROM objectif
            WHERE DATE(date_creation) BETWEEN ? AND ?
            GROUP BY DATE(date_creation)
            HAVING COUNT(*) > 1
        ");
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function deleteRowsByIds(array $ids) {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return true;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM objectif WHERE id IN ($placeholders)");
        $isDeleted = $stmt->execute($ids);

        if (!$isDeleted) {
            $this->lastError = "Impossible de supprimer les lignes de l'ancien plan.";
        }

        return $isDeleted;
    }

    private function validateSevenDayPlanRows(array $planRows) {
        if (count($planRows) !== 7) {
            $this->lastError = "Le plan doit contenir exactement 7 jours.";
            return false;
        }

        $normalizedDates = $this->extractPlanDates($planRows);

        if (in_array(null, $normalizedDates, true)) {
            $this->lastError = "Une date du plan est invalide.";
            return false;
        }

        foreach ($normalizedDates as $index => $normalizedDate) {
            $expectedDate = (new DateTimeImmutable(date('Y-m-d')))
                ->modify('+' . $index . ' day')
                ->format('Y-m-d');

            if ($normalizedDate !== $expectedDate) {
                $this->lastError = "Le plan doit couvrir les 7 prochains jours consecutifs.";
                return false;
            }
        }

        if (count(array_unique($normalizedDates)) !== 7) {
            $this->lastError = "Le plan contient des dates dupliquees.";
            return false;
        }

        return true;
    }

    private function extractPlanDates(array $planRows) {
        $normalizedDates = [];

        foreach ($planRows as $planRow) {
            $normalizedDates[] = $this->normalizeDateValue($planRow['date_creation'] ?? null);
        }

        return $normalizedDates;
    }

    private function normalizeDateValue($date) {
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

    private function buildActivePlanMessage(array $activePlan) {
        $remainingDays = (int) ($activePlan['remaining_days'] ?? 0);
        $dayLabel = $remainingDays > 1 ? 'jours' : 'jour';

        return "Vous avez deja un plan actif. Vous pourrez le modifier dans {$remainingDays} {$dayLabel}.";
    }
}
