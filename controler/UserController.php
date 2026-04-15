<?php
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/config.php';

class UserController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function listUsers() {
        $query = $this->db->query("SELECT * FROM utilisateurs");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsers() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM utilisateurs");
        return $query->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function deleteUser($id) {
        $query = $this->db->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $query->execute(['id' => $id]);
    }

    public function toggleRole($id) {
        // Toggle between user and admin
        $user = $this->db->prepare("SELECT role FROM utilisateurs WHERE id = :id");
        $user->execute(['id' => $id]);
        $res = $user->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            $newRole = ($res['role'] === 'admin') ? 'user' : 'admin';
            $update = $this->db->prepare("UPDATE utilisateurs SET role = :newRole WHERE id = :id");
            $update->execute(['newRole' => $newRole, 'id' => $id]);
        }
    }
}
?>
