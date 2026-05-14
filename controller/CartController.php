<?php

require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../model/Commande.php';
require_once __DIR__ . '/../model/Produit.php';
require_once __DIR__ . '/../model/Database.php';

class CartController
{
    private Produit $products;
    private Commande $orders;

    public function __construct(?PDO $db = null)
    {
        $connection = $db ?: Database::getConnection();
        $this->products = new Produit($connection);
        $this->orders = new Commande($connection);
    }

    public function add(): void
    {
        $productId = (int) ($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $quantityRaw = trim((string) ($_POST['quantity'] ?? '1'));

        if ($productId <= 0 || filter_var($quantityRaw, FILTER_VALIDATE_INT) === false || (int) $quantityRaw <= 0) {
            $_SESSION['error'] = 'Produit ou quantite invalide.';
            $this->redirectBack();
        }

        $product = $this->products->getById($productId);
        if (!$product || (int) ($product['is_approved'] ?? 0) !== 1) {
            $_SESSION['error'] = 'Produit introuvable.';
            $this->redirectBack();
        }

        Cart::addItem($productId, (int) $quantityRaw, (float) $product['price']);
        $_SESSION['success'] = 'Produit ajoute au panier.';
        header('Location: /Web/index.php?action=cart-view');
        exit;
    }

    public function view(): void
    {
        $this->render('front/produits/cart', [
            'cartItems' => Cart::itemsWithProducts($this->products),
            'cartTotal' => Cart::total(),
            'cartCount' => Cart::count(),
            'pageTitle' => 'Smart Nutrition - Panier',
            'showFooter' => true,
        ]);
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $updates = $_POST['cart_update'] ?? [];
        if (!is_array($updates)) {
            $_SESSION['error'] = 'Panier invalide.';
            header('Location: /Web/index.php?action=cart-view');
            exit;
        }

        foreach ($updates as $productId => $quantity) {
            if (filter_var($quantity, FILTER_VALIDATE_INT) === false) {
                continue;
            }
            Cart::updateItem((int) $productId, (int) $quantity);
        }

        $_SESSION['success'] = 'Panier mis a jour.';
        header('Location: /Web/index.php?action=cart-view');
        exit;
    }

    public function remove(): void
    {
        Cart::removeItem((int) ($_GET['product_id'] ?? 0));
        $_SESSION['success'] = 'Produit retire du panier.';
        header('Location: /Web/index.php?action=cart-view');
        exit;
    }

    public function checkoutForm(array $old = [], ?string $error = null): void
    {
        if (Cart::isEmpty()) {
            $_SESSION['error'] = 'Votre panier est vide.';
            header('Location: /Web/index.php?action=foods-management');
            exit;
        }

        $this->render('front/produits/checkout', [
            'cartItems' => Cart::itemsWithProducts($this->products),
            'cartTotal' => Cart::total(),
            'cartCount' => Cart::count(),
            'old' => $old ?: ['buyer_name' => '', 'buyer_phone' => '', 'buyer_address' => '', 'buyer_email' => ''],
            'error' => $error,
            'pageTitle' => 'Smart Nutrition - Checkout',
            'showFooter' => true,
        ]);
    }

    public function checkout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        if (Cart::isEmpty()) {
            $_SESSION['error'] = 'Votre panier est vide.';
            header('Location: /Web/index.php?action=foods-management');
            exit;
        }

        $buyerName = trim((string) ($_POST['buyer_name'] ?? ''));
        $buyerPhone = trim((string) ($_POST['buyer_phone'] ?? ''));
        $buyerAddress = trim((string) ($_POST['buyer_address'] ?? ''));
        $buyerEmail = trim((string) ($_POST['buyer_email'] ?? ''));
        $old = compact('buyerName', 'buyerPhone', 'buyerAddress', 'buyerEmail');
        $old = [
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'buyer_address' => $buyerAddress,
            'buyer_email' => $buyerEmail,
        ];

        if ($buyerName === '' || $buyerPhone === '' || $buyerAddress === '' || $buyerEmail === '') {
            $this->checkoutForm($old, 'Tous les champs sont obligatoires.');
            return;
        }

        if (!preg_match('/^[\p{L}\s\'-]+$/u', $buyerName)) {
            $this->checkoutForm($old, 'Le nom doit contenir uniquement des lettres, espaces, tirets ou apostrophes.');
            return;
        }

        if (!preg_match('/^\+?\d{6,8}$/', $buyerPhone)) {
            $this->checkoutForm($old, 'Le telephone doit contenir 6 a 8 chiffres.');
            return;
        }

        if (!filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
            $this->checkoutForm($old, 'Adresse email invalide.');
            return;
        }

        $orderId = $this->orders->create(Cart::toOrderData($old));
        if (!$orderId) {
            $this->checkoutForm($old, 'Impossible de creer la commande.');
            return;
        }

        Cart::clear();
        header('Location: /Web/index.php?action=order-list&created=1');
        exit;
    }

    public function clear(): void
    {
        Cart::clear();
        $_SESSION['success'] = 'Panier vide.';
        header('Location: /Web/index.php?action=foods-management');
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/' . $view . '.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    private function redirectBack(): void
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/Web/index.php?action=foods-management'));
        exit;
    }
}
