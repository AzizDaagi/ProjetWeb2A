<?php

class Produit {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        return $this->conn->query("SELECT * FROM produit");
    }

    public function getAllApproved() {
        return $this->conn->query("SELECT * FROM produit WHERE is_approved = 1");
    }

   public function create($data) {
    $name = $data['name'];
    $desc = $data['description'];
    $price = $data['price'];
    $calories = $data['calories'];
    $image = $data['image'];
    $added_by = $data['added_by'];
    $is_approved = $data['is_approved'];

    return $this->conn->query("
        INSERT INTO produit (name, description, price, calories, image, added_by, is_approved)
        VALUES ('$name','$desc','$price','$calories','$image','$added_by','$is_approved')
    ");
}

    public function delete($id) {
        return $this->conn->query("DELETE FROM produit WHERE id=$id");
    }

    public function update($id, $data) {
    $name = $data['name'];
    $desc = $data['description'];
    $price = $data['price'];
    $calories = $data['calories'];
    $added_by = $data['added_by'];

    return $this->conn->query("
        UPDATE produit 
        SET name='$name',
            description='$desc',
            price='$price',
            calories='$calories',
            added_by='$added_by'
        WHERE id=$id
    ");
}
public function getById($id) {
    return $this->conn->query("SELECT * FROM produit WHERE id=$id")->fetch_assoc();
}

public function getPending() {
    return $this->conn->query("SELECT * FROM produit WHERE is_approved = 0");
}

public function approve($id) {
    return $this->conn->query("UPDATE produit SET is_approved = 1 WHERE id=$id");
}
}