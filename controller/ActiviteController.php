<?php
require_once __DIR__ . '/../model/Activite.php';
require_once __DIR__ . '/../model/Exercice.php';

class ActiviteController {

    public function index() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        
        require_once __DIR__ . '/../View/admin_activite_index.php';
    }

    public function show() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }

        $id = $_GET['id'];
        $activiteModel = new Activite();
        $activite = $activiteModel->getById($id);

        if (!$activite) {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }

        $exerciceModel = new Exercice();
        $exercices = $exerciceModel->getByActiviteId($id);

        require_once __DIR__ . '/../View/admin_activite_show.php';
    }

    public function createActivite() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom_activite']);
            $duree = $_POST['duree_minutes'];
            $calories = $_POST['calories_brulees'];
            $desc = trim($_POST['description']);

            // PHP Validation Server-Side
            if (empty($nom) || empty($duree) || empty($calories) || empty($desc)) {
                $error = "PHP: Tous les champs sont obligatoires.";
            } elseif (is_numeric($nom)) {
                $error = "PHP: Le nom ne peut pas être uniquement composé de chiffres.";
            } elseif (!is_numeric($duree) || $duree <= 0) {
                $error = "PHP: La durée doit être un nombre positif.";
            } elseif (!is_numeric($calories) || $calories < 0) {
                $error = "PHP: Les calories doivent être un nombre positif ou zéro.";
            }

            if (isset($error)) {
                $activiteModel = new Activite();
                $activites = $activiteModel->getAll();
                require_once __DIR__ . '/../View/admin_activite_index.php';
                return;
            }

            require_once __DIR__ . '/../utils/ProfanityFilter.php';
            if (ProfanityFilter::checkArray([$nom, $desc])) {
                $error = "Veuillez ne pas utiliser de langage inapproprié.";
                $activiteModel = new Activite();
                $activites = $activiteModel->getAll();
                require_once __DIR__ . '/../View/admin_activite_index.php';
                return;
            }

            $activiteModel = new Activite();
            $activiteModel->nom_activite = htmlspecialchars($nom);
            $activiteModel->description = htmlspecialchars($desc);
            $activiteModel->duree_minutes = (int) $duree;
            $activiteModel->calories_brulees = (int) $calories;
            $activiteModel->create();
            
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
    }

    public function addExercice() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_activite'])) {
            $nom = trim($_POST['nom_exercice']);
            $series = $_POST['series'];
            $reps = $_POST['repetitions'];
            $muscle = trim($_POST['muscle_principal']);
            $diff = trim($_POST['niveau_difficulte']);
            $cal = $_POST['calories_estimees'];

            if (empty($nom) || empty($series) || empty($reps) || empty($muscle) || empty($diff) || empty($cal)) {
                header("Location: index.php?action=admin_show&id=" . $_POST['id_activite'] . "&error=fields");
                exit;
            }
            if (is_numeric($nom) || is_numeric($muscle)) {
                header("Location: index.php?action=admin_show&id=" . $_POST['id_activite'] . "&error=not_numeric_text");
                exit;
            }
            if (!is_numeric($series) || $series <= 0 || !is_numeric($reps) || $reps <= 0 || !is_numeric($cal) || $cal < 0) {
                header("Location: index.php?action=admin_show&id=" . $_POST['id_activite'] . "&error=invalid_numbers");
                exit;
            }

            require_once __DIR__ . '/../utils/ProfanityFilter.php';
            if (ProfanityFilter::checkArray([$nom, $muscle, $_POST['muscle_secondaire'] ?? ''])) {
                header("Location: index.php?action=admin_show&id=" . $_POST['id_activite'] . "&error=profanity");
                exit;
            }

            $exerciceModel = new Exercice();
            $exerciceModel->nom_exercice = htmlspecialchars($nom);
            $exerciceModel->series = (int) $series;
            $exerciceModel->repetitions = (int) $reps;
            $exerciceModel->muscle_principal = htmlspecialchars($muscle);
            $exerciceModel->muscle_secondaire = htmlspecialchars(trim($_POST['muscle_secondaire'] ?? ''));
            $exerciceModel->niveau_difficulte = htmlspecialchars($diff);
            $exerciceModel->calories_estimees = (int) $cal;
            $exerciceModel->id_activite = (int) $_POST['id_activite'];
            $exerciceModel->create();
            
            header("Location: index.php?action=admin_show&id=" . $_POST['id_activite']);
            exit;
        }
    }

    public function editExercice() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
        $id = (int)$_GET['id'];
        $exerciceModel = new Exercice();
        $exercice = $exerciceModel->getById($id);

        if (!$exercice) {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }

        require_once __DIR__ . '/../View/admin_editExercice.php';
    }

    public function updateExercice() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_exercice'])) {
            $nom = trim($_POST['nom_exercice']);
            $series = $_POST['series'];
            $reps = $_POST['repetitions'];
            $muscle = trim($_POST['muscle_principal']);
            $diff = trim($_POST['niveau_difficulte']);
            $cal = $_POST['calories_estimees'];

            if (empty($nom) || empty($series) || empty($reps) || empty($muscle) || empty($diff) || empty($cal)) {
                header("Location: index.php?action=editExercice&id=" . $_POST['id_exercice'] . "&error=fields");
                exit;
            }
            if (is_numeric($nom) || is_numeric($muscle)) {
                header("Location: index.php?action=editExercice&id=" . $_POST['id_exercice'] . "&error=not_numeric_text");
                exit;
            }
            if (!is_numeric($series) || $series <= 0 || !is_numeric($reps) || $reps <= 0 || !is_numeric($cal) || $cal < 0) {
                header("Location: index.php?action=editExercice&id=" . $_POST['id_exercice'] . "&error=invalid_numbers");
                exit;
            }

            require_once __DIR__ . '/../utils/ProfanityFilter.php';
            if (ProfanityFilter::checkArray([$nom, $muscle, $_POST['muscle_secondaire'] ?? ''])) {
                header("Location: index.php?action=editExercice&id=" . $_POST['id_exercice'] . "&error=profanity");
                exit;
            }

            $exerciceModel = new Exercice();
            $exerciceModel->id_exercice = (int) $_POST['id_exercice'];
            $exerciceModel->nom_exercice = htmlspecialchars($nom);
            $exerciceModel->series = (int) $series;
            $exerciceModel->repetitions = (int) $reps;
            $exerciceModel->muscle_principal = htmlspecialchars($muscle);
            $exerciceModel->muscle_secondaire = htmlspecialchars(trim($_POST['muscle_secondaire'] ?? ''));
            $exerciceModel->niveau_difficulte = htmlspecialchars($diff);
            $exerciceModel->calories_estimees = (int) $cal;
            $exerciceModel->update();
            
            if (isset($_POST['id_activite'])) {
                header("Location: index.php?action=admin_show&id=" . $_POST['id_activite']);
            } else {
                header('Location: index.php?action=admin_dashboard');
            }
            exit;
        }
    }

    public function deleteExercice() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = (int) $_GET['id'];
            $exerciceModel = new Exercice();
            $exercice = $exerciceModel->getById($id);
            if ($exercice) {
                $exerciceModel->delete($id);
                header("Location: index.php?action=admin_show&id=" . $exercice['id_activite']);
                exit;
            }
        }
        header('Location: index.php?action=admin_dashboard');
        exit;
    }

    public function editActivite() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
        $id = $_GET['id'];
        $activiteModel = new Activite();
        $activite = $activiteModel->getById($id);

        if (!$activite) {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }

        require_once __DIR__ . '/../View/admin_editActivite.php';
    }

    public function updateActivite() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_activite'])) {
            $nom = trim($_POST['nom_activite']);
            $desc = trim($_POST['description']);
            $duree = $_POST['duree_minutes'];
            $cal = $_POST['calories_brulees'];

            // PHP Validation
            if (empty($nom) || empty($desc) || empty($duree) || empty($cal)) {
                header('Location: index.php?action=editActivite&id=' . $_POST['id_activite'] . '&error=fields');
                exit;
            }
            if (is_numeric($nom)) {
                header('Location: index.php?action=editActivite&id=' . $_POST['id_activite'] . '&error=not_numeric_text');
                exit;
            }
            if (!is_numeric($duree) || $duree <= 0 || !is_numeric($cal) || $cal < 0) {
                header('Location: index.php?action=editActivite&id=' . $_POST['id_activite'] . '&error=invalid_numbers');
                exit;
            }

            require_once __DIR__ . '/../utils/ProfanityFilter.php';
            if (ProfanityFilter::checkArray([$nom, $desc])) {
                header('Location: index.php?action=editActivite&id=' . $_POST['id_activite'] . '&error=profanity');
                exit;
            }

            $activiteModel = new Activite();
            $activiteModel->id_activite = $_POST['id_activite'];
            $activiteModel->nom_activite = htmlspecialchars($nom);
            $activiteModel->description = htmlspecialchars($desc);
            $activiteModel->duree_minutes = (int) $duree;
            $activiteModel->calories_brulees = (int) $cal;
            $activiteModel->update();
            
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
    }

    public function deleteActivite() {
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = (int)$_GET['id'];
            $activiteModel = new Activite();
            $activiteModel->delete($id);
        }
        header('Location: index.php?action=admin_dashboard');
        exit;
    }
}
?>
