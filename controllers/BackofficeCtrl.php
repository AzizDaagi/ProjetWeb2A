<?php

require_once __DIR__ . '/../models/aliment.php';
require_once __DIR__ . '/../models/objectif.php';
require_once __DIR__ . '/../models/suivi.php';
require_once __DIR__ . '/../models/utilisateur.php';
require_once __DIR__ . '/../services/ObjectifCalculatorService.php';

class BackofficeCtrl
{
    private $alimentModel;
    private $objectifModel;
    private $objectifCalculator;
    private $suiviModel;
    private $utilisateurModel;
    private $typesAutorises = ['proteine', 'glucide', 'lipide'];
    private $unitesAutorisees = ['g', 'piece'];

    public function __construct($pdo)
    {
        $this->alimentModel = new Aliment($pdo);
        $this->objectifModel = new Objectif($pdo);
        $this->objectifCalculator = new ObjectifCalculatorService();
        $this->suiviModel = new Suivi($pdo);
        $this->utilisateurModel = new Utilisateur($pdo);
    }

    public function dashboard()
    {
        $evolutionData = $this->suiviModel->getEvolutionData(7);

        $this->render('dashboard.php', [
            'pageTitle' => 'Dashboard',
            'currentSection' => 'dashboard',
            'totalUsers' => $this->utilisateurModel->countAll(),
            'totalRepas' => $this->suiviModel->countAllMeals(),
            'totalCalories' => $this->suiviModel->getTotalCaloriesTracked(),
            'totalAliments' => $this->alimentModel->countAll(),
            'recentUsers' => $this->utilisateurModel->getRecent(5),
            'evolutionLabels' => array_column($evolutionData, 'label'),
            'caloriesTrendPoints' => $this->buildPolylinePoints(array_column($evolutionData, 'total_calories')),
            'repasTrendPoints' => $this->buildPolylinePoints(array_column($evolutionData, 'repas_count')),
        ]);
    }

    public function users()
    {
        $this->render('users/index.php', [
            'pageTitle' => 'Utilisateurs',
            'currentSection' => 'users',
            'users' => $this->utilisateurModel->getAll(),
        ]);
    }

    public function suivi()
    {
        $this->render('aliments/index.php', [
            'pageTitle' => 'Suivi',
            'currentSection' => 'suivi',
            'aliments' => $this->alimentModel->getAll(),
        ]);
    }

    public function suiviCreate()
    {
        $aliment = $_SESSION['admin_aliment_old'] ?? [];
        unset($_SESSION['admin_aliment_old']);

        $this->render('aliments/create.php', [
            'pageTitle' => 'Ajouter un aliment',
            'currentSection' => 'suivi',
            'aliment' => $aliment,
        ]);
    }

    public function suiviStore()
    {
        $_SESSION['admin_aliment_old'] = $_POST;
        $data = $this->validateAliment($_POST);

        if (!$data) {
            header("Location: index.php?controller=backoffice&action=suiviCreate");
            exit;
        }

        $this->alimentModel->create($data);
        unset($_SESSION['admin_aliment_old']);
        $_SESSION['admin_aliment_success'] = "Aliment ajoute avec succes";

        header("Location: index.php?controller=backoffice&action=suivi");
        exit;
    }

    public function suiviEdit()
    {
        if (empty($_GET['id'])) {
            header("Location: index.php?controller=backoffice&action=suivi");
            exit;
        }

        $aliment = $this->alimentModel->getById($_GET['id']);

        if (!$aliment) {
            $_SESSION['admin_aliment_error'] = "Aliment introuvable";
            header("Location: index.php?controller=backoffice&action=suivi");
            exit;
        }

        $oldInput = $_SESSION['admin_aliment_old'] ?? [];
        unset($_SESSION['admin_aliment_old']);

        if (!empty($oldInput) && (int) ($oldInput['id'] ?? 0) === (int) $aliment['id']) {
            $aliment = array_merge($aliment, $oldInput);
        }

        $this->render('aliments/edit.php', [
            'pageTitle' => 'Modifier un aliment',
            'currentSection' => 'suivi',
            'aliment' => $aliment,
        ]);
    }

