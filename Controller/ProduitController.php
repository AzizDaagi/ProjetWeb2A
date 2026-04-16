<?php

require_once __DIR__ . '/../Model/Produit.php';

class ProduitController {
    private $model;

    public function __construct($db) {
        $this->model = new Produit($db);
    }

    // FRONT OFFICE (show products)
    public function frontList() {
        $products = $this->model->getAllApproved();
        include __DIR__ . '/../View/FrontOffice/produits/list.php';
    }

    // BACK OFFICE (admin list)
    public function backList() {
        $products = $this->model->getAll();
        include __DIR__ . '/../View/BackOffice/produits/list.php';
    }

    // CREATE PRODUCT
    public function create() {
        if ($_POST) {

            $image = $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $image);

            $_POST['image'] = $image;
            $_POST['is_approved'] = 1;

            $this->model->create($_POST);

            header("Location: index.php?action=backList");
            exit;
        }

        include __DIR__ . '/../View/BackOffice/produits/create.php';
    }

    // DELETE PRODUCT
    public function delete() {
        $id = $_GET['id'];

        $this->model->delete($id);

        header("Location: index.php?action=backList");
        exit;
    }

    public function edit() {
    $id = $_GET['id'];

    // get product
    $product = $this->model->getById($id);

    // update if form submitted
    if ($_POST) {
        $this->model->update($id, $_POST);

        header("Location: index.php?action=backList");
        exit;
    }

    include __DIR__ . '/../View/BackOffice/produits/edit.php';
}

public function frontCreate() {
    if ($_POST) {

        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $image);

        $_POST['image'] = $image;
        $_POST['is_approved'] = 0; 

        $this->model->create($_POST);

        echo "Product sent for approval ✅";
        return;
    }

    include __DIR__ . '/../View/FrontOffice/produits/create.php';
}

public function pending() {
    $products = $this->model->getPending();
    include __DIR__ . '/../View/BackOffice/produits/pending_clean.php';
}

public function approve() {
    $id = $_GET['id'];

    $this->model->approve($id);

    header("Location: index.php?action=pending");
    exit;
}


}
