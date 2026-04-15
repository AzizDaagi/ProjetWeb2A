<?php
class User {
    private $id;
    private $nom;
    private $email;
    private $role;

    public function __construct($id = null, $nom = null, $email = null, $role = 'user') {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->role = $role;
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }

    public function setRole($role) { $this->role = $role; }
}
?>
