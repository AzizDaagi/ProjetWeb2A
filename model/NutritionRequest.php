<?php
require_once __DIR__ . '/Database.php';

class NutritionRequest {
    private $conn;

    public $id;
    public $user_name;
    public $email;
    public $current_weight;
    public $current_goal;
    public $height;
    public $message;
    public $generated_activities;
    public $generated_exercises;
    public $selected_exercises;
    public $status;
    public $created_at;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function create() {
        $query = "INSERT INTO nutrition_requests 
                  (user_name, email, current_weight, current_goal, height, message, generated_activities, generated_exercises, selected_exercises, status) 
                  VALUES (:name, :email, :weight, :goal, :height, :msg, :gen_act, :gen_ex, :sel_ex, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->user_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':weight', $this->current_weight);
        $stmt->bindParam(':goal', $this->current_goal);
        $stmt->bindParam(':height', $this->height);
        $stmt->bindParam(':msg', $this->message);
        $stmt->bindParam(':gen_act', $this->generated_activities);
        $stmt->bindParam(':gen_ex', $this->generated_exercises);
        $stmt->bindParam(':sel_ex', $this->selected_exercises);
        $stmt->bindParam(':status', $this->status);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateSelectedExercises() {
        $query = "UPDATE nutrition_requests SET selected_exercises = :sel_ex WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sel_ex', $this->selected_exercises);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM nutrition_requests ORDER BY created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM nutrition_requests WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAdmin() {
        $query = "UPDATE nutrition_requests 
                  SET generated_activities = :gen_act, selected_exercises = :sel_ex, status = :status 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':gen_act', $this->generated_activities);
        $stmt->bindParam(':sel_ex', $this->selected_exercises);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM nutrition_requests WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
