<?php
require_once __DIR__ . '/Database.php';

class Exercice {
    private $conn;

    public $id_exercice;
    public $nom_exercice;
    public $series;
    public $repetitions;
    public $id_activite;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getByActiviteId($id_activite) {
        $query = "SELECT * FROM exercice WHERE id_activite = :id_activite ORDER BY id_exercice ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO exercice (nom_exercice, series, repetitions, id_activite) 
                  VALUES (:nom, :series, :repetitions, :id_activite)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nom', $this->nom_exercice);
        $stmt->bindParam(':series', $this->series);
        $stmt->bindParam(':repetitions', $this->repetitions);
        $stmt->bindParam(':id_activite', $this->id_activite);

        return $stmt->execute();
    }
}
?>
