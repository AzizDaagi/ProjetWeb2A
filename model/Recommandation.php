<?php
class Recommandation {
    private $id;
    private $titre;
    private $type_objectif;
    private $contenu_regle;

    public function __construct($id = null, $titre = null, $type_objectif = null, $contenu_regle = null) {
        $this->id = $id;
        $this->titre = $titre;
        $this->type_objectif = $type_objectif;
        $this->contenu_regle = $contenu_regle;
    }

    public function getId() { return $this->id; }
    public function getTitre() { return $this->titre; }
    public function getTypeObjectif() { return $this->type_objectif; }
    public function getContenuRegle() { return $this->contenu_regle; }
}
?>
