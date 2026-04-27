<?php

namespace App\Controller;

use App\Model\Commande;
use App\Model\Database;
use App\Model\Product;

class CommandeController extends BaseController
{
    private Commande $orders;
    private Product $products;

    public function __construct()
    {
        $connection = Database::connection();
        $this->orders = new Commande($connection);
        $this->products = new Product($connection);
    }

    public function createFront(): void
    {
        $products = $this->products->approved();
        $selectedProduct = (int) ($_GET['product_id'] ?? 0);

        if ($this->isPost()) {
            try {
                $data = $this->validatedOrderPayload();
                $this->orders->create($data);
                $this->redirect('order.list', ['created' => 1]);
            } catch (\InvalidArgumentException $exception) {
                $selectedProduct = (int) ($_POST['product_id'] ?? $selectedProduct);

                $this->renderWithFormError('front/order_create', [
                    'products' => $products,
                    'selectedProduct' => $selectedProduct,
                    'old' => $this->oldOrderInput(),
                ], $exception);
                return;
            }
        }

        $this->render('front/order_create', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'old' => [],
        ]);
    }

    public function frontList(): void
    {
        $orders = $this->orders->all();

        $this->render('front/order_list', [
            'orders' => $orders,
            'created' => (int) ($_GET['created'] ?? 0) === 1,
            'deleted' => (int) ($_GET['deleted'] ?? 0) === 1,
            'updated' => (int) ($_GET['updated'] ?? 0) === 1,
        ]);
    }

    public function editFront(): void
    {
        $this->editCommon('front/order_edit', 'order.list');
    }

    public function deleteFront(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->orders->delete($id);
        }

        $this->redirect('order.list', ['deleted' => 1]);
    }

    public function adminList(): void
    {
        $this->render('back/admin_orders', [
            'orders' => $this->orders->all(),
        ]);
    }

    public function editAdmin(): void
    {
        $this->editCommon('back/admin_order_edit', 'admin.orders');
    }

    public function deleteAdmin(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->orders->delete($id);
        }

        $this->redirect('admin.orders', ['deleted' => 1]);
    }

    private function editCommon(string $view, string $redirectAction): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $order = $this->orders->find($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Order not found.';
            return;
        }

        if ($this->isPost()) {
            try {
                $data = $this->validatedOrderPayload();
                $this->orders->update($id, $data);
                $this->redirect($redirectAction, ['updated' => 1]);
            } catch (\InvalidArgumentException $exception) {
                $fallbackProductId = (int) ($_POST['product_id'] ?? ($order['product_id'] ?? 0));
                $products = $this->products->approved();
                $selectedProduct = null;
                foreach ($products as $candidate) {
                    if ((int) $candidate['id'] === $fallbackProductId) {
                        $selectedProduct = $candidate;
                        break;
                    }
                }

                $this->renderWithFormError($view, [
                    'order' => array_merge($order, $this->oldOrderInput(), ['product_id' => $fallbackProductId]),
                    'products' => $products,
                    'product' => $selectedProduct,
                ], $exception);
                return;
            }
        }

        $this->render($view, [
            'order' => $order,
            'products' => $this->products->approved(),
            'product' => $this->products->find((int) ($order['product_id'] ?? 0)),
        ]);
    }

    private function validatedOrderPayload(): array
    {
        $productRaw = trim((string) ($_POST['product_id'] ?? ''));
        $buyerName = trim((string) ($_POST['buyer_name'] ?? ''));
        $buyerPhone = trim((string) ($_POST['buyer_phone'] ?? ''));
        $buyerAddress = trim((string) ($_POST['buyer_address'] ?? ''));
        $quantityRaw = trim((string) ($_POST['quantity'] ?? ''));

        if ($productRaw === '' || filter_var($productRaw, FILTER_VALIDATE_INT) === false) {
            throw new \InvalidArgumentException('Please choose a valid product.');
        }

        if ($quantityRaw === '' || filter_var($quantityRaw, FILTER_VALIDATE_INT) === false) {
            throw new \InvalidArgumentException('Quantity must be a valid integer.');
        }

        $productId = (int) $productRaw;
        $quantity = (int) $quantityRaw;

        if ($productId <= 0 || $buyerName === '' || $buyerPhone === '' || $buyerAddress === '') {
            throw new \InvalidArgumentException('Missing required order fields.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        if (!preg_match('/^[0-9+\-\s()]{6,20}$/', $buyerPhone)) {
            throw new \InvalidArgumentException('Phone number format is invalid.');
        }

        $product = $this->products->find($productId);
        if ($product === null) {
            throw new \InvalidArgumentException('Selected product was not found.');
        }

        $price = (float) ($product['price'] ?? 0);

        return [
            'product_id' => $productId,
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'buyer_address' => $buyerAddress,
            'quantity' => $quantity,
            'total_price' => $price * $quantity,
        ];
    }

    private function oldOrderInput(): array
    {
        return $this->oldInput(
            ['buyer_name', 'buyer_phone', 'buyer_address', 'quantity'],
            ['quantity' => '1']
        );
    }
}