<?php

require_once __DIR__ . '/../models/aliment.php';

class adminAlimentCtrl
{
    private $alimentModel;
    private $typesAutorises = ['proteine', 'glucide', 'lipide'];
    private $unitesAutorisees = ['g', 'piece'];

    public function __construct($pdo)
    {
        $this->alimentModel = new Aliment($pdo);
    }

    public function index()
    {
        $this->render('aliments/index.php', [
            'pageTitle' => 'Gestion des aliments',
            'currentSection' => 'aliments',
            'aliments' => $this->alimentModel->getAll(),
        ]);
    }

    public function create()
    {
        $aliment = $_SESSION['admin_aliment_old'] ?? [];
        unset($_SESSION['admin_aliment_old']);

        $this->render('aliments/create.php', [
            'pageTitle' => 'Ajouter un aliment',
            'currentSection' => 'aliments',
            'aliment' => $aliment,
        ]);
    }

    public function store()
    {
        $_SESSION['admin_aliment_old'] = $_POST;
        $data = $this->validate($_POST);

        if (!$data) {
            header("Location: index.php?controller=adminAliment&action=create");
            exit;
        }

        $this->alimentModel->create($data);
        unset($_SESSION['admin_aliment_old']);
        $_SESSION['admin_aliment_success'] = "Aliment ajoute avec succes";

        header("Location: index.php?controller=adminAliment&action=index");
        exit;
    }

    public function edit()
    {
        if (empty($_GET['id'])) {
            header("Location: index.php?controller=adminAliment&action=index");
            exit;
        }

        $aliment = $this->alimentModel->getById($_GET['id']);

        if (!$aliment) {
            $_SESSION['admin_aliment_error'] = "Aliment introuvable";
            header("Location: index.php?controller=adminAliment&action=index");
            exit;
        }

        $oldInput = $_SESSION['admin_aliment_old'] ?? [];
        unset($_SESSION['admin_aliment_old']);

        if (!empty($oldInput) && (int) ($oldInput['id'] ?? 0) === (int) $aliment['id']) {
            $aliment = array_merge($aliment, $oldInput);
        }

        $this->render('aliments/edit.php', [
            'pageTitle' => 'Modifier un aliment',
            'currentSection' => 'aliments',
            'aliment' => $aliment,
        ]);
    }

    public function update()
    {
        $_SESSION['admin_aliment_old'] = $_POST;
        $data = $this->validate($_POST);

        if (!$data || empty($_POST['id'])) {
            header("Location: index.php?controller=adminAliment&action=edit&id=" . urlencode((string) ($_POST['id'] ?? '')));
            exit;
        }

        $data['id'] = $_POST['id'];
        $this->alimentModel->update($data);
        unset($_SESSION['admin_aliment_old']);
        $_SESSION['admin_aliment_success'] = "Aliment modifie avec succes";

        header("Location: index.php?controller=adminAliment&action=index");
        exit;
    }

    public function delete()
    {
        if (!empty($_GET['id'])) {
            $this->alimentModel->delete($_GET['id']);
            $_SESSION['admin_aliment_success'] = "Aliment supprime avec succes";
        }

        header("Location: index.php?controller=adminAliment&action=index");
        exit;
    }

    private function validate($data)
    {
        $nom = trim($data['nom'] ?? '');
        $calories = isset($data['calories']) ? (float) $data['calories'] : 0;
        $proteines = isset($data['proteines']) ? (float) $data['proteines'] : 0;
        $glucides = isset($data['glucides']) ? (float) $data['glucides'] : 0;
        $lipides = isset($data['lipides']) ? (float) $data['lipides'] : 0;
        $unite = $data['unite'] ?? 'g';
        $type = $data['type'] ?? '';

        if (
            $nom === '' ||
            $calories <= 0 ||
            $proteines < 0 ||
            $glucides < 0 ||
            $lipides < 0 ||
            !in_array($unite, $this->unitesAutorisees, true) ||
            !in_array($type, $this->typesAutorises, true)
        ) {
            $_SESSION['admin_aliment_error'] = "Nom, calories, unite, type et macros valides sont obligatoires";
            return false;
        }

        return [
            'nom' => $nom,
            'calories' => $calories,
            'unite' => $unite,
            'proteines' => $proteines,
            'glucides' => $glucides,
            'lipides' => $lipides,
            'type' => $type
        ];
    }

    private function render($relativeView, array $data = [])
    {
        extract($data, EXTR_SKIP);

        $view = __DIR__ . '/../views/back/' . ltrim($relativeView, '/');
        require __DIR__ . '/../views/back/layout.php';
    }
}
