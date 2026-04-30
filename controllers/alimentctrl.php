<?php

require_once __DIR__ . '/../models/aliment.php';
require_once __DIR__ . '/../models/suivi.php';
require_once __DIR__ . '/../helpers/report_mail.php';

class alimentctrl
{

    private $alimentModel;
    private $suiviModel;
    private $allowedTypes = ['proteine', 'glucide', 'lipide'];
    private $allowedUnits = ['g', 'piece'];

    public function __construct($pdo)
    {
        $this->alimentModel = new Aliment($pdo);
        $this->suiviModel = new Suivi($pdo);
    }

    public function index()
    {
        $aliments = $this->alimentModel->getAll();

        $date = !empty($_GET['date']) ? trim($_GET['date']) : null;
        $mode = $_GET['mode'] ?? null;
        $isValidDate = $this->isValidTrackingDate($date);
        $selectedDate = $isValidDate ? $date : date('Y-m-d');
        $isDetailMode = ($mode === 'detail' && $isValidDate);
        $isAddMode = ($mode === 'add' && $isValidDate);
        $isMainTrackingPage = !$isDetailMode && !$isAddMode && !$isValidDate;
        $historyFilters = $this->resolveHistoryFilters($_GET);
        $history = [];
        $details = [];
        $isEmptyDetailMode = false;
        $emptyDetailRow = null;
        $detailDate = $selectedDate;

        if ($isDetailMode) {
            $details = $this->suiviModel->getByDate($selectedDate);

            if (empty($details)) {
                $detailHistory = $this->suiviModel->getHistory([
                    'period' => 'custom',
                    'status' => '',
                    'start_date' => $selectedDate,
                    'end_date' => $selectedDate,
                ]);

                if (!empty($detailHistory)) {
                    $isEmptyDetailMode = true;
                    $emptyDetailRow = $detailHistory[0];
                } else {
                    $history = $this->suiviModel->getHistory($historyFilters);
                }
            }
        } elseif ($isAddMode) {
            $history = [];
        } else {
            $history = $this->suiviModel->getHistory($historyFilters);
        }

        $mealItems = $this->getMealItemsFromSession();
        $mealDate = $this->getMealDateFromSession();

        if (!empty($mealItems) && $mealDate === null) {
            $this->clearMealSession();
            $mealItems = [];
        }

        $mealDate = $this->getMealDateFromSession();
        $composerDate = $mealDate ?? ($isAddMode ? $selectedDate : date('Y-m-d'));
        $trackingDate = $selectedDate;
        $total = $isAddMode
            ? $this->suiviModel->getTotalByDate($selectedDate)
            : $this->suiviModel->getTodayTotal();
        $showTrackedDate = $isAddMode;
        $mealTotal = $this->calculateMealTotal($mealItems);
        $hasMealDateConflict = $isAddMode && !empty($mealItems) && $mealDate !== null && $mealDate !== $selectedDate;
        $showMealSection = $isMainTrackingPage && !empty($mealItems);

        require __DIR__ . '/../views/front/aliments/index.php';
    }

    public function search()
    {
        $this->searchAliment();
    }

    public function searchAliment()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $query = trim((string) ($_GET['query'] ?? ''));
        $type = trim((string) ($_GET['type'] ?? ''));

        if ($query === '' || !in_array($type, $this->allowedTypes, true)) {
            echo json_encode([]);
            exit;
        }

