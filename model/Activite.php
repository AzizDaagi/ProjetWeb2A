<?php
require_once __DIR__ . '/Database.php';

class Activite {
    private $conn;

    public $id_activite;
    public $nom_activite;
    public $description;
    public $duree_minutes;
    public $calories_brulees;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM activite ORDER BY id_activite DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM activite WHERE id_activite = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO activite (nom_activite, description, duree_minutes, calories_brulees) 
                  VALUES (:nom, :desc, :duree, :cal)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nom', $this->nom_activite);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':duree', $this->duree_minutes);
        $stmt->bindParam(':cal', $this->calories_brulees);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE activite SET nom_activite = :nom, description = :desc, duree_minutes = :duree, calories_brulees = :cal WHERE id_activite = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nom', $this->nom_activite);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':duree', $this->duree_minutes);
        $stmt->bindParam(':cal', $this->calories_brulees);
        $stmt->bindParam(':id', $this->id_activite, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM activite WHERE id_activite = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
