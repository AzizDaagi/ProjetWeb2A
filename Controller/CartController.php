<?php

namespace App\Controller;

use App\Model\Cart;
use App\Model\Product;
use App\Model\Commande;
use App\Model\Database;

class CartController extends BaseController
{
    private Product $productModel;
    private Commande $commandeModel;

    public function __construct()
    {
        $connection = Database::connection();
        $this->productModel = new Product($connection);
        $this->commandeModel = new Commande($connection);
    }

    /**
     * Add product to cart
     * POST: product_id, quantity
     * GET: redirects to cart or back to referrer
     */
    public function addToCart()
    {
        $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        $quantityRaw = trim((string) ($_POST['quantity'] ?? '1'));

        if (filter_var($quantityRaw, FILTER_VALIDATE_INT) === false) {
            $_SESSION['error'] = 'Quantity must be a valid integer';
            return $this->redirectBack();
        }

        $quantity = (int) $quantityRaw;

        if ($productId <= 0 || $quantity <= 0) {
            $_SESSION['error'] = 'Invalid product or quantity';
            return $this->redirectBack();
        }

        // Verify product exists and get price
        $product = $this->productModel->find($productId);
        if (!$product) {
            $_SESSION['error'] = 'Product not found';
            return $this->redirectBack();
        }

        // Add to cart
        Cart::addItem($productId, $quantity, (float)$product['price']);
        $_SESSION['success'] = "Product added to cart ({$quantity} item" . ($quantity > 1 ? 's' : '') . ')';

        // Redirect to cart if POST, otherwise back to referrer
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return header('Location: ' . route_url('cart.view'));
        }

        $this->redirectBack();
    }

    /**
     * View shopping cart
     */
    public function viewCart()
    {
        $cartItems = Cart::itemsWithProducts($this->productModel);

        $this->render('front/cart', [
            'cartItems' => $cartItems,
            'cartTotal' => Cart::total(),
            'cartCount' => Cart::count(),
        ]);
    }

    /**
     * Update cart item quantities
     * POST: cart_update[product_id] = quantity
     */
    public function updateCart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirectBack();
        }

        $updates = $_POST['cart_update'] ?? [];

        if (!is_array($updates)) {
            $_SESSION['error'] = 'Invalid cart data';
            return header('Location: ' . route_url('cart.view'));
        }

        $updatedCount = 0;
        foreach ($updates as $productId => $quantity) {
            $productId = (int)$productId;

            if (filter_var($quantity, FILTER_VALIDATE_INT) === false) {
                continue;
            }

            $quantity = (int)$quantity;

            if ($quantity <= 0) {
                Cart::removeItem($productId);
                $updatedCount++;
            } else {
                if (Cart::updateItem($productId, $quantity)) {
                    $updatedCount++;
                }
            }
        }

        if ($updatedCount > 0) {
            $_SESSION['success'] = 'Cart updated successfully';
        }

        return header('Location: ' . route_url('cart.view'));
    }

    /**
     * Remove item from cart
     * GET: product_id
     */
    public function removeFromCart()
    {
        $productId = (int)($_GET['product_id'] ?? 0);

        if ($productId <= 0) {
            $_SESSION['error'] = 'Invalid product';
        } else {
            if (Cart::removeItem($productId)) {
                $_SESSION['success'] = 'Item removed from cart';
            } else {
                $_SESSION['error'] = 'Item not found in cart';
            }
        }

        return header('Location: ' . route_url('cart.view'));
    }

    /**
     * Display checkout form
     */
    public function checkoutForm()
    {
        if (Cart::isEmpty()) {
            $_SESSION['error'] = 'Your cart is empty';
            return header('Location: ' . route_url('home'));
        }

        $error = isset($_SESSION['error']) ? (string) $_SESSION['error'] : null;
        unset($_SESSION['error']);

        $this->render('front/checkout', [
            'cartItems' => Cart::itemsWithProducts($this->productModel),
            'cartTotal' => Cart::total(),
            'cartCount' => Cart::count(),
            'error' => $error,
            'old' => [
                'buyer_name' => '',
                'buyer_phone' => '',
                'buyer_address' => '',
            ],
        ]);
    }

    /**
     * Process checkout and create order
     * POST: buyer_name, buyer_phone, buyer_address
     */
    public function checkout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirectBack();
        }

        if (Cart::isEmpty()) {
            $_SESSION['error'] = 'Your cart is empty';
            return header('Location: ' . route_url('home'));
        }

        // Validate buyer data
        $buyerName = trim((string) ($_POST['buyer_name'] ?? ''));
        $buyerPhone = trim((string) ($_POST['buyer_phone'] ?? ''));
        $buyerAddress = trim((string) ($_POST['buyer_address'] ?? ''));

        $old = $this->oldInput(['buyer_name', 'buyer_phone', 'buyer_address']);

        if (empty($buyerName) || empty($buyerPhone) || empty($buyerAddress)) {
            $this->renderWithFormError('front/checkout', [
                'cartItems' => Cart::itemsWithProducts($this->productModel),
                'cartTotal' => Cart::total(),
                'cartCount' => Cart::count(),
                'old' => $old,
            ], new \InvalidArgumentException('All fields are required'));
            return;
        }

        if (!preg_match('/^[0-9+\-\s()]{6,20}$/', $buyerPhone)) {
            $this->renderWithFormError('front/checkout', [
                'cartItems' => Cart::itemsWithProducts($this->productModel),
                'cartTotal' => Cart::total(),
                'cartCount' => Cart::count(),
                'old' => $old,
            ], new \InvalidArgumentException('Phone number format is invalid'));
            return;
        }

        // Create order with cart items
        $orderData = Cart::toOrderData([
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'buyer_address' => $buyerAddress,
        ]);

        $orderId = $this->commandeModel->create($orderData);

        if ($orderId) {
            Cart::clear();
            $_SESSION['created'] = true;
            return header('Location: ' . route_url('order.list'));
        } else {
            $this->renderWithFormError('front/checkout', [
                'cartItems' => Cart::itemsWithProducts($this->productModel),
                'cartTotal' => Cart::total(),
                'cartCount' => Cart::count(),
                'old' => $old,
            ], new \RuntimeException('Failed to create order. Please try again'));
            return;
        }
    }

    /**
     * Clear cart (with confirmation)
     */
    public function clearCart()
    {
        Cart::clear();
        $_SESSION['success'] = 'Cart cleared';
        return header('Location: ' . route_url('home'));
    }

    /**
     * Helper: Redirect to previous page or home
     */
    private function redirectBack(): never
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? route_url('home');
        header('Location: ' . $referrer);
        exit;
    }
}
