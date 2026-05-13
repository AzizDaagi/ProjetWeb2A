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
        $sortBy = (string) ($_GET['sort'] ?? 'name');
        $sortOrder = (string) ($_GET['order'] ?? 'ASC');
        $query = trim((string) ($_GET['q'] ?? ''));

        $this->render('front/front_list', [
            'products' => $this->filterProducts(
                $this->products->approvedSorted($sortBy, $sortOrder),
                $query
            ),
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'query' => $query,
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
        $sortBy = isset($_GET['sort']) ? (string) $_GET['sort'] : null;
        $sortOrder = isset($_GET['order']) ? (string) $_GET['order'] : null;
        $status = (string) ($_GET['status'] ?? 'all');
        $query = trim((string) ($_GET['q'] ?? ''));

        if ($sortBy !== null && $sortOrder !== null) {
            $products = $this->products->allSorted($sortBy, $sortOrder);
        } else {
            $products = $this->products->all();
        }

        if ($status === 'approved') {
            $products = array_values(array_filter($products, static fn(array $product): bool => (int) $product['is_approved'] === 1));
        } elseif ($status === 'pending') {
            $products = array_values(array_filter($products, static fn(array $product): bool => (int) $product['is_approved'] === 0));
        }

        $this->render('back/admin_list', [
            'products' => $this->filterProducts($products, $query),
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'status' => $status,
            'query' => $query,
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
        $sortBy = (string) ($_GET['sort'] ?? 'id');
        $sortOrder = (string) ($_GET['order'] ?? 'DESC');
        $query = trim((string) ($_GET['q'] ?? ''));

        $this->render('back/admin_pending', [
            'products' => $this->filterProducts(
                $this->products->pendingSorted($sortBy, $sortOrder),
                $query
            ),
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'query' => $query,
        ]);
    }
    private function filterProducts(array $products, string $query): array
    {
        if ($query === '') {
            return $products;
        }

        $needle = mb_strtolower($query);

        return array_values(array_filter($products, static function (array $product) use ($needle): bool {
            $haystack = mb_strtolower(implode(' ', [
                (string) ($product['name'] ?? ''),
                (string) ($product['description'] ?? ''),
                (string) ($product['added_by'] ?? ''),
                (string) ($product['price'] ?? ''),
                (string) ($product['calories'] ?? ''),
            ]));

            return str_contains($haystack, $needle);
        }));
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

    public function predictionPanel(): void
    {
        $this->render('back/admin_prediction', [
            'products' => $this->products->allSorted('name', 'ASC'),
        ]);
    }

    public function predictProductStats(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Invalid product id.',
                'pred_type' => 'error',
            ]);
        }

        $product = $this->products->find($id);
        if ($product === null) {
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Product not found.',
                'pred_type' => 'error',
            ]);
        }

        $predictScript = dirname(__DIR__) . '/Controller/ml_predict_regression.py';
        if (!is_file($predictScript)) {
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Prediction script not found.',
                'pred_type' => 'error',
            ]);
        }

        $name = (string) ($product['name'] ?? '');
        $description = (string) ($product['description'] ?? '');

        $command = $this->pythonCommandPrefix() . ' ' . escapeshellarg($predictScript)
            . ' --name ' . escapeshellarg($name)
            . ' --description ' . escapeshellarg($description)
            . ' 2>&1';

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        if (empty($output)) {
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Prediction failed: no output from prediction script.',
                'pred_type' => 'error',
            ]);
        }

        $raw = trim((string) end($output));
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            if (isset($decoded['error'])) {
                $this->redirect('admin.prediction.panel', [
                    'pred_message' => 'Prediction error: ' . $this->shortErrorText((string) $decoded['error']),
                    'pred_type' => 'error',
                ]);
            }
        } elseif ($exitCode !== 0) {
            $errorOutput = $this->shortErrorText(trim(implode(' | ', array_slice($output, -4))));
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Prediction failed: ' . ($errorOutput !== '' ? $errorOutput : 'unknown error'),
                'pred_type' => 'error',
            ]);
        }

        $predictedPrice = isset($decoded['predicted_price']) ? (float) $decoded['predicted_price'] : null;
        $predictedCalories = isset($decoded['predicted_calories']) ? (int) $decoded['predicted_calories'] : null;

        $message = 'Predictions for product #' . $id . ':';
        if ($predictedPrice !== null) {
            $message .= ' Price: ' . number_format($predictedPrice, 2) . ' DT';
        }
        if ($predictedCalories !== null) {
            $message .= ' | Calories: ' . $predictedCalories . ' kcal';
        }

        $this->redirect('admin.prediction.panel', [
            'pred_message' => $message,
            'pred_type' => 'success',
        ]);
    }

    public function formPredict(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin.prediction.panel');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $predictionType = (string) ($_POST['prediction_type'] ?? 'calories');
        $predictionType = $predictionType === 'price' ? 'price' : 'calories';

        if ($name === '' && $description === '') {
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Please provide a product name or description.',
                'pred_type' => 'error',
            ]);
        }

        $predictScript = dirname(__DIR__) . '/Controller/ml_predict_regression.py';
        if (!is_file($predictScript)) {
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Prediction script not found.',
                'pred_type' => 'error',
            ]);
        }

        $command = $this->pythonCommandPrefix() . ' ' . escapeshellarg($predictScript)
            . ' --name ' . escapeshellarg($name)
            . ' --description ' . escapeshellarg($description)
            . ' 2>&1';

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        if (empty($output)) {
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Prediction failed: no output from prediction script.',
                'pred_type' => 'error',
            ]);
        }

        $raw = trim((string) end($output));
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            if (isset($decoded['error'])) {
                $this->redirect('admin.prediction.panel', [
                    'pred_message' => 'Prediction error: ' . $this->shortErrorText((string) $decoded['error']),
                    'pred_type' => 'error',
                ]);
            }

            $predictedPrice = isset($decoded['predicted_price']) ? (float) $decoded['predicted_price'] : null;
            $predictedCalories = isset($decoded['predicted_calories']) ? (int) $decoded['predicted_calories'] : null;

            if ($predictionType === 'price' && $predictedPrice !== null) {
                $this->redirect('admin.prediction.panel', [
                    'pred_message' => 'Predicted Price: ' . number_format($predictedPrice, 2) . ' DT',
                    'pred_type' => 'success',
                ]);
            }

            if ($predictionType === 'calories' && $predictedCalories !== null) {
                $this->redirect('admin.prediction.panel', [
                    'pred_message' => 'Predicted Calories: ' . $predictedCalories . ' kcal',
                    'pred_type' => 'success',
                ]);
            }

            if ($predictedCalories === null && $predictedPrice === null) {
                $this->redirect('admin.prediction.panel', [
                    'pred_message' => 'Prediction did not return usable values.',
                    'pred_type' => 'error',
                ]);
            }
        }

        // If we reach here, parsing failed. If python exited non-zero, show raw output.
        if ($exitCode !== 0) {
            $errorOutput = $this->shortErrorText(trim(implode(' | ', array_slice($output, -4))));
            $this->redirect('admin.prediction.panel', [
                'pred_message' => 'Prediction failed: ' . ($errorOutput !== '' ? $errorOutput : 'unknown error'),
                'pred_type' => 'error',
            ]);
        }
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

        $templateRoot = dirname(__DIR__) . '/View/template_only';
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

        // Ensure seller name contains only letters, spaces, apostrophes or hyphens
        if (!preg_match('/^[\p{L}\s\'-]+$/u', $addedBy)) {
            throw new \InvalidArgumentException('Seller name must contain only letters, spaces, hyphens or apostrophes.');
        }

        if ($priceRaw === '' || !preg_match('/^\d+(?:\.\d+)?$/', $priceRaw)) {
            throw new \InvalidArgumentException('Price must be a valid number using only digits and an optional decimal point.');
        }

        if ($caloriesRaw === '' || !preg_match('/^\d+$/', $caloriesRaw)) {
            throw new \InvalidArgumentException('Calories must be a valid non-negative integer using only digits.');
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

    private function pythonExecutable(): string
    {
        $venvWindowsPython = dirname(__DIR__) . '/Controller/.venv/Scripts/python.exe';
        if (is_file($venvWindowsPython) && is_executable($venvWindowsPython)) {
            return $venvWindowsPython;
        }

        $venvPython = dirname(__DIR__) . '/Controller/.venv/bin/python3';
        if (is_file($venvPython) && is_executable($venvPython)) {
            return $venvPython;
        }

        return DIRECTORY_SEPARATOR === '\\' ? 'python' : 'python3';
    }

    private function pythonCommandPrefix(): string
    {
        $hfCacheDir = DIRECTORY_SEPARATOR === '\\'
            ? rtrim(sys_get_temp_dir(), '\\/') . '/projetweb1_hf_cache'
            : '/tmp/projetweb1_hf_cache';

        if (!is_dir($hfCacheDir)) {
            @mkdir($hfCacheDir, 0777, true);
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return 'set "LD_LIBRARY_PATH=" && '
                . 'set "PYTHONUTF8=1" && '
                . 'set "HF_HOME=' . $hfCacheDir . '" && '
                . 'set "HUGGINGFACE_HUB_CACHE=' . $hfCacheDir . '/hub" && '
                . 'set "TRANSFORMERS_CACHE=' . $hfCacheDir . '/transformers" && '
                . 'set "HF_HUB_DISABLE_XET=1" && '
                . 'set "LOCAL_EMBEDDINGS_ONLY=1" && '
                . escapeshellarg($this->pythonExecutable());
        }

        return 'env -u LD_LIBRARY_PATH '
            . 'PYTHONUTF8=1 '
            . 'HF_HOME=' . escapeshellarg($hfCacheDir) . ' '
            . 'HUGGINGFACE_HUB_CACHE=' . escapeshellarg($hfCacheDir . '/hub') . ' '
            . 'TRANSFORMERS_CACHE=' . escapeshellarg($hfCacheDir . '/transformers') . ' '
            . 'HF_HUB_DISABLE_XET=1 '
            . 'LOCAL_EMBEDDINGS_ONLY=1 '
            . escapeshellarg($this->pythonExecutable());
    }

    private function shortErrorText(string $message): string
    {
        $clean = preg_replace('/\s+/', ' ', trim($message));
        if ($clean === null || $clean === '') {
            return 'unknown error';
        }

        return mb_substr($clean, 0, 240);
    }
}