        echo json_encode($this->alimentModel->searchByTypeAndName($query, $type, 5));
        exit;
    }

    public function store()
    {
        $redirectDate = trim($_POST['date_consommation'] ?? date('Y-m-d'));
        $origin = $this->resolveOrigin($_POST['origin'] ?? 'main');
        $_POST['date_consommation'] = $redirectDate;
        $errors = $this->validateConsommationInput($_POST);

        if (!empty($errors)) {
            $_SESSION['aliment_error'] = $errors;
            $this->redirectToTrackingPage($redirectDate, $origin, true);
        }

        $mealItems = $this->getMealItemsFromSession();
        $mealDate = $this->getMealDateFromSession();

        if (!empty($mealItems) && $mealDate !== null && $mealDate !== $redirectDate) {
            $_SESSION['aliment_error'] = [
                "Un repas est deja en cours pour le {$mealDate}. Validez-le ou annulez-le avant de changer de date."
            ];
            $this->redirectToTrackingPage($mealDate, 'main');
        }

        $mealItem = $this->suiviModel->prepareMealItem($_POST);

        if ($mealItem === false) {
            $_SESSION['aliment_error'] = [
                $this->suiviModel->getLastError() ?: "Impossible d'ajouter cet aliment au repas."
            ];
            $this->redirectToTrackingPage($redirectDate, $origin, true);
        }

        $mealItems[] = $mealItem;

        $_SESSION['repas'] = array_values($mealItems);
        $_SESSION['repas_date'] = $redirectDate;
        $_SESSION['aliment_success'] = "Aliment ajoute au repas en cours.";

        $this->redirectToTrackingPage($redirectDate, 'main');
    }

    public function validateMeal()
    {
        $mealItems = $this->getMealItemsFromSession();
        $mealDate = $this->getMealDateFromSession();

        if (empty($mealItems) || $mealDate === null) {
            $_SESSION['aliment_error'] = ["Aucun repas en attente a valider."];
            header("Location: index.php?controller=suivi&action=index");
            exit;
        }

        if (!$this->suiviModel->validerRepas($mealItems, $mealDate)) {
            $_SESSION['aliment_error'] = [
                $this->suiviModel->getLastError() ?: "Impossible d'enregistrer ce repas pour le moment."
            ];
            $this->redirectToTrackingPage($mealDate, 'main');
        }

        $this->clearMealSession();
        $_SESSION['aliment_success'] = "Repas enregistre avec succes.";

        header("Location: index.php?controller=suivi&action=index&mode=detail&date=" . urlencode($mealDate));
        exit;
    }

    public function cancelMeal()
    {
        $mealDate = $this->getMealDateFromSession();

        if (!empty($this->getMealItemsFromSession())) {
            $this->clearMealSession();
            $_SESSION['aliment_success'] = "Le repas en cours a ete annule.";
        }

        $this->redirectToTrackingPage($mealDate, 'main');
    }

    public function removeMealItem()
    {
        $itemIndex = $_POST['item_index'] ?? null;
        $mealItems = $this->getMealItemsFromSession();
        $mealDate = $this->getMealDateFromSession();

        if (
            filter_var($itemIndex, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false ||
            !isset($mealItems[(int) $itemIndex])
        ) {
            $_SESSION['aliment_error'] = ["L'element du repas est introuvable."];
            $this->redirectToTrackingPage($mealDate, 'main');
        }

        unset($mealItems[(int) $itemIndex]);
        $mealItems = array_values($mealItems);

        if (empty($mealItems)) {
            $this->clearMealSession();
            $_SESSION['aliment_success'] = "Le repas en attente est maintenant vide.";
        } else {
            $_SESSION['repas'] = $mealItems;
            $_SESSION['aliment_success'] = "Aliment retire du repas en cours.";
        }

        $this->redirectToTrackingPage($mealDate, 'main');
    }

    public function createCustom()
    {
        require __DIR__ . '/../views/front/aliments/create_custom.php';
    }

    public function storeCustom()
    {
        $validation = $this->validateCustomAlimentInput($_POST);

        if (!empty($validation['errors'])) {
            $_SESSION['custom_aliment_error'] = $validation['errors'];
            $_SESSION['custom_aliment_form'] = [
                'nom' => trim((string) ($_POST['nom'] ?? '')),
                'calories' => trim((string) ($_POST['calories'] ?? '')),
                'proteines' => trim((string) ($_POST['proteines'] ?? '')),
                'glucides' => trim((string) ($_POST['glucides'] ?? '')),
                'lipides' => trim((string) ($_POST['lipides'] ?? '')),
                'unite' => $_POST['unite'] ?? 'g',
                'type' => $_POST['type'] ?? 'proteine',
            ];
            header("Location: index.php?controller=suivi&action=createCustom");
            exit;
        }

        $this->alimentModel->create($validation['data']);
        unset($_SESSION['custom_aliment_form']);

        header("Location: index.php");
        exit;
    }

    public function delete()
    {
        if (!empty($_GET['id'])) {
            $this->suiviModel->delete($_GET['id']);
        }

        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?controller=suivi&action=index';
        header("Location: " . $redirect);
        exit;
    }

    public function edit()
    {
        if (empty($_GET['id'])) {
            header("Location: index.php?controller=suivi&action=index");
            exit;
        }

        $entry = $this->suiviModel->getById($_GET['id']);

        if (!$entry) {
            header("Location: index.php?controller=suivi&action=index");
            exit;
        }

        require __DIR__ . '/../views/front/aliments/edit.php';
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        $quantite = $_POST['quantite'] ?? null;

        if (
            filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false ||
            $quantite === null ||
            $quantite === '' ||
            !is_numeric($quantite) ||
            (float) $quantite <= 0
        ) {
            $_SESSION['aliment_edit_error'] = ["Quantite invalide."];
            header("Location: index.php?controller=suivi&action=edit&id=" . urlencode((string) $id));
            exit;
        }

        $date = $this->suiviModel->update($_POST);

        if ($date) {
            header("Location: index.php?controller=suivi&action=index&mode=detail&date=" . urlencode($date));
        } else {
            header("Location: index.php?controller=suivi&action=index");
        }

        exit;
    }

    public function sendReport()
    {
        $stats = $this->suiviModel->getWeeklyStats();
        $result = sendWeeklyReport($stats);

        if (!empty($result['success'])) {
            $_SESSION['objectif_success'] = "Email envoye";
        } else {
            $_SESSION['objectif_error'] = $result['error'] ?? "Impossible d'envoyer l'email";
        }

        header("Location: index.php?controller=objectif&action=index");
        exit;
    }

    private function isValidTrackingDate($date)
    {
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $dateObject = DateTime::createFromFormat('Y-m-d', $date);
        $dateErrors = DateTime::getLastErrors();
        $hasDateErrors = is_array($dateErrors)
            && (($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0);

        return $dateObject
            && $dateObject->format('Y-m-d') === $date
            && !$hasDateErrors;
    }

    private function validateConsommationInput($data)
    {
        $errors = [];
        $alimentId = $data['aliment_id'] ?? null;
        $quantite = $data['quantite'] ?? null;
        $type = $data['type'] ?? '';
        $date = trim($data['date_consommation'] ?? '');

        if (filter_var($alimentId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $errors[] = "Veuillez selectionner un aliment valide.";
        }

        if ($quantite === null || $quantite === '' || !is_numeric($quantite) || (float) $quantite <= 0) {
            $errors[] = "Quantite invalide.";
        }

        if (!in_array($type, $this->allowedTypes, true)) {
            $errors[] = "Type invalide.";
        }

        if (!$this->isValidTrackingDate($date)) {
            $errors[] = "Date invalide.";
        }

        return $errors;
    }

    private function validateCustomAlimentInput($data)
    {
        $errors = [];
        $nom = trim((string) ($data['nom'] ?? ''));
        $calories = trim((string) ($data['calories'] ?? ''));
        $proteines = trim((string) ($data['proteines'] ?? '0'));
        $glucides = trim((string) ($data['glucides'] ?? '0'));
        $lipides = trim((string) ($data['lipides'] ?? '0'));
        $type = $data['type'] ?? '';
        $unite = $data['unite'] ?? 'g';

        if ($nom === '') {
            $errors[] = "Le nom de l'aliment est obligatoire.";
        }

        if (!is_numeric($calories) || (float) $calories <= 0) {
            $errors[] = "Les calories doivent etre un nombre superieur a 0.";
        }

        if (!is_numeric($proteines) || (float) $proteines < 0) {
            $errors[] = "Les proteines doivent etre un nombre valide.";
        }

        if (!is_numeric($glucides) || (float) $glucides < 0) {
            $errors[] = "Les glucides doivent etre un nombre valide.";
        }

        if (!is_numeric($lipides) || (float) $lipides < 0) {
            $errors[] = "Les lipides doivent etre un nombre valide.";
        }

        if (!in_array($type, $this->allowedTypes, true)) {
            $errors[] = "Type invalide.";
        }

        if (!in_array($unite, $this->allowedUnits, true)) {
            $errors[] = "Unite invalide.";
        }

        return [
            'errors' => $errors,
            'data' => [
                'nom' => $nom,
                'calories' => (float) $calories,
                'proteines' => (float) $proteines,
                'glucides' => (float) $glucides,
                'lipides' => (float) $lipides,
                'type' => $type,
                'unite' => $unite,
            ],
        ];
    }

    private function getMealItemsFromSession()
    {
        $mealItems = $_SESSION['repas'] ?? [];

        if (!is_array($mealItems)) {
            return [];
        }

        return array_values(array_filter($mealItems, 'is_array'));
    }

    private function getMealDateFromSession()
    {
        $mealDate = trim((string) ($_SESSION['repas_date'] ?? ''));

        return $this->isValidTrackingDate($mealDate) ? $mealDate : null;
    }

    private function calculateMealTotal(array $mealItems)
    {
        return array_reduce($mealItems, function ($total, $mealItem) {
            return $total + (float) ($mealItem['calories'] ?? 0);
        }, 0.0);
    }

    private function clearMealSession()
    {
        unset($_SESSION['repas'], $_SESSION['repas_date']);
    }

    private function resolveOrigin($origin)
    {
        return $origin === 'history' ? 'history' : 'main';
    }

    private function resolveHistoryFilters($query)
    {
        $period = $this->normalizeHistoryPeriod($query['history_period'] ?? '');
        $status = $this->normalizeHistoryStatus($query['history_status'] ?? '');
        $startDate = $this->normalizeHistoryFilterDate($query['history_start_date'] ?? null);
        $endDate = $this->normalizeHistoryFilterDate($query['history_end_date'] ?? null);

        if (
            $period === '' &&
            !empty($query['date']) &&
            $this->isValidTrackingDate(trim((string) $query['date']))
        ) {
            $period = 'custom';
            $startDate = trim((string) $query['date']);
            $endDate = trim((string) $query['date']);
        }

        $today = date('Y-m-d');

        if ($period === 'today') {
            $startDate = $today;
            $endDate = $today;
        } elseif ($period === 'last7') {
            $startDate = date('Y-m-d', strtotime('-6 days'));
            $endDate = $today;
        } elseif ($period === 'custom') {
            if ($startDate === null || $endDate === null) {
                $period = '';
                $startDate = null;
                $endDate = null;
            } elseif ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }
        } else {
            $startDate = null;
            $endDate = null;
        }

        return [
            'period' => $period,
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function normalizeHistoryPeriod($period)
    {
        $allowedPeriods = ['today', 'last7', 'custom'];
        $period = trim((string) $period);

        return in_array($period, $allowedPeriods, true) ? $period : '';
    }

    private function normalizeHistoryStatus($status)
    {
        $allowedStatuses = ['depasse', 'ok', 'sous', 'aucune'];
        $status = trim((string) $status);

        return in_array($status, $allowedStatuses, true) ? $status : '';
    }

    private function normalizeHistoryFilterDate($date)
    {
        $date = trim((string) $date);

        return $this->isValidTrackingDate($date) ? $date : null;
    }

    private function redirectToTrackingPage($date = null, $origin = 'main', $keepAddMode = false)
    {
        $origin = $this->resolveOrigin($origin);

        if ($keepAddMode && $origin === 'history' && $this->isValidTrackingDate($date)) {
            header("Location: index.php?controller=suivi&action=index&mode=add&date=" . urlencode((string) $date));
            exit;
        }

        header("Location: index.php?controller=suivi&action=index");

        exit;
    }
}
