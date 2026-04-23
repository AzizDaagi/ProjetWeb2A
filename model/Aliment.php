<?php
class Aliment {
    private $id;
    private $nom;
    private $calories;
    private $proteines;
    private $glucides;
    private $lipides;
    private $type;
    private $image_url;

    public function __construct($id = null, $nom = null, $calories = null, $proteines = 0.0, $glucides = 0.0, $lipides = 0.0, $type = null, $image_url = null) {
        $this->id = $id;
        $this->nom = $nom;
        $this->calories = $calories;
        $this->proteines = $proteines;
        $this->glucides = $glucides;
        $this->lipides = $lipides;
        $this->type = $type;
        $this->image_url = $image_url;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getCalories() { return $this->calories; }
    public function getProteines() { return $this->proteines; }
    public function getGlucides() { return $this->glucides; }
    public function getLipides() { return $this->lipides; }
    public function getType() { return $this->type; }
    public function getImageUrl() { return $this->image_url; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }
    public function setCalories($calories) { $this->calories = $calories; }
    public function setProteines($proteines) { $this->proteines = $proteines; }
    public function setGlucides($glucides) { $this->glucides = $glucides; }
    public function setLipides($lipides) { $this->lipides = $lipides; }
    public function setType($type) { $this->type = $type; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }
}
?>
