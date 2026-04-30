<?php

require_once __DIR__ . '/../models/objectif.php';
require_once __DIR__ . '/../models/suivi.php';
require_once __DIR__ . '/../services/ObjectifCalculatorService.php';

class objectifctrl
{
    private $objectifModel;
    private $suiviModel;
    private $objectifCalculator;

    public function __construct($pdo)
    {
        $this->objectifModel = new Objectif($pdo);
        $this->suiviModel = new Suivi($pdo);
        $this->objectifCalculator = new ObjectifCalculatorService();
    }

    public function index()
    {
        $activePlan = $this->objectifModel->getActivePlanStatus();
        $planRows = $this->objectifModel->getLatestPlanRows();
        $planStartDate = $activePlan['start_date'] ?? (!empty($planRows[0]['date_creation']) ? (string) $planRows[0]['date_creation'] : null);
        $todayObjectif = $this->prepareStoredObjectif($this->objectifModel->getObjectifDuJour(), $planStartDate);
        $objectif = !empty($todayObjectif) ? $todayObjectif : null;
        $objectifMessage = empty($objectif) ? "Aucun objectif defini pour aujourd'hui." : null;
        $planStartObjectif = !empty($planRows[0]) ? $this->prepareStoredObjectif($planRows[0], $planStartDate) : null;
        $canModifyPlanToday = !empty($activePlan['can_modify_today']) && !empty($planStartObjectif);
        $objectifDebug = $_SESSION['objectif_debug'] ?? null;

        $total_today = $this->suiviModel->getTodayTotal();
        $todayMacros = $this->suiviModel->getTodayMacros();
        $objectifSummary = $this->hasPhysicalProfile($objectif)
            ? $this->objectifCalculator->calculateNutritionTargets($objectif)
            : [];
        $sexeOptions = $this->objectifCalculator->getSexeOptions();
        $activiteDisplayLabel = !empty($objectif)
            ? $this->objectifCalculator->getActiviteLabel($objectif)
            : '-';
        $activiteInputOptions = $this->objectifCalculator->getActiviteSelectOptions();
        $objectifTypeOptions = $this->objectifCalculator->getObjectifTypeOptions();
        unset($_SESSION['objectif_debug']);

        require __DIR__ . '/../views/front/objectif/index.php';
    }

