<?php
require_once __DIR__ . '/../model/Activite.php';
require_once __DIR__ . '/../model/Exercice.php';

class ActiviteController {

    public function index() {
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        
        require_once __DIR__ . '/../View/activite/index.php';
    }

    public function show() {
        if (!isset($_GET['id'])) {
            header('Location: index.php');
            exit;
        }

        $id = $_GET['id'];
        $activiteModel = new Activite();
        $activite = $activiteModel->getById($id);

        if (!$activite) {
            header('Location: index.php');
            exit;
        }

        $exerciceModel = new Exercice();
        $exercices = $exerciceModel->getByActiviteId($id);

        require_once __DIR__ . '/../View/activite/show.php';
    }

    public function createActivite() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activiteModel = new Activite();
            $activiteModel->nom_activite = $_POST['nom_activite'];
            $activiteModel->description = $_POST['description'];
            $activiteModel->duree_minutes = $_POST['duree_minutes'];
            $activiteModel->calories_brulees = $_POST['calories_brulees'];
            $activiteModel->create();
            
            header('Location: index.php');
            exit;
        }
    }

    public function addExercice() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_activite'])) {
            $exerciceModel = new Exercice();
            $exerciceModel->nom_exercice = $_POST['nom_exercice'];
            $exerciceModel->series = $_POST['series'];
            $exerciceModel->repetitions = $_POST['repetitions'];
            $exerciceModel->id_activite = $_POST['id_activite'];
            $exerciceModel->create();
            
            header("Location: index.php?action=show&id=" . $_POST['id_activite']);
            exit;
        }
    }

    public function editActivite() {
        if (!isset($_GET['id'])) {
            header('Location: index.php');
            exit;
        }
        $id = $_GET['id'];
        $activiteModel = new Activite();
        $activite = $activiteModel->getById($id);

        if (!$activite) {
            header('Location: index.php');
            exit;
        }

        require_once __DIR__ . '/../View/activite/editActivite.php';
    }

    public function updateActivite() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_activite'])) {
            $activiteModel = new Activite();
            $activiteModel->id_activite = $_POST['id_activite'];
            $activiteModel->nom_activite = $_POST['nom_activite'];
            $activiteModel->description = $_POST['description'];
            $activiteModel->duree_minutes = $_POST['duree_minutes'];
            $activiteModel->calories_brulees = $_POST['calories_brulees'];
            $activiteModel->update();
            
            header('Location: index.php');
            exit;
        }
    }

    public function deleteActivite() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $activiteModel = new Activite();
            $activiteModel->delete($id);
        }
        header('Location: index.php');
        exit;
    }
}
?>
