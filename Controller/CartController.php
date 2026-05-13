<?php

namespace App\Controller;

use App\Model\Cart;
use App\Model\Product;
use App\Model\Commande;
use App\Model\Database;
use App\Service\Mailer;

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
                'buyer_email' => '',
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
        $buyerEmail = trim((string) ($_POST['buyer_email'] ?? ''));

        $old = $this->oldInput(['buyer_name', 'buyer_phone', 'buyer_address', 'buyer_email']);

        if (empty($buyerName) || empty($buyerPhone) || empty($buyerAddress) || empty($buyerEmail)) {
            $this->renderWithFormError('front/checkout', [
                'cartItems' => Cart::itemsWithProducts($this->productModel),
                'cartTotal' => Cart::total(),
                'cartCount' => Cart::count(),
                'old' => $old,
            ], new \InvalidArgumentException('All fields are required'));
            return;
        }

        // Buyer name: only letters, spaces, hyphens or apostrophes
        if (!preg_match('/^[\p{L}\s\'-]+$/u', $buyerName)) {
            $this->renderWithFormError('front/checkout', [
                'cartItems' => Cart::itemsWithProducts($this->productModel),
                'cartTotal' => Cart::total(),
                'cartCount' => Cart::count(),
                'old' => $old,
            ], new \InvalidArgumentException('Buyer name must contain only letters, spaces, hyphens or apostrophes'));
            return;
        }

        // Phone: optional leading + and 6-8 digits (no spaces or punctuation)
        if (!preg_match('/^\+?\d{6,8}$/', $buyerPhone)) {
            $this->renderWithFormError('front/checkout', [
                'cartItems' => Cart::itemsWithProducts($this->productModel),
                'cartTotal' => Cart::total(),
                'cartCount' => Cart::count(),
                'old' => $old,
            ], new \InvalidArgumentException('Phone number must be 6-8 digits, optionally starting with +'));
            return;
        }

        // Validate email
        if (!filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
            $this->renderWithFormError('front/checkout', [
                'cartItems' => Cart::itemsWithProducts($this->productModel),
                'cartTotal' => Cart::total(),
                'cartCount' => Cart::count(),
                'old' => $old,
            ], new \InvalidArgumentException('A valid email address is required'));
            return;
        }

        // Create order with cart items
        $cartItemsForEmail = Cart::itemsWithProducts($this->productModel);
        $cartTotalForEmail = Cart::total();

        $orderData = Cart::toOrderData([
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'buyer_address' => $buyerAddress,
            'buyer_email' => $buyerEmail,
        ]);

        $orderId = $this->commandeModel->create($orderData);

        if ($orderId) {
            // attempt to send confirmation email (best-effort)
            try {
                $mailConfig = require __DIR__ . '/config/mail.php';
                $mailer = new Mailer($mailConfig['host'], (int)$mailConfig['port'], $mailConfig['username'], $mailConfig['password'], $mailConfig['from_email'], $mailConfig['from_name']);
                $subject = "Order confirmation #{$orderId}";
                $body = $this->buildOrderConfirmationEmailHtml(
                    $orderId,
                    $buyerName,
                    $buyerPhone,
                    $buyerAddress,
                    $cartItemsForEmail,
                    $cartTotalForEmail
                );
                $plainTextBody = $this->buildOrderConfirmationPlainText(
                    $orderId,
                    $buyerName,
                    $buyerPhone,
                    $buyerAddress,
                    $cartItemsForEmail,
                    $cartTotalForEmail
                );
                $mailer->send($buyerEmail, $subject, $body, true, $plainTextBody);
                $_SESSION['success'] = 'Order placed and confirmation email sent to ' . $buyerEmail . '.';
            } catch (\Throwable $e) {
                // don't block the checkout if email sending fails; log to session for debugging
                $_SESSION['error'] = 'Order placed but failed to send confirmation email: ' . $e->getMessage();
                error_log('[ProjetWeb1] Order confirmation email failed for order ' . $orderId . ': ' . $e->getMessage());
            }

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

    private function buildOrderConfirmationEmailHtml(
        int $orderId,
        string $buyerName,
        string $buyerPhone,
        string $buyerAddress,
        array $items,
        float $totalPrice
    ): string {
        $templateFile = dirname(__DIR__) . '/View/emails/order_confirmation.php';

        if (!is_file($templateFile)) {
            return '<p>Thank you for your order.</p>';
        }

        ob_start();
        require $templateFile;
        $html = ob_get_clean();

        return is_string($html) ? $html : '<p>Thank you for your order.</p>';
    }

    private function buildOrderConfirmationPlainText(
        int $orderId,
        string $buyerName,
        string $buyerPhone,
        string $buyerAddress,
        array $items,
        float $totalPrice
    ): string {
        $lines = [];
        $lines[] = 'Smart Nutrition Order Confirmation';
        $lines[] = 'Order #' . $orderId;
        $lines[] = 'Customer: ' . $buyerName;
        $lines[] = 'Phone: ' . $buyerPhone;
        $lines[] = 'Delivery Address: ' . str_replace(["\r\n", "\r", "\n"], ' | ', $buyerAddress);
        $lines[] = '';
        $lines[] = 'Items:';

        foreach ($items as $item) {
            $lines[] = '- ' . ($item['product_name'] ?? 'Unknown') . ' | Qty: ' . (int) ($item['quantity'] ?? 0) . ' | Unit: ' . number_format((float) ($item['unit_price'] ?? 0), 2) . ' DH | Subtotal: ' . number_format((float) ($item['subtotal'] ?? 0), 2) . ' DH';
        }

        $lines[] = '';
        $lines[] = 'Total: ' . number_format($totalPrice, 2) . ' DH';

        return implode("\n", $lines);
    }
}