    public function store()
    {
        $validation = $this->validateObjectifInput($_POST);

        if (!empty($validation['errors'])) {
            $_SESSION['objectif_error'] = $validation['errors'];
            $_SESSION['objectif_form'] = $this->buildFormState($_POST);
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $activePlan = $this->objectifModel->getActivePlanStatus();

        if (!empty($activePlan['is_locked'])) {
            $_SESSION['objectif_error'] = [$this->buildActivePlanMessage($activePlan)];
            $_SESSION['objectif_form'] = $this->buildFormState($_POST);
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $calculationInput = $validation['data'];
        $calculationInput['debug_log'] = true;
        $baseData = $this->objectifCalculator->buildObjectifData($calculationInput);
        $debugMetrics = $baseData['calculation_debug'] ?? null;
        unset($baseData['calculation_debug']);
        $planRows = $this->buildSevenDayPlanRows($baseData);

        if ($debugMetrics !== null && !empty($planRows[0]['calories_cible'])) {
            $debugMetrics['calories_cible'] = (int) $planRows[0]['calories_cible'];
        }

        unset($_SESSION['objectif_form']);

        $isCreated = $this->objectifModel->createSevenDayPlan($planRows);

        if (!$isCreated) {
            $_SESSION['objectif_error'] = [
                $this->objectifModel->getLastError() ?: "Impossible de creer le plan nutritionnel sur 7 jours pour le moment."
            ];
        } else {
            $_SESSION['objectif_debug'] = $debugMetrics;
            $_SESSION['objectif_success'] = "Plan nutritionnel sur 7 jours genere avec succes";
        }

        header("Location: index.php?controller=objectif&action=index");
        exit;
    }

    public function edit()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $activePlan = $this->objectifModel->getActivePlanStatus();
        $planStartObjectif = $this->getPlanStartObjectif();

        if (
            empty($activePlan['can_modify_today']) ||
            empty($planStartObjectif) ||
            ($id > 0 && (int) $planStartObjectif['id'] !== $id)
        ) {
            $_SESSION['objectif_error'] = [$this->buildPlanModificationErrorMessage($activePlan)];
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $objectif = $planStartObjectif;

        if (!$objectif) {
            $_SESSION['objectif_error'] = ["Objectif introuvable."];
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $storedForm = $_SESSION['objectif_form'] ?? [];
        unset($_SESSION['objectif_form']);

        if (!empty($storedForm) && (int) ($storedForm['id'] ?? 0) === (int) $objectif['id']) {
            $objectif = array_merge($objectif, $storedForm);
        }

        $objectif = $this->prepareStoredObjectif($objectif, $objectif['date_creation'] ?? null);

        $objectifSummary = $this->hasPhysicalProfile($objectif)
            ? $this->objectifCalculator->calculateNutritionTargets($objectif)
            : [];
        $sexeOptions = $this->objectifCalculator->getSexeOptions();
        $activiteInputOptions = $this->objectifCalculator->getActiviteSelectOptions();
        $objectifTypeOptions = $this->objectifCalculator->getObjectifTypeOptions();

        require __DIR__ . '/../views/front/objectif/edit.php';
    }

    public function update()
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $activePlan = $this->objectifModel->getActivePlanStatus();

        if (
            $id <= 0 ||
            empty($activePlan['can_modify_today']) ||
            !$this->isPlanStartObjectifId($id)
        ) {
            $_SESSION['objectif_error'] = [$this->buildPlanModificationErrorMessage($activePlan)];
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $validation = $this->validateObjectifInput($_POST);

        if (!empty($validation['errors'])) {
            $_SESSION['objectif_error'] = !empty($validation['errors']) ? $validation['errors'] : ["Objectif invalide."];
            $_SESSION['objectif_form'] = $this->buildFormState($_POST, $id);
            header("Location: index.php?controller=objectif&action=edit&id=" . urlencode((string) $id));
            exit;
        }

        $objectif = $this->objectifModel->getById($id);

        if (!$objectif) {
            $_SESSION['objectif_error'] = ["Objectif introuvable."];
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $calculationInput = $validation['data'];
        $calculationInput['debug_log'] = true;
        $data = $this->objectifCalculator->buildObjectifData($calculationInput);
        $debugMetrics = $data['calculation_debug'] ?? null;
        unset($data['calculation_debug']);
        $planRows = $this->buildSevenDayPlanRows($data);

        if ($debugMetrics !== null && !empty($planRows[0]['calories_cible'])) {
            $debugMetrics['calories_cible'] = (int) $planRows[0]['calories_cible'];
        }

        $isUpdated = $this->objectifModel->replaceLatestPlan($planRows);

        if ($isUpdated) {
            unset($_SESSION['objectif_form']);
            $_SESSION['objectif_debug'] = $debugMetrics;
            $_SESSION['objectif_success'] = "Plan nutritionnel sur 7 jours mis a jour avec succes";
        } else {
            $_SESSION['objectif_error'] = [
                $this->objectifModel->getLastError() ?: "Impossible de mettre a jour le plan nutritionnel."
            ];
            $_SESSION['objectif_form'] = $this->buildFormState($_POST, $id);
            header("Location: index.php?controller=objectif&action=edit&id=" . urlencode((string) $id));
            exit;
        }

        header("Location: index.php?controller=objectif&action=index");
        exit;
    }

    public function delete()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $isDeleted = $this->objectifModel->delete($id);

            if ($isDeleted) {
                $_SESSION['objectif_success'] = "Objectif supprime avec succes";
            } else {
                $_SESSION['objectif_error'] = [
                    $this->objectifModel->getLastError() ?: "Impossible de supprimer l'objectif."
                ];
            }
        }

        header("Location: index.php?controller=objectif&action=index");
        exit;
    }

    private function validateObjectifInput($data)
    {
        $errors = [];
        $poidsInput = trim((string) ($data['poids'] ?? ''));
        $tailleInput = trim((string) ($data['taille'] ?? ''));
        $ageInput = trim((string) ($data['age'] ?? ''));
        $sexe = $data['sexe'] ?? 'homme';
        $activite = $data['activite'] ?? 'moderate';
        $objectifType = $data['objectif_type'] ?? 'maintien';
        $isPoidsValid = $poidsInput !== '' && is_numeric($poidsInput);
        $isTailleValid = $tailleInput !== '' && is_numeric($tailleInput);
        $isAgeValid = $ageInput !== '' && filter_var($ageInput, FILTER_VALIDATE_INT) !== false;

        if (!$isPoidsValid) {
            $errors[] = "Le poids est obligatoire et doit etre numerique.";
        }

        if (!$isTailleValid) {
            $errors[] = "La taille est obligatoire et doit etre numerique.";
        }

        if (!$isAgeValid) {
            $errors[] = "L'age est obligatoire et doit etre un entier.";
        }

        $poids = (float) $poidsInput;
        $taille = (float) $tailleInput;
        $age = (int) $ageInput;

        if ($isPoidsValid && ($poids <= 0 || $poids > 500)) {
            $errors[] = "Le poids doit etre compris entre 1 et 500 kg.";
        }

        if ($isTailleValid && ($taille <= 0 || $taille > 300)) {
            $errors[] = "La taille doit etre comprise entre 1 et 300 cm.";
        }

        if ($isAgeValid && ($age <= 0 || $age > 120)) {
            $errors[] = "L'age doit etre compris entre 1 et 120 ans.";
        }

        if (!array_key_exists($sexe, $this->objectifCalculator->getSexeOptions())) {
            $errors[] = "Le sexe selectionne est invalide.";
        }

        if (!array_key_exists($activite, $this->objectifCalculator->getActiviteSelectOptions())) {
            $errors[] = "Le niveau d'activite selectionne est invalide.";
        }

        if (!array_key_exists($objectifType, $this->objectifCalculator->getObjectifTypeOptions())) {
            $errors[] = "Le type d'objectif selectionne est invalide.";
        }

        return [
            'errors' => $errors,
            'data' => [
                'poids' => $poids,
                'taille' => $taille,
                'age' => $age,
                'sexe' => $sexe,
                'activite' => $activite,
                'objectif_type' => $objectifType,
            ],
        ];
    }

    private function buildFormState($data, $id = null)
    {
        $formState = [
            'poids' => trim((string) ($data['poids'] ?? '')),
            'taille' => trim((string) ($data['taille'] ?? '')),
            'age' => trim((string) ($data['age'] ?? '')),
            'sexe' => $data['sexe'] ?? 'homme',
            'activite' => $data['activite'] ?? 'moderate',
            'activite_input' => $data['activite'] ?? 'moderate',
            'objectif_type' => $data['objectif_type'] ?? 'maintien',
        ];

        if ($id !== null && $id > 0) {
            $formState['id'] = (int) $id;
        }

        return $formState;
    }

    private function buildSevenDayPlanRows(array $baseData)
    {
        $planRows = [];
        $today = new DateTimeImmutable(date('Y-m-d'));

        for ($index = 0; $index < 7; $index++) {
            $planDate = $today->modify('+' . $index . ' day')->format('Y-m-d');
            $dayCalories = $this->resolvePlanDayCalories((int) ($baseData['calories_cible'] ?? 0), $index);
            $dayMacros = $this->objectifCalculator->calculateMacroTargetsForCalories(
                $dayCalories,
                $baseData['objectif_type'] ?? 'maintien'
            );

            $planRows[] = array_merge($baseData, $dayMacros, [
                'calories_cible' => $dayCalories,
                'date_creation' => $planDate,
            ]);
        }

        return $planRows;
    }

    private function resolvePlanDayCalories($baseCalories, $index)
    {
        $baseCalories = max(0, (int) $baseCalories);

        if ($index === 5) {
            return $baseCalories + 300;
        }

        if ($index === 6) {
            return $baseCalories + 200;
        }

        return max(0, $baseCalories - 100);
    }

    private function buildActivePlanMessage(array $activePlan)
    {
        $remainingDays = (int) ($activePlan['remaining_days'] ?? 0);
        $dayLabel = $remainingDays > 1 ? 'jours' : 'jour';

        return "Vous avez deja un plan actif. Vous pourrez le modifier dans {$remainingDays} {$dayLabel}.";
    }

    private function prepareStoredObjectif($objectif, $planStartDate = null)
    {
        if (empty($objectif) || !is_array($objectif)) {
            return $objectif;
        }

        $objectif['allow_calorie_activity_inference'] = true;
        $objectif['plan_day_index'] = $this->resolvePlanDayIndex(
            $objectif['date_creation'] ?? null,
            $planStartDate
        );
        $objectif['activite_input'] = $this->objectifCalculator->getActiviteSelectValue($objectif);

        return $objectif;
    }

    private function getPlanStartObjectif()
    {
        $planRows = $this->objectifModel->getLatestPlanRows();

        if (empty($planRows[0]) || !is_array($planRows[0])) {
            return null;
        }

        $planStartDate = $planRows[0]['date_creation'] ?? null;

        return $this->prepareStoredObjectif($planRows[0], $planStartDate);
    }

    private function isPlanStartObjectifId($id)
    {
        $planStartObjectif = $this->getPlanStartObjectif();

        if (empty($planStartObjectif['id'])) {
            return false;
        }

        return (int) $planStartObjectif['id'] === (int) $id;
    }

    private function buildPlanModificationErrorMessage($activePlan)
    {
        if (!empty($activePlan['is_locked']) && empty($activePlan['can_modify_today'])) {
            return "Le plan 7 jours ne peut etre modifie que le premier jour.";
        }

        return "Aucun plan modifiable n'est disponible pour le moment.";
    }

    private function hasPhysicalProfile($objectif)
    {
        if (empty($objectif) || !is_array($objectif)) {
            return false;
        }

        return isset(
            $objectif['poids'],
            $objectif['taille'],
            $objectif['age'],
            $objectif['sexe'],
            $objectif['activite'],
            $objectif['objectif_type']
        ) && (float) $objectif['poids'] > 0
            && (float) $objectif['taille'] > 0
            && (int) $objectif['age'] > 0;
    }

    private function resolvePlanDayIndex($objectifDate, $planStartDate)
    {
        $objectifDate = $this->normalizeDateValue($objectifDate);
        $planStartDate = $this->normalizeDateValue($planStartDate);

        if ($objectifDate === null || $planStartDate === null) {
            return null;
        }

        try {
            $objectifDateObject = new DateTimeImmutable($objectifDate);
            $planStartDateObject = new DateTimeImmutable($planStartDate);
        } catch (Exception $exception) {
            return null;
        }

        return (int) $planStartDateObject->diff($objectifDateObject)->format('%r%a');
    }

    private function normalizeDateValue($date)
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