    public function suiviUpdate()
    {
        $_SESSION['admin_aliment_old'] = $_POST;
        $data = $this->validateAliment($_POST);

        if (!$data || empty($_POST['id'])) {
            header("Location: index.php?controller=backoffice&action=suiviEdit&id=" . urlencode((string) ($_POST['id'] ?? '')));
            exit;
        }

        $data['id'] = $_POST['id'];
        $this->alimentModel->update($data);
        unset($_SESSION['admin_aliment_old']);
        $_SESSION['admin_aliment_success'] = "Aliment modifie avec succes";

        header("Location: index.php?controller=backoffice&action=suivi");
        exit;
    }

    public function suiviDelete()
    {
        if (!empty($_GET['id'])) {
            $this->alimentModel->delete($_GET['id']);
            $_SESSION['admin_aliment_success'] = "Aliment supprime avec succes";
        }

        header("Location: index.php?controller=backoffice&action=suivi");
        exit;
    }

    public function objectifs()
    {
        $objectifs = $this->objectifModel->getAll();

        foreach ($objectifs as &$objectif) {
            $objectif['activite_label'] = $this->objectifCalculator->getActiviteLabel($objectif['activite'] ?? null);
        }
        unset($objectif);

        $this->render('objectifs/index.php', [
            'pageTitle' => 'Objectifs',
            'currentSection' => 'objectifs',
            'objectifs' => $objectifs,
            'sexeOptions' => $this->objectifCalculator->getSexeOptions(),
            'objectifTypeOptions' => $this->objectifCalculator->getObjectifTypeOptions(),
        ]);
    }

    public function objectifShow()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $objectif = $id > 0 ? $this->objectifModel->getById($id) : null;

        if (!$objectif) {
            $_SESSION['admin_objectif_error'] = "Objectif introuvable";
            header("Location: index.php?controller=backoffice&action=objectifs");
            exit;
        }

        $this->render('objectifs/show.php', [
            'pageTitle' => 'Detail objectif',
            'currentSection' => 'objectifs',
            'objectif' => $objectif,
            'objectifSummary' => $this->hasPhysicalProfile($objectif)
                ? $this->objectifCalculator->calculateNutritionTargets($objectif)
                : [],
            'repasCount' => $this->objectifModel->countLinkedMeals($id),
            'sexeLabel' => $this->objectifCalculator->getSexeLabel($objectif['sexe'] ?? 'homme'),
            'activiteLabel' => $this->objectifCalculator->getActiviteLabel($objectif['activite'] ?? null),
            'objectifTypeLabel' => $this->objectifCalculator->getObjectifTypeLabel($objectif['objectif_type'] ?? 'maintien'),
        ]);
    }

    public function objectifDelete()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $isDeleted = $this->objectifModel->delete($id);

            if ($isDeleted) {
                $_SESSION['admin_objectif_success'] = "Objectif supprime avec succes";
            } else {
                $_SESSION['admin_objectif_error'] = $this->objectifModel->getLastError() ?: "Impossible de supprimer l'objectif";
            }
        }

        header("Location: index.php?controller=backoffice&action=objectifs");
        exit;
    }

    private function render($relativeView, array $data = [])
    {
        extract($data, EXTR_SKIP);

        $view = __DIR__ . '/../views/back/' . ltrim($relativeView, '/');
        require __DIR__ . '/../views/back/layout.php';
    }

    private function buildPolylinePoints(array $values)
    {
        if (empty($values)) {
            return '0,220 860,220';
        }

        $width = 860;
        $maxHeight = 220;
        $topPadding = 18;
        $bottomPadding = 18;
        $maxValue = max(1, (float) max($values));
        $count = count($values);
        $stepX = $count > 1 ? $width / ($count - 1) : $width;
        $points = [];

        foreach ($values as $index => $value) {
            $x = round($index * $stepX, 2);
            $normalized = ((float) $value) / $maxValue;
            $usableHeight = $maxHeight - $topPadding - $bottomPadding;
            $y = round($maxHeight - ($normalized * $usableHeight) - $bottomPadding, 2);
            $points[] = $x . ',' . $y;
        }

        return implode(' ', $points);
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

    private function validateAliment($data)
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
            'type' => $type,
        ];
    }
}
