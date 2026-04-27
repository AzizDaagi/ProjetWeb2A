<?php

namespace App\Controller;

use App\Model\Database;
use App\Model\Product;

class ProductController extends BaseController
{
    private Product $products;

    public function __construct()
    {
        $this->products = new Product(Database::connection());
    }

    public function index(): void
    {
        $this->render('front/front_list', [
            'products' => $this->products->approved(),
        ]);
    }

    public function createFront(): void
    {
        if ($this->isPost()) {
            try {
                $payload = $this->validatedPayload();
                $payload['image'] = $this->uploadImage();
                $payload['is_approved'] = 0;

                $this->products->create($payload);
                $this->redirect('home');
            } catch (\InvalidArgumentException | \RuntimeException $exception) {
                $this->renderWithFormError('front/front_create', [
                    'old' => $this->oldProductInput(),
                ], $exception);
                return;
            }
        }

        $this->render('front/front_create', [
            'old' => [],
        ]);
    }

    public function adminIndex(): void
    {
        $this->render('back/admin_list', [
            'products' => $this->products->all(),
        ]);
    }

    public function createAdmin(): void
    {
        if ($this->isPost()) {
            try {
                $payload = $this->validatedPayload();
                $payload['image'] = $this->uploadImage();
                $payload['is_approved'] = 1;

                $this->products->create($payload);
                $this->redirect('admin.products');
            } catch (\InvalidArgumentException | \RuntimeException $exception) {
                $this->renderWithFormError('back/admin_create', [
                    'old' => $this->oldProductInput(),
                ], $exception);
                return;
            }
        }

        $this->render('back/admin_create', [
            'old' => [],
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->products->find($id);

        if ($product === null) {
            http_response_code(404);
            echo 'Product not found.';
            return;
        }

        if ($this->isPost()) {
            try {
                $this->products->update($id, $this->validatedPayload(false));
                $this->redirect('admin.products');
            } catch (\InvalidArgumentException | \RuntimeException $exception) {
                $this->renderWithFormError('back/admin_edit', [
                    'product' => array_merge($product, $this->oldProductInput()),
                ], $exception);
                return;
            }
        }

        $this->render('back/admin_edit', [
            'product' => $product,
        ]);
    }

    private function oldProductInput(): array
    {
        return $this->oldInput([
            'name',
            'description',
            'price',
            'calories',
            'added_by',
        ]);
    }

    public function pending(): void
    {
        $this->render('back/admin_pending', [
            'products' => $this->products->pending(),
        ]);
    }

    public function approve(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->products->approve($id);
        }

        $this->redirect('admin.products.pending');
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->products->delete($id);
        }

        $redirectAction = ($_GET['from'] ?? '') === 'pending'
            ? 'admin.products.pending'
            : 'admin.products';

        $this->redirect($redirectAction);
    }

    public function previewTemplate(): void
    {
        $templates = [
            'front-home' => 'front/home.php',
            'front-login' => 'front/auth/login.php',
            'front-register' => 'front/auth/register.php',
            'back-dashboard' => 'back/admin/dashboard.php',
            'back-users-list' => 'back/users/list.php',
            'back-users-edit' => 'back/users/edit.php',
        ];

        $selectedPage = (string) ($_GET['page'] ?? 'back-dashboard');
        if (!isset($templates[$selectedPage])) {
            $selectedPage = 'back-dashboard';
        }

        $templateRoot = dirname(__DIR__) . '/template_only';
        $templatePage = $templateRoot . '/' . $templates[$selectedPage];

        if (!is_file($templatePage)) {
            throw new \RuntimeException('Template preview file not found.');
        }

        $headerPath = $templateRoot . '/layouts/header.php';
        $footerPath = $templateRoot . '/layouts/footer.php';

        if (!is_file($headerPath) || !is_file($footerPath)) {
            throw new \RuntimeException('Template layout files not found.');
        }

        $pageTitle = 'Template Preview';
        $showNav = true;
        $showFooter = true;
        $totalUsers = 42;
        $recentUsers = [
            ['id' => 10, 'prenom' => 'Nour', 'nom' => 'Ali', 'email' => 'nour@example.com'],
            ['id' => 9, 'prenom' => 'Karim', 'nom' => 'Ben', 'email' => 'karim@example.com'],
            ['id' => 8, 'prenom' => 'Aya', 'nom' => 'Mansour', 'email' => 'aya@example.com'],
        ];
        $users = [
            ['id' => 1, 'nom' => 'Admin', 'prenom' => 'Main', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['id' => 2, 'nom' => 'User', 'prenom' => 'First', 'email' => 'user@example.com', 'role' => 'user'],
        ];
        $user = ['id' => 1, 'nom' => 'Admin', 'prenom' => 'Main', 'email' => 'admin@example.com', 'role' => 'admin'];

        require $headerPath;
        require $templatePage;
        require $footerPath;
    }

    private function validatedPayload(bool $requireImage = true): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $priceRaw = trim((string) ($_POST['price'] ?? ''));
        $caloriesRaw = trim((string) ($_POST['calories'] ?? ''));
        $addedBy = trim((string) ($_POST['added_by'] ?? ''));

        if ($name === '' || $description === '' || $addedBy === '') {
            throw new \InvalidArgumentException('Missing required fields.');
        }

        if ($priceRaw === '' || !is_numeric($priceRaw)) {
            throw new \InvalidArgumentException('Price must be a valid number.');
        }

        if ($caloriesRaw === '' || filter_var($caloriesRaw, FILTER_VALIDATE_INT) === false) {
            throw new \InvalidArgumentException('Calories must be a valid integer.');
        }

        $price = (float) $priceRaw;
        $calories = (int) $caloriesRaw;

        if ($price < 0 || $calories < 0) {
            throw new \InvalidArgumentException('Price and calories must be zero or positive values.');
        }

        if ($requireImage && empty($_FILES['image']['name'])) {
            throw new \InvalidArgumentException('Product image is required.');
        }

        return [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'calories' => $calories,
            'added_by' => $addedBy,
        ];
    }

    private function uploadImage(): string
    {
        if (empty($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            throw new \RuntimeException('Image upload failed.');
        }

        $originalName = basename((string) $_FILES['image']['name']);
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '-', $originalName) ?: 'product-image';
        $fileName = uniqid('product_', true) . '-' . $safeName;
        $uploadDirectory = dirname(__DIR__) . '/View/uploads';

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
            throw new \RuntimeException('Unable to create upload directory.');
        }

        if (!is_writable($uploadDirectory)) {
            @chmod($uploadDirectory, 0777);
        }

        if (!is_writable($uploadDirectory)) {
            throw new \RuntimeException('Upload directory is not writable: ' . $uploadDirectory);
        }

        $targetPath = $uploadDirectory . '/' . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Unable to save uploaded image.');
        }

        return $fileName;
    }
}
