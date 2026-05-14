<?php
require_once __DIR__ . '/../model/aliment.php';
require_once __DIR__ . '/../model/Database.php';

class AlimentController {
    private $model;

    public function __construct() {
        $pdo = Database::getConnection();
        $this->model = new Aliment($pdo);
    }

    public function listAliments() {
        return $this->model->getAll();
    }

    public function getAliment($id) {
        return $this->model->getById($id);
    }

    public function countAliments() {
        return $this->model->countAll();
    }

    public function addAliment($nom, $calories, $proteines, $glucides, $lipides, $fibres, $type, $image_url = null) {
        return $this->model->create([
            'nom' => $nom,
            'calories' => $calories,
            'proteines' => $proteines,
            'glucides' => $glucides,
            'lipides' => $lipides,
            'fibres' => $fibres,
            'type' => $type,
            'image_url' => $image_url
        ]);
    }

    public function updateAliment($id, $nom, $calories, $proteines, $glucides, $lipides, $fibres, $type, $image_url = null) {
        return $this->model->update($id, [
            'nom' => $nom,
            'calories' => $calories,
            'proteines' => $proteines,
            'glucides' => $glucides,
            'lipides' => $lipides,
            'fibres' => $fibres,
            'type' => $type,
            'image_url' => $image_url
        ]);
    }

    public function deleteAliment($id) {
        return $this->model->delete($id);
    }
}
