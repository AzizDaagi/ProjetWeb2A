<?php
class Recette {
    private $id;
    private $nom;
    private $description;
    private $temps_preparation;
    private $niveau_difficulte;
    private $image_url;

    public function __construct($id = null, $nom = null, $description = null, $temps_preparation = null, $niveau_difficulte = null, $image_url = null) {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->temps_preparation = $temps_preparation;
        $this->niveau_difficulte = $niveau_difficulte;
        $this->image_url = $image_url;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getTempsPreparation() { return $this->temps_preparation; }
    public function getNiveauDifficulte() { return $this->niveau_difficulte; }
    public function getImageUrl() { return $this->image_url; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setTempsPreparation($temps_preparation) { $this->temps_preparation = $temps_preparation; }
    public function setNiveauDifficulte($niveau_difficulte) { $this->niveau_difficulte = $niveau_difficulte; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }
}
?>
