<?php
require_once __DIR__ . '/../../model/Activite.php';
require_once __DIR__ . '/../../model/Exercice.php';

class ActiviteController {

    public function index() {
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        
        require_once __DIR__ . '/../../View/back/activite/index.php';
    }

    public function show() {
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

        require_once __DIR__ . '/../../View/back/activite/show.php';
    }

    public function createActivite() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // PHP Validation Server-Side
            if (empty(trim($_POST['nom_activite'])) || empty(trim($_POST['duree_minutes'])) || empty(trim($_POST['calories_brulees']))) {
                $error = "PHP: Tous les champs sont obligatoires.";
                $activiteModel = new Activite();
                $activites = $activiteModel->getAll();
                require_once __DIR__ . '/../../View/back/activite/index.php';
                return;
            }

            if (!is_numeric($_POST['duree_minutes']) || $_POST['duree_minutes'] <= 0) {
                $error = "PHP: La durée doit être un nombre positif.";
                $activiteModel = new Activite();
                $activites = $activiteModel->getAll();
                require_once __DIR__ . '/../../View/back/activite/index.php';
                return;
            }

            $activiteModel = new Activite();
            $activiteModel->nom_activite = htmlspecialchars(trim($_POST['nom_activite']));
            $activiteModel->description = htmlspecialchars(trim($_POST['description']));
            $activiteModel->duree_minutes = (int) $_POST['duree_minutes'];
            $activiteModel->calories_brulees = (int) $_POST['calories_brulees'];
            $activiteModel->create();
            
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
    }

    public function addExercice() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_activite'])) {
            if (empty(trim($_POST['nom_exercice'])) || empty(trim($_POST['series'])) || empty(trim($_POST['repetitions'])) || 
                empty(trim($_POST['muscle_principal'])) || empty(trim($_POST['niveau_difficulte'])) || empty(trim($_POST['calories_estimees']))) {
                header("Location: index.php?action=admin_show&id=" . $_POST['id_activite'] . "&error=fields");
                exit;
            }

            $exerciceModel = new Exercice();
            $exerciceModel->nom_exercice = htmlspecialchars(trim($_POST['nom_exercice']));
            $exerciceModel->series = (int) $_POST['series'];
            $exerciceModel->repetitions = (int) $_POST['repetitions'];
            $exerciceModel->muscle_principal = htmlspecialchars(trim($_POST['muscle_principal']));
            $exerciceModel->muscle_secondaire = htmlspecialchars(trim($_POST['muscle_secondaire'] ?? ''));
            $exerciceModel->niveau_difficulte = htmlspecialchars(trim($_POST['niveau_difficulte']));
            $exerciceModel->calories_estimees = (int) $_POST['calories_estimees'];
            $exerciceModel->id_activite = (int) $_POST['id_activite'];
            $exerciceModel->create();
            
            header("Location: index.php?action=admin_show&id=" . $_POST['id_activite']);
            exit;
        }
    }

    public function editExercice() {
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

        require_once __DIR__ . '/../../View/back/activite/editExercice.php';
    }

    public function updateExercice() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_exercice'])) {
            if (empty(trim($_POST['nom_exercice'])) || empty(trim($_POST['series'])) || empty(trim($_POST['repetitions'])) || 
                empty(trim($_POST['muscle_principal'])) || empty(trim($_POST['niveau_difficulte'])) || empty(trim($_POST['calories_estimees']))) {
                header("Location: index.php?action=editExercice&id=" . $_POST['id_exercice'] . "&error=fields");
                exit;
            }

            $exerciceModel = new Exercice();
            $exerciceModel->id_exercice = (int) $_POST['id_exercice'];
            $exerciceModel->nom_exercice = htmlspecialchars(trim($_POST['nom_exercice']));
            $exerciceModel->series = (int) $_POST['series'];
            $exerciceModel->repetitions = (int) $_POST['repetitions'];
            $exerciceModel->muscle_principal = htmlspecialchars(trim($_POST['muscle_principal']));
            $exerciceModel->muscle_secondaire = htmlspecialchars(trim($_POST['muscle_secondaire'] ?? ''));
            $exerciceModel->niveau_difficulte = htmlspecialchars(trim($_POST['niveau_difficulte']));
            $exerciceModel->calories_estimees = (int) $_POST['calories_estimees'];
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
        if (isset($_GET['id'])) {
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

        require_once __DIR__ . '/../../View/back/activite/editActivite.php';
    }

    public function updateActivite() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_activite'])) {
            // PHP Validation
            if (empty(trim($_POST['nom_activite']))) {
                header('Location: index.php?action=editActivite&id=' . $_POST['id_activite'] . '&error=nom_vide');
                exit;
            }

            $activiteModel = new Activite();
            $activiteModel->id_activite = $_POST['id_activite'];
            $activiteModel->nom_activite = htmlspecialchars(trim($_POST['nom_activite']));
            $activiteModel->description = htmlspecialchars(trim($_POST['description']));
            $activiteModel->duree_minutes = (int) $_POST['duree_minutes'];
            $activiteModel->calories_brulees = (int) $_POST['calories_brulees'];
            $activiteModel->update();
            
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
    }

    public function deleteActivite() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $activiteModel = new Activite();
            $activiteModel->delete($id);
        }
        header('Location: index.php?action=admin_dashboard');
        exit;
    }
}
?>
