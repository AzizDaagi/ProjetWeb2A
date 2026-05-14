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
        $sortBy = (string) ($_GET['sort'] ?? 'name');
        $sortOrder = (string) ($_GET['order'] ?? 'ASC');
        $query = trim((string) ($_GET['q'] ?? ''));
        $products = $this->filterProducts($this->model->getApprovedSorted($sortBy, $sortOrder), $query);

        $this->render('front/produits/list', [
            'products' => $products,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'query' => $query,
            'pageTitle' => 'Smart Nutrition - Ecommerce',
            'showFooter' => true,
        ]);
    }

    public function backList(): void
    {
        $sortBy = (string) ($_GET['sort'] ?? 'id');
        $sortOrder = (string) ($_GET['order'] ?? 'DESC');
        $status = (string) ($_GET['status'] ?? 'all');
        $query = trim((string) ($_GET['q'] ?? ''));
        $products = $this->model->getAllSorted($sortBy, $sortOrder);

        if ($status === 'approved') {
            $products = array_values(array_filter($products, static fn(array $product): bool => (int) $product['is_approved'] === 1));
        } elseif ($status === 'pending') {
            $products = array_values(array_filter($products, static fn(array $product): bool => (int) $product['is_approved'] === 0));
        }

        $products = $this->filterProducts($products, $query);

        $this->render('back/produits/list', [
            'products' => $products,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'status' => $status,
            'query' => $query,
            'pageTitle' => 'Back Office - Produits',
            'isAdminTemplate' => true,
            'bodyClass' => 'backoffice-page products-admin-page',
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->productPayload(true);
                $image = $this->uploadImage(true);
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $this->render('back/produits/create', [
                    'pageTitle' => 'Back Office - Ajouter un produit',
                    'isAdminTemplate' => true,
                    'bodyClass' => 'backoffice-page products-admin-page',
                    'error' => $exception->getMessage(),
                    'old' => $this->oldProductInput(),
                ]);
                return;
            }

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
            'old' => [],
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
            try {
                $this->model->update($id, $this->productPayload(false));
            } catch (InvalidArgumentException $exception) {
                $this->render('back/produits/edit', [
                    'product' => array_merge($product, $this->oldProductInput()),
                    'pageTitle' => 'Back Office - Modifier un produit',
                    'isAdminTemplate' => true,
                    'bodyClass' => 'backoffice-page products-admin-page',
                    'error' => $exception->getMessage(),
                ]);
                return;
            }
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
            try {
                $data = $this->productPayload(true);
                $image = $this->uploadImage(true);
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $this->render('front/produits/create', [
                    'pageTitle' => 'Smart Nutrition - Proposer un produit',
                    'showFooter' => true,
                    'error' => $exception->getMessage(),
                    'old' => $this->oldProductInput(),
                ]);
                return;
            }

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
            'old' => [],
        ]);
    }

    public function pending(): void
    {
        $sortBy = (string) ($_GET['sort'] ?? 'id');
        $sortOrder = (string) ($_GET['order'] ?? 'DESC');
        $query = trim((string) ($_GET['q'] ?? ''));
        $products = $this->filterProducts($this->model->getPendingSorted($sortBy, $sortOrder), $query);

        $this->render('back/produits/pending', [
            'products' => $products,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'query' => $query,
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

    public function predictionPanel(): void
    {
        $this->render('back/produits/prediction', [
            'products' => $this->model->getAllSorted('name', 'ASC'),
            'pageTitle' => 'Back Office - Prediction produits',
            'isAdminTemplate' => true,
            'bodyClass' => 'backoffice-page products-admin-page',
        ]);
    }

    public function predictProductStats(): void
    {
        $productId = (int) ($_GET['id'] ?? 0);
        $product = $productId > 0 ? $this->model->getById($productId) : null;

        if (!$product) {
            $this->redirectPrediction('Produit introuvable.', 'error');
        }

        $prediction = $this->predictFromText((string) $product['name'], (string) $product['description']);
        $this->redirectPrediction(
            'Prediction pour #' . $productId . ' : prix ' . number_format($prediction['price'], 2) . ' DT | calories ' . $prediction['calories'] . ' kcal',
            'success'
        );
    }

    public function formPredict(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Web/index.php?action=products-prediction');
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $predictionType = (string) ($_POST['prediction_type'] ?? 'calories');

        if ($name === '' && $description === '') {
            $this->redirectPrediction('Veuillez saisir un nom ou une description.', 'error');
        }

        $prediction = $this->predictFromText($name, $description);
        $message = $predictionType === 'price'
            ? 'Prix predit : ' . number_format($prediction['price'], 2) . ' DT'
            : 'Calories predites : ' . $prediction['calories'] . ' kcal';

        $this->redirectPrediction($message, 'success');
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
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $priceRaw = trim((string) ($_POST['price'] ?? ''));
        $caloriesRaw = trim((string) ($_POST['calories'] ?? ''));
        $addedBy = trim((string) ($_POST['added_by'] ?? ($_SESSION['user_name'] ?? 'Utilisateur')));

        if ($name === '' || $description === '' || $addedBy === '') {
            throw new InvalidArgumentException('Tous les champs obligatoires doivent etre remplis.');
        }

        if (!preg_match('/^[\p{L}\s\'-]+$/u', $addedBy)) {
            throw new InvalidArgumentException('Le vendeur doit contenir uniquement des lettres, espaces, tirets ou apostrophes.');
        }

        if ($priceRaw === '' || !preg_match('/^\d+(?:\.\d+)?$/', $priceRaw)) {
            throw new InvalidArgumentException('Le prix doit etre un nombre valide.');
        }

        if ($caloriesRaw === '' || !preg_match('/^\d+$/', $caloriesRaw)) {
            throw new InvalidArgumentException('Les calories doivent etre un entier positif.');
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'price' => (float) $priceRaw,
            'calories' => (int) $caloriesRaw,
            'added_by' => $addedBy,
        ];

        if ($includeImage) {
            $data['image'] = '';
        }

        return $data;
    }

    private function uploadImage(bool $required): ?string
    {
        if (empty($_FILES['image']['name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            if ($required) {
                throw new InvalidArgumentException('Image produit obligatoire.');
            }

            return null;
        }

        $extension = strtolower(pathinfo((string) $_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new InvalidArgumentException('Format image invalide.');
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '-', pathinfo((string) $_FILES['image']['name'], PATHINFO_FILENAME)) ?: 'product';
        $filename = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '-' . $safeName . '.' . $extension;
        $uploadDir = __DIR__ . '/../uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . '/' . $filename)) {
            throw new RuntimeException('Impossible d enregistrer l image.');
        }

        return $filename;
    }

    private function filterProducts(array $products, string $query): array
    {
        if ($query === '') {
            return $products;
        }

        $needle = mb_strtolower($query);
        return array_values(array_filter($products, static function (array $product) use ($needle): bool {
            return str_contains(mb_strtolower(implode(' ', [
                (string) ($product['name'] ?? ''),
                (string) ($product['description'] ?? ''),
                (string) ($product['added_by'] ?? ''),
                (string) ($product['price'] ?? ''),
                (string) ($product['calories'] ?? ''),
            ])), $needle);
        }));
    }

    private function oldProductInput(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price' => trim((string) ($_POST['price'] ?? '')),
            'calories' => trim((string) ($_POST['calories'] ?? '')),
            'added_by' => trim((string) ($_POST['added_by'] ?? ($_SESSION['user_name'] ?? ''))),
        ];
    }

    private function predictFromText(string $name, string $description): array
    {
        $text = mb_strtolower($name . ' ' . $description);
        $categories = [
            'fruit' => [['apple', 'banana', 'orange', 'grape', 'strawberry', 'fruit', 'mangue', 'kiwi'], [2, 15], [50, 150]],
            'vegetable' => [['carrot', 'broccoli', 'salad', 'tomato', 'vegetable', 'salade'], [1, 12], [20, 120]],
            'meat' => [['chicken', 'beef', 'fish', 'salmon', 'protein', 'viande'], [18, 80], [150, 420]],
            'dairy' => [['milk', 'cheese', 'yogurt', 'yaourt', 'dairy'], [3, 25], [50, 300]],
            'grain' => [['bread', 'rice', 'pasta', 'pain', 'riz', 'pates'], [2, 15], [100, 280]],
            'snack' => [['cookie', 'chocolate', 'cake', 'snack', 'chocolat'], [3, 25], [180, 520]],
            'beverage' => [['juice', 'water', 'drink', 'jus', 'eau'], [1, 10], [0, 180]],
        ];

        $best = null;
        $bestScore = 0;
        foreach ($categories as $category) {
            $score = 0;
            foreach ($category[0] as $keyword) {
                if (str_contains($text, $keyword)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $best = $category;
                $bestScore = $score;
            }
        }

        if ($best === null) {
            $best = [[], [3, 20], [100, 300]];
        }

        return [
            'price' => round(($best[1][0] + $best[1][1]) / 2, 2),
            'calories' => (int) round(($best[2][0] + $best[2][1]) / 2),
        ];
    }

    private function redirectPrediction(string $message, string $type): void
    {
        header('Location: /Web/index.php?action=products-prediction&pred_message=' . urlencode($message) . '&pred_type=' . urlencode($type));
        exit;
    }
}
