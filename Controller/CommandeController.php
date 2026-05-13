<?php

namespace App\Controller;

use App\Model\Commande;
use App\Model\Database;
use App\Model\Product;
use App\Service\OrderPdf;

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
        $products = $this->products->approvedSorted('name', 'ASC');
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
        $sortBy = (string) ($_GET['sort'] ?? 'id');
        $sortOrder = (string) ($_GET['order'] ?? 'DESC');
        $query = trim((string) ($_GET['q'] ?? ''));

        $orders = $this->orders->allSorted($sortBy, $sortOrder, $query);

        $this->render('front/order_list', [
            'orders' => $orders,
            'created' => (int) ($_GET['created'] ?? 0) === 1,
            'deleted' => (int) ($_GET['deleted'] ?? 0) === 1,
            'updated' => (int) ($_GET['updated'] ?? 0) === 1,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'query' => $query,
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
        $sortBy = (string) ($_GET['sort'] ?? 'id');
        $sortOrder = (string) ($_GET['order'] ?? 'DESC');
        $query = trim((string) ($_GET['q'] ?? ''));

        $this->render('back/admin_orders', [
            'orders' => $this->orders->allSorted($sortBy, $sortOrder, $query),
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'query' => $query,
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

    public function downloadAdminPdf(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $order = $this->orders->find($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Order not found.';
            return;
        }

        $pdf = new OrderPdf();
        $content = $pdf->build($order);
        $filename = $pdf->filenameForOrder($order);

        // Clean all output buffers to avoid previously buffered HTML/whitespace
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        // Turn off output compression if enabled to avoid corrupting binary stream
        if (function_exists('ini_set')) {
            @ini_set('zlib.output_compression', '0');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Content-Transfer-Encoding: binary');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');

        $stream = fopen('php://temp', 'r+b');
        if ($stream === false) {
            http_response_code(500);
            echo 'Unable to stream PDF.';
            return;
        }

        fwrite($stream, $content);
        rewind($stream);
        fpassthru($stream);
        fclose($stream);

        @flush();
        exit(0);
    }

    // Simple debug page: lists orders with PDF link and server-side md5 checksum
    public function debugPdf(): void
    {
        $orders = $this->orders->allSorted('id', 'DESC', '');

        // Minimal HTML without app layout to avoid extra output
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>PDF Debug</title></head><body>';
        echo '<h2>Orders PDF debug</h2>';
        echo '<p>Click a link to download the PDF. The <strong>X-PDF-MD5</strong> header shows server checksum.</p>';
        echo '<table border="1" cellpadding="6" cellspacing="0"><tr><th>Id</th><th>Buyer</th><th>PDF Link</th></tr>';

        foreach ($orders as $o) {
            $id = (int) ($o['id'] ?? 0);
            $filename = (new \App\Service\OrderPdf())->filenameForOrder($o);
            $md5 = md5((new \App\Service\OrderPdf())->build($o));
            $link = '/ProjetWeb1/Controller/index.php?action=admin.orders.pdf&id=' . $id;
            echo '<tr>';
            echo '<td>' . $id . '</td>';
            echo '<td>' . htmlspecialchars($o['buyer_name'] ?? '(no name)') . '</td>';
            echo '<td><a href="' . $link . '" target="_blank" rel="noopener">Download</a> — MD5: ' . $md5 . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit(0);
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
                $products = $this->products->approvedSorted('name', 'ASC');
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
            'products' => $this->products->approvedSorted('name', 'ASC'),
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

        if ($quantityRaw === '' || !preg_match('/^\d+$/', $quantityRaw)) {
            throw new \InvalidArgumentException('Quantity must be a valid non-negative integer using only digits.');
        }

        $productId = (int) $productRaw;
        $quantity = (int) $quantityRaw;

        if ($productId <= 0 || $buyerName === '' || $buyerPhone === '' || $buyerAddress === '') {
            throw new \InvalidArgumentException('Missing required order fields.');
        }

        // Buyer name: only letters, spaces, hyphens or apostrophes
        if (!preg_match('/^[\p{L}\s\'-]+$/u', $buyerName)) {
            throw new \InvalidArgumentException('Buyer name must contain only letters, spaces, hyphens or apostrophes.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        // Phone: optional leading + and 6-8 digits (no spaces or punctuation)
        if (!preg_match('/^\+?\d{6,8}$/', $buyerPhone)) {
            throw new \InvalidArgumentException('Phone number must be 6-8 digits, optionally starting with +.');
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