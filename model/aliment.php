<?php
class Aliment {
    private $id;
    private $nom;
    private $calories;
    private $proteines;
    private $glucides;
    private $lipides;
    private $fibres;
    private $type;
    private $image_url;

    private $pdo;

    public function __construct($arg1 = null, $nom = null, $calories = null, $proteines = 0.0, $glucides = 0.0, $lipides = 0.0, $fibres = 0.0, $type = null, $image_url = null) {
        if ($arg1 instanceof PDO) {
            $this->pdo = $arg1;
        } else {
            $this->id = $arg1;
            $this->nom = $nom;
            $this->calories = $calories;
            $this->proteines = $proteines;
            $this->glucides = $glucides;
            $this->lipides = $lipides;
            $this->fibres = $fibres;
            $this->type = $type;
            $this->image_url = $image_url;
        }
    }

    // Database Methods
    public function getAll() {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->query("SELECT * FROM aliments ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll() {
        if (!$this->pdo) return 0;
        return (int) $this->pdo->query("SELECT COUNT(*) FROM aliments")->fetchColumn();
    }

    public function getById($id) {
        if (!$this->pdo) return null;
        $stmt = $this->pdo->prepare("SELECT * FROM aliments WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        return $this->getById($id);
    }

    public function findManyByIds(array $ids) {
        if (!$this->pdo || empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM aliments WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($query) {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare("SELECT * FROM aliments WHERE nom LIKE ? LIMIT 10");
        $stmt->execute(['%' . $query . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByTypeAndName($query, $type, $limit = 5) {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare("SELECT * FROM aliments WHERE type = ? AND nom LIKE ? LIMIT ?");
        $stmt->execute([$type, '%' . $query . '%', (int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForRecipeSelection() {
        return $this->getAll();
    }

    public function create($data) {
        if (!$this->pdo) return false;
        $sql = "INSERT INTO aliments (nom, calories, proteines, glucides, lipides, fibres, type, image_url) 
                VALUES (:nom, :calories, :proteines, :glucides, :lipides, :fibres, :type, :image_url)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'nom' => $data['nom'],
            'calories' => $data['calories'],
            'proteines' => $data['proteines'] ?? 0,
            'glucides' => $data['glucides'] ?? 0,
            'lipides' => $data['lipides'] ?? 0,
            'fibres' => $data['fibres'] ?? 0,
            'type' => $data['type'],
            'image_url' => $data['image_url'] ?? null
        ]);
    }

    public function update($id, $data) {
        if (!$this->pdo) return false;
        $sql = "UPDATE aliments SET nom = :nom, calories = :calories, proteines = :proteines, 
                glucides = :glucides, lipides = :lipides, fibres = :fibres, type = :type, 
                image_url = :image_url WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete($id) {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("DELETE FROM aliments WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // Getters
    public function getId() { return $this->id; }

    public function getNom() { return $this->nom; }
    public function getCalories() { return $this->calories; }
    public function getProteines() { return $this->proteines; }
    public function getGlucides() { return $this->glucides; }
    public function getLipides() { return $this->lipides; }
    public function getFibres() { return $this->fibres; }
    public function getType() { return $this->type; }
    public function getImageUrl() { return $this->image_url; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }
    public function setCalories($calories) { $this->calories = $calories; }
    public function setProteines($proteines) { $this->proteines = $proteines; }
    public function setGlucides($glucides) { $this->glucides = $glucides; }
    public function setLipides($lipides) { $this->lipides = $lipides; }
    public function setFibres($fibres) { $this->fibres = $fibres; }
    public function setType($type) { $this->type = $type; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }
}
?>
