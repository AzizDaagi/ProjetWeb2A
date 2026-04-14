<?php
require_once __DIR__ . '/../../model/Activite.php';
require_once __DIR__ . '/../../model/Exercice.php';

class FrontController {

    public function home() {
        require_once __DIR__ . '/../../View/front/home.php';
    }

    public function listActivites() {
        $activiteModel = new Activite();
        $activites = $activiteModel->getAll();
        
        require_once __DIR__ . '/../../View/front/activites.php';
    }

    public function showExercices() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?action=activites');
            exit;
        }

        $id = $_GET['id'];
        $activiteModel = new Activite();
        $activite = $activiteModel->getById($id);

        if (!$activite) {
            header('Location: index.php?action=activites');
            exit;
        }

        $exerciceModel = new Exercice();
        $exercices = $exerciceModel->getByActiviteId($id);

        require_once __DIR__ . '/../../View/front/exercices.php';
    }
}
?>
