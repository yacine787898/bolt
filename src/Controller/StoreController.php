<?php

declare(strict_types=1);

final class StoreController extends BaseController
{
    public function index(): void
    {
        $productRepository = new ProductRepository($this->db);
        $products = $productRepository->listActive();

        $this->render('store-home', [
            'products' => $products,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function showProduct(array $params): void
    {
        $slug = $params['slug'] ?? '';
        $productRepository = new ProductRepository($this->db);
        $product = $productRepository->findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            echo 'Produit introuvable.';
            return;
        }

        $this->render('product', [
            'product' => $product,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function showCart(): void
    {
        $productRepository = new ProductRepository($this->db);
        $cart = $this->cart();
        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->findById($productId);
            if (!$product) {
                continue;
            }
            $lineTotal = $quantity * (float) $product['price'];
            $subtotal += $lineTotal;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        $this->render('cart', [
            'items' => $items,
            'subtotal' => $subtotal,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function addToCart(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        if ($productId > 0) {
            $cart = $this->cart();
            $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
            $_SESSION['cart'] = $cart;
        }

        header('Location: /cart');
        exit;
    }

    public function updateCart(): void
    {
        $quantities = $_POST['quantities'] ?? [];
        $cart = [];

        foreach ($quantities as $productId => $quantity) {
            $qty = (int) $quantity;
            if ($qty > 0) {
                $cart[(int) $productId] = $qty;
            }
        }

        $_SESSION['cart'] = $cart;
        header('Location: /cart');
        exit;
    }

    public function showCheckout(): void
    {
        $productRepository = new ProductRepository($this->db);
        $wilayaRepository = new WilayaRepository($this->db);
        $cart = $this->cart();

        if (empty($cart)) {
            header('Location: /cart');
            exit;
        }

        $items = [];
        $subtotal = 0.0;
        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->findById($productId);
            if (!$product) {
                continue;
            }
            $lineTotal = $quantity * (float) $product['price'];
            $subtotal += $lineTotal;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        $wilayas = $wilayaRepository->listAll();

        $this->render('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'wilayas' => $wilayas,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function placeOrder(): void
    {
        $cart = $this->cart();
        if (empty($cart)) {
            header('Location: /cart');
            exit;
        }

        $customerName = trim((string) ($_POST['customer_name'] ?? ''));
        $customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
        $customerAddress = trim((string) ($_POST['customer_address'] ?? ''));
        $customerCommune = trim((string) ($_POST['customer_commune'] ?? ''));
        $wilayaId = (int) ($_POST['wilaya_id'] ?? 0);
        $deliveryType = $_POST['delivery_type'] ?? 'domicile';

        if ($customerName === '' || $customerPhone === '' || $customerAddress === '' || $wilayaId <= 0) {
            header('Location: /checkout');
            exit;
        }

        $productRepository = new ProductRepository($this->db);
        $wilayaRepository = new WilayaRepository($this->db);
        $orderRepository = new OrderRepository($this->db);

        $items = [];
        $subtotal = 0.0;
        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->findById($productId);
            if (!$product) {
                continue;
            }
            $unitPrice = (float) $product['price'];
            $subtotal += $unitPrice * $quantity;
            $items[] = [
                'product_id' => (int) $product['id'],
                'name' => $product['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        if (empty($items)) {
            header('Location: /cart');
            exit;
        }

        $wilaya = $wilayaRepository->findById($wilayaId);
        if (!$wilaya) {
            header('Location: /checkout');
            exit;
        }

        $deliveryType = $deliveryType === 'stopdesk' ? 'stopdesk' : 'domicile';
        $shippingPrice = $deliveryType === 'stopdesk'
            ? (float) $wilaya['stopdesk_price']
            : (float) $wilaya['domicile_price'];

        $total = $subtotal + $shippingPrice;

        $orderId = $orderRepository->create([
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_address' => $customerAddress,
            'customer_commune' => $customerCommune,
            'wilaya_id' => $wilayaId,
            'wilaya_name' => $wilaya['name'],
            'delivery_type' => $deliveryType,
            'shipping_price' => $shippingPrice,
            'subtotal' => $subtotal,
            'total' => $total,
        ], $items);

        $_SESSION['cart'] = [];

        header('Location: /order-success?id=' . $orderId);
        exit;
    }

    public function showOrderSuccess(): void
    {
        $orderId = (int) ($_GET['id'] ?? 0);
        $orderRepository = new OrderRepository($this->db);
        $order = $orderRepository->findById($orderId);

        if (!$order) {
            header('Location: /');
            exit;
        }

        $this->render('order-success', [
            'order' => $order,
            'cartCount' => $this->cartCount(),
        ]);
    }

    /** @return array<int, int> */
    private function cart(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        return is_array($cart) ? $cart : [];
    }

    private function cartCount(): int
    {
        return array_sum($this->cart());
    }
}
