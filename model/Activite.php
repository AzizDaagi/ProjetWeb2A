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

    public function getDashboardStats() {
        $stats = [];
        
        // Total Activities
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM activite");
        $stats['total_activities'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Exercises
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM exercice");
        $stats['total_exercises'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Calories (sum of all activities)
        $stmt = $this->conn->query("SELECT SUM(calories_brulees) AS total FROM activite");
        $stats['total_calories'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Average Duration
        $stmt = $this->conn->query("SELECT AVG(duree_minutes) AS avg_duration FROM activite");
        $stats['avg_duration'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_duration'] ?? 0, 1);
        
        // Most Popular Activity (by number of exercises)
        $queryPop = "SELECT a.nom_activite, COUNT(e.id_exercice) as exercise_count 
                     FROM activite a 
                     LEFT JOIN exercice e ON a.id_activite = e.id_activite 
                     GROUP BY a.id_activite 
                     ORDER BY exercise_count DESC LIMIT 1";
        $stmt = $this->conn->query($queryPop);
        $pop = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['popular_activity'] = $pop ? $pop['nom_activite'] : 'N/A';
        
        // Most Targeted Muscle
        $queryMuscle = "SELECT muscle_principal, COUNT(*) as count 
                        FROM exercice 
                        GROUP BY muscle_principal 
                        ORDER BY count DESC LIMIT 1";
        $stmt = $this->conn->query($queryMuscle);
        $muscle = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['most_targeted_muscle'] = $muscle ? $muscle['muscle_principal'] : 'N/A';
        
        return $stats;
    }

    public function getChartExercisesPerActivity() {
        $query = "SELECT a.nom_activite, COUNT(e.id_exercice) as count 
                  FROM activite a 
                  LEFT JOIN exercice e ON a.id_activite = e.id_activite 
                  GROUP BY a.id_activite";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChartMuscleDistribution() {
        $query = "SELECT muscle_principal, COUNT(*) as count 
                  FROM exercice 
                  GROUP BY muscle_principal";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChartCaloriesPerActivity() {
        $query = "SELECT nom_activite, calories_brulees 
                  FROM activite";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
