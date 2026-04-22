<?php

require_once __DIR__ . '/../models/aliment.php';
require_once __DIR__ . '/../models/objectif.php';
require_once __DIR__ . '/../models/suivi.php';
require_once __DIR__ . '/../helpers/report_mail.php';

class alimentctrl
{

    private $alimentModel;
    private $objectifModel;
    private $suiviModel;
    private $allowedTypes = ['proteine', 'glucide', 'lipide'];
    private $allowedUnits = ['g', 'piece'];

    public function __construct($pdo)
    {
        $this->alimentModel = new Aliment($pdo);
        $this->objectifModel = new Objectif($pdo);
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
        $history = [];
        $details = [];

        if ($isDetailMode) {
            $details = $this->suiviModel->getByDate($selectedDate);

            if (empty($details)) {
                $history = $this->suiviModel->getHistory();
            }
        } elseif ($isAddMode) {
            $history = [];
        } elseif ($isValidDate) {
            $history = $this->suiviModel->getHistoryByDate($selectedDate);
        } else {
            $history = $this->suiviModel->getHistory();
        }

        $total = $isAddMode
            ? $this->suiviModel->getTotalByDate($selectedDate)
            : $this->suiviModel->getTodayTotal();

        require __DIR__ . '/../views/front/aliments/index.php';
    }

    public function store()
    {
        $redirectDate = trim($_POST['date_consommation'] ?? date('Y-m-d'));
        $_POST['date_consommation'] = $redirectDate;
        $returnToAdd = !empty($_POST['return_to_add']) && $this->isValidTrackingDate($redirectDate);
        $errors = $this->validateConsommationInput($_POST);

        if (!empty($errors)) {
            $_SESSION['aliment_error'] = $errors;

            if ($returnToAdd) {
                header("Location: index.php?controller=aliment&action=index&mode=add&date=" . urlencode($redirectDate));
            } else {
                header("Location: index.php?controller=aliment&action=index");
            }
            exit;
        }

        if (!$this->suiviModel->ajouter($_POST)) {
            $_SESSION['aliment_error'] = ["Impossible d'ajouter cette consommation. Verifie la date choisie."];
        }

        if ($returnToAdd) {
            header("Location: index.php?controller=aliment&action=index&mode=add&date=" . urlencode($redirectDate));
        } else {
            header("Location: index.php?controller=aliment&action=index");
        }
        exit;
    }

    public function addAliment()
    {
        $this->alimentModel->create($_POST);

        header("Location: index.php?controller=aliment&action=index");
        exit;
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
            header("Location: index.php?controller=aliment&action=createCustom");
            exit;
        }

        $this->alimentModel->create($validation['data']);
        unset($_SESSION['custom_aliment_form']);

        header("Location: index.php");
        exit;
    }

    public function detail()
    {
        $date = $_GET['date'] ?? '';
        header("Location: index.php?controller=aliment&action=index&mode=detail&date=" . urlencode($date));
        exit;
    }
    public function delete()
    {
        if (!empty($_GET['id'])) {
            $this->suiviModel->delete($_GET['id']);
        }

        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?controller=aliment&action=index';
        header("Location: " . $redirect);
        exit;
    }

    public function edit()
    {
        if (empty($_GET['id'])) {
            header("Location: index.php?controller=aliment&action=index");
            exit;
        }

        $entry = $this->suiviModel->getById($_GET['id']);

        if (!$entry) {
            header("Location: index.php?controller=aliment&action=index");
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
            header("Location: index.php?controller=aliment&action=edit&id=" . urlencode((string) $id));
            exit;
        }

        $date = $this->suiviModel->update($_POST);

        if ($date) {
            header("Location: index.php?controller=aliment&action=index&mode=detail&date=" . urlencode($date));
        } else {
            header("Location: index.php?controller=aliment&action=index");
        }

        exit;
    }

    public function stats()
    {
        header("Location: index.php?controller=stats&action=index");
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

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO aliments (nom, calories, type)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $data['nom'],
            $data['calories'],
            $data['type']
        ]);
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
            && !$hasDateErrors
            && $date <= date('Y-m-d');
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
}
