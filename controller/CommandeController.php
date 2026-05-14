<?php

require_once __DIR__ . '/../model/Commande.php';
require_once __DIR__ . '/../model/Produit.php';
require_once __DIR__ . '/../model/OrderPdf.php';
require_once __DIR__ . '/../model/Database.php';

class CommandeController
{
    private Commande $orders;
    private Produit $products;

    public function __construct(?PDO $db = null)
    {
        $connection = $db ?: Database::getConnection();
        $this->orders = new Commande($connection);
        $this->products = new Produit($connection);
    }

    public function createFront(): void
    {
        $products = $this->products->getApprovedSorted('name', 'ASC');
        $selectedProduct = (int) ($_GET['product_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->orders->create($this->validatedOrderPayload());
                header('Location: /Web/index.php?action=order-list&created=1');
                exit;
            } catch (InvalidArgumentException $exception) {
                $this->render('front/produits/order_create', [
                    'products' => $products,
                    'selectedProduct' => (int) ($_POST['product_id'] ?? $selectedProduct),
                    'old' => $this->oldOrderInput(),
                    'error' => $exception->getMessage(),
                    'pageTitle' => 'Smart Nutrition - Commander',
                    'showFooter' => true,
                ]);
                return;
            }
        }

        $this->render('front/produits/order_create', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'old' => [],
            'pageTitle' => 'Smart Nutrition - Commander',
            'showFooter' => true,
        ]);
    }

    public function frontList(): void
    {
        $this->renderOrderList('front/produits/order_list', false);
    }

    public function adminList(): void
    {
        $this->renderOrderList('back/produits/orders', true);
    }

    public function editFront(): void
    {
        $this->editCommon('front/produits/order_edit', 'order-list', false);
    }

    public function editAdmin(): void
    {
        $this->editCommon('back/produits/order_edit', 'admin-orders', true);
    }

    public function deleteFront(): void
    {
        $this->deleteAndRedirect('order-list');
    }

    public function deleteAdmin(): void
    {
        $this->deleteAndRedirect('admin-orders');
    }

    public function downloadAdminPdf(): void
    {
        $order = $this->orders->find((int) ($_GET['id'] ?? 0));
        if (!$order) {
            http_response_code(404);
            echo 'Commande introuvable.';
            return;
        }

        $pdf = new OrderPdf();
        $content = $pdf->build($order);

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $pdf->filenameForOrder($order) . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    private function renderOrderList(string $view, bool $admin): void
    {
        $sortBy = (string) ($_GET['sort'] ?? 'id');
        $sortOrder = (string) ($_GET['order'] ?? 'DESC');
        $query = trim((string) ($_GET['q'] ?? ''));

        $this->render($view, [
            'orders' => $this->orders->allSorted($sortBy, $sortOrder, $query),
            'created' => (int) ($_GET['created'] ?? 0) === 1,
            'deleted' => (int) ($_GET['deleted'] ?? 0) === 1,
            'updated' => (int) ($_GET['updated'] ?? 0) === 1,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'query' => $query,
            'pageTitle' => $admin ? 'Back Office - Commandes' : 'Smart Nutrition - Mes commandes',
            'isAdminTemplate' => $admin,
            'bodyClass' => $admin ? 'backoffice-page products-admin-page' : '',
            'showFooter' => !$admin,
        ]);
    }

    private function editCommon(string $view, string $redirectAction, bool $admin): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $order = $this->orders->find($id);
        if (!$order) {
            header('Location: /Web/index.php?action=' . $redirectAction);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->orders->update($id, $this->validatedOrderPayload());
                header('Location: /Web/index.php?action=' . $redirectAction . '&updated=1');
                exit;
            } catch (InvalidArgumentException $exception) {
                $order = array_merge($order, $this->oldOrderInput());
                $this->renderOrderForm($view, $order, $admin, $exception->getMessage());
                return;
            }
        }

        $this->renderOrderForm($view, $order, $admin);
    }

    private function renderOrderForm(string $view, array $order, bool $admin, ?string $error = null): void
    {
        $this->render($view, [
            'order' => $order,
            'products' => $this->products->getApprovedSorted('name', 'ASC'),
            'error' => $error,
            'pageTitle' => $admin ? 'Back Office - Modifier commande' : 'Smart Nutrition - Modifier commande',
            'isAdminTemplate' => $admin,
            'bodyClass' => $admin ? 'backoffice-page products-admin-page' : '',
            'showFooter' => !$admin,
        ]);
    }

    private function deleteAndRedirect(string $action): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->orders->delete($id);
        }

        header('Location: /Web/index.php?action=' . $action . '&deleted=1');
        exit;
    }

    private function validatedOrderPayload(): array
    {
        $productId = filter_var(trim((string) ($_POST['product_id'] ?? '')), FILTER_VALIDATE_INT);
        $buyerName = trim((string) ($_POST['buyer_name'] ?? ''));
        $buyerPhone = trim((string) ($_POST['buyer_phone'] ?? ''));
        $buyerAddress = trim((string) ($_POST['buyer_address'] ?? ''));
        $buyerEmail = trim((string) ($_POST['buyer_email'] ?? ''));
        $quantity = filter_var(trim((string) ($_POST['quantity'] ?? '')), FILTER_VALIDATE_INT);

        if (!$productId || !$quantity || $buyerName === '' || $buyerPhone === '' || $buyerAddress === '') {
            throw new InvalidArgumentException('Tous les champs obligatoires doivent etre remplis.');
        }

        if (!preg_match('/^[\p{L}\s\'-]+$/u', $buyerName)) {
            throw new InvalidArgumentException('Le nom doit contenir uniquement des lettres, espaces, tirets ou apostrophes.');
        }

        if (!preg_match('/^\+?\d{6,8}$/', $buyerPhone)) {
            throw new InvalidArgumentException('Le telephone doit contenir 6 a 8 chiffres.');
        }

        if ($buyerEmail !== '' && !filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Adresse email invalide.');
        }

        $product = $this->products->getById((int) $productId);
        if (!$product) {
            throw new InvalidArgumentException('Produit introuvable.');
        }

        return [
            'product_id' => (int) $productId,
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'buyer_address' => $buyerAddress,
            'buyer_email' => $buyerEmail ?: null,
            'quantity' => (int) $quantity,
            'total_price' => (float) $product['price'] * (int) $quantity,
        ];
    }

    private function oldOrderInput(): array
    {
        return [
            'product_id' => (int) ($_POST['product_id'] ?? 0),
            'buyer_name' => trim((string) ($_POST['buyer_name'] ?? '')),
            'buyer_phone' => trim((string) ($_POST['buyer_phone'] ?? '')),
            'buyer_address' => trim((string) ($_POST['buyer_address'] ?? '')),
            'buyer_email' => trim((string) ($_POST['buyer_email'] ?? '')),
            'quantity' => trim((string) ($_POST['quantity'] ?? '1')),
        ];
    }

    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/' . $view . '.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }
}
