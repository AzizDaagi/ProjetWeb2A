<?php
require_once __DIR__ . '/Database.php';

class Exercice {
    private $conn;

    public $id_exercice;
    public $nom_exercice;
    public $series;
    public $repetitions;
    public $muscle_principal;
    public $muscle_secondaire;
    public $niveau_difficulte;
    public $calories_estimees;
    public $id_activite;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM exercice ORDER BY id_exercice ASC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByActiviteId($id_activite) {
        $query = "SELECT * FROM exercice WHERE id_activite = :id_activite ORDER BY id_exercice ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id_exercice) {
        $query = "SELECT * FROM exercice WHERE id_exercice = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_exercice, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO exercice (nom_exercice, series, repetitions, muscle_principal, muscle_secondaire, niveau_difficulte, calories_estimees, id_activite) 
                  VALUES (:nom, :series, :repetitions, :mp, :ms, :nd, :ce, :id_activite)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nom', $this->nom_exercice);
        $stmt->bindParam(':series', $this->series);
        $stmt->bindParam(':repetitions', $this->repetitions);
        $stmt->bindParam(':mp', $this->muscle_principal);
        $stmt->bindParam(':ms', $this->muscle_secondaire);
        $stmt->bindParam(':nd', $this->niveau_difficulte);
        $stmt->bindParam(':ce', $this->calories_estimees);
        $stmt->bindParam(':id_activite', $this->id_activite);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE exercice SET nom_exercice = :nom, series = :series, repetitions = :repetitions, 
                  muscle_principal = :mp, muscle_secondaire = :ms, niveau_difficulte = :nd, calories_estimees = :ce 
                  WHERE id_exercice = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nom', $this->nom_exercice);
        $stmt->bindParam(':series', $this->series);
        $stmt->bindParam(':repetitions', $this->repetitions);
        $stmt->bindParam(':mp', $this->muscle_principal);
        $stmt->bindParam(':ms', $this->muscle_secondaire);
        $stmt->bindParam(':nd', $this->niveau_difficulte);
        $stmt->bindParam(':ce', $this->calories_estimees);
        $stmt->bindParam(':id', $this->id_exercice, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM exercice WHERE id_exercice = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
