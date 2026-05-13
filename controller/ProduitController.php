<?php

require_once __DIR__ . '/../model/Produit.php';

class ProduitController
{
    private Produit $model;

    public function __construct(?PDO $db = null)
    {
        $this->model = new Produit($db ?: Database::getConnection());
    }

    public function frontList(): void
    {
        $products = $this->model->getAllApproved();
        $this->render('front/produits/list', [
            'products' => $products,
            'pageTitle' => 'Smart Nutrition - Ecommerce',
            'showFooter' => true,
        ]);
    }

    public function backList(): void
    {
        $products = $this->model->getAll();
        $this->render('back/produits/list', [
            'products' => $products,
            'pageTitle' => 'Back Office - Produits',
            'isAdminTemplate' => true,
            'bodyClass' => 'backoffice-page products-admin-page',
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->productPayload(true);
            $image = $this->uploadImage(true);

            if ($image !== null) {
                $data['image'] = $image;
            }

            $data['is_approved'] = 1;
            $this->model->create($data);

            header('Location: /Web/index.php?action=products-admin');
            exit;
        }

        $this->render('back/produits/create', [
            'pageTitle' => 'Back Office - Ajouter un produit',
            'isAdminTemplate' => true,
            'bodyClass' => 'backoffice-page products-admin-page',
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->model->getById($id);

        if (!$product) {
            header('Location: /Web/index.php?action=products-admin');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $this->productPayload(false));
            header('Location: /Web/index.php?action=products-admin');
            exit;
        }

        $this->render('back/produits/edit', [
            'product' => $product,
            'pageTitle' => 'Back Office - Modifier un produit',
            'isAdminTemplate' => true,
            'bodyClass' => 'backoffice-page products-admin-page',
        ]);
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
        }

        $target = ($_GET['from'] ?? '') === 'pending' ? 'products-pending' : 'products-admin';
        header('Location: /Web/index.php?action=' . $target);
        exit;
    }

    public function frontCreate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->productPayload(true);
            $image = $this->uploadImage(true);

            if ($image !== null) {
                $data['image'] = $image;
            }

            $data['is_approved'] = 0;
            $this->model->create($data);

            header('Location: /Web/index.php?action=foods-management&submitted=1');
            exit;
        }

        $this->render('front/produits/create', [
            'pageTitle' => 'Smart Nutrition - Proposer un produit',
            'showFooter' => true,
        ]);
    }

    public function pending(): void
    {
        $products = $this->model->getPending();
        $this->render('back/produits/pending', [
            'products' => $products,
            'pageTitle' => 'Back Office - Produits en attente',
            'isAdminTemplate' => true,
            'bodyClass' => 'backoffice-page products-admin-page',
        ]);
    }

    public function approve(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->model->approve($id);
        }

        header('Location: /Web/index.php?action=products-pending');
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/' . $view . '.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    private function productPayload(bool $includeImage): array
    {
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price' => (float) ($_POST['price'] ?? 0),
            'calories' => (int) ($_POST['calories'] ?? 0),
            'added_by' => trim((string) ($_POST['added_by'] ?? ($_SESSION['user_name'] ?? 'Utilisateur'))),
        ];

        if ($includeImage) {
            $data['image'] = '';
        }

        return $data;
    }

    private function uploadImage(bool $required): ?string
    {
        if (empty($_FILES['image']['name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            return $required ? '' : null;
        }

        $extension = strtolower(pathinfo((string) $_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $allowedExtensions, true)) {
            return $required ? '' : null;
        }

        $filename = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $uploadDir = __DIR__ . '/../uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . '/' . $filename);

        return $filename;
    }
}
