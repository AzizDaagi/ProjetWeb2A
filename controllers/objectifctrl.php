<?php

require_once __DIR__ . '/../models/objectif.php';
require_once __DIR__ . '/../models/suivi.php';

class objectifctrl
{
    private $objectifModel;
    private $suiviModel;
    private $allowedObjectifTypes = ['maintien', 'prise_muscle'];

    public function __construct($pdo)
    {
        $this->objectifModel = new Objectif($pdo);
        $this->suiviModel = new Suivi($pdo);
    }

    public function index()
    {
        $objectif = $this->objectifModel->getLatest();
        $total_today = $this->suiviModel->getTodayTotal();
        $todayMacros = $this->suiviModel->getTodayMacros();

        require __DIR__ . '/../views/front/objectif/index.php';
    }

    public function store()
    {
        $validation = $this->validateObjectifInput($_POST);

        if (!empty($validation['errors'])) {
            $_SESSION['objectif_error'] = $validation['errors'];
            $_SESSION['objectif_form'] = [
                'calories_cible' => trim((string) ($_POST['calories_cible'] ?? '')),
                'objectif_type' => $_POST['objectif_type'] ?? 'maintien',
            ];
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $calories = $validation['calories'];
        $objectifType = $validation['objectif_type'];
        $data = $this->buildObjectifData($calories, $objectifType);
        $existingObjectif = $this->objectifModel->getLatest();
        unset($_SESSION['objectif_form']);

        if ($existingObjectif) {
            $data['id'] = $existingObjectif['id'];
            $this->objectifModel->update($data);
            $_SESSION['objectif_success'] = "Objectif mis a jour avec succes";
        } else {
            $this->objectifModel->save($data);
            $_SESSION['objectif_success'] = "Objectif cree avec succes";
        }

        header("Location: index.php?controller=objectif&action=index");
        exit;
    }

    public function edit()
    {
        $objectif = $this->objectifModel->getLatest();

        if (!$objectif) {
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        require __DIR__ . '/../views/front/objectif/edit.php';
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        $validation = $this->validateObjectifInput($_POST);

        if (!$id || !empty($validation['errors'])) {
            $_SESSION['objectif_error'] = !empty($validation['errors']) ? $validation['errors'] : ["Objectif invalide."];
            $_SESSION['objectif_form'] = [
                'calories_cible' => trim((string) ($_POST['calories_cible'] ?? '')),
                'objectif_type' => $_POST['objectif_type'] ?? 'maintien',
            ];
            header("Location: index.php?controller=objectif&action=index");
            exit;
        }

        $calories = $validation['calories'];
        $objectifType = $validation['objectif_type'];
        $data = $this->buildObjectifData($calories, $objectifType);
        $data['id'] = $id;

        $this->objectifModel->update($data);

        header("Location: index.php?controller=objectif&action=index");
        exit;
    }

    public function delete()
    {
        if (!empty($_GET['id'])) {
            $this->objectifModel->delete($_GET['id']);
        }

        header("Location: index.php?controller=objectif&action=index");
        exit;
    }

    private function buildObjectifData($calories, $objectifType)
    {
        if (!in_array($objectifType, $this->allowedObjectifTypes, true)) {
            $objectifType = 'maintien';
        }

        if ($objectifType === 'prise_muscle') {
            $proteines = ($calories * 0.35) / 4;
            $lipides = ($calories * 0.20) / 9;
            $glucides = ($calories * 0.45) / 4;
        } else {
            $proteines = ($calories * 0.30) / 4;
            $lipides = ($calories * 0.25) / 9;
            $glucides = ($calories * 0.45) / 4;
        }

        return [
            'calories_cible' => $calories,
            'objectif_type' => $objectifType,
            'proteines' => round($proteines),
            'lipides' => round($lipides),
            'glucides' => round($glucides),
        ];
    }

    private function validateObjectifInput($data)
    {
        $errors = [];
        $caloriesInput = trim((string) ($data['calories_cible'] ?? ''));
        $objectifType = $data['objectif_type'] ?? 'maintien';
        $calories = null;

        if ($caloriesInput === '') {
            $errors[] = "Les calories cibles sont obligatoires.";
        } elseif (!is_numeric($caloriesInput)) {
            $errors[] = "Les calories cibles doivent etre numeriques.";
        } else {
            $calories = (float) $caloriesInput;

            if ($calories <= 0) {
                $errors[] = "Les calories cibles doivent etre superieures a 0.";
            } elseif ($calories > 10000) {
                $errors[] = "Les calories cibles doivent etre au maximum de 10000 kcal.";
            }
        }

        if (!in_array($objectifType, $this->allowedObjectifTypes, true)) {
            $errors[] = "Type d'objectif invalide.";
        }

        return [
            'errors' => $errors,
            'calories' => $calories !== null ? (int) round($calories) : 0,
            'objectif_type' => $objectifType,
        ];
    }
}
