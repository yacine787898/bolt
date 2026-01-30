<?php

declare(strict_types=1);

final class AdminController extends BaseController
{
    public function showLogin(): void
    {
        $this->render('admin-login');
    }

    public function login(): void
    {
        $password = (string) ($_POST['password'] ?? '');

        if ($password === $this->config->adminPassword) {
            Auth::loginAdmin();
            header('Location: /admin');
            exit;
        }

        $this->render('admin-login', [
            'error' => 'Mot de passe incorrect.',
        ]);
    }

    public function dashboard(): void
    {
        Auth::requireAdmin('/admin/login');

        $productRepository = new ProductRepository($this->db);
        $orderRepository = new OrderRepository($this->db);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('admin-dashboard', [
            'products' => $productRepository->listAll(),
            'orders' => $orderRepository->listAll(),
            'flash' => $flash,
        ]);
    }

    public function saveProduct(): void
    {
        Auth::requireAdmin('/admin/login');

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || $price <= 0) {
            $_SESSION['flash'] = 'Merci de renseigner un nom et un prix valide.';
            header('Location: /admin');
            exit;
        }

        if ($slug === '') {
            $slug = $this->slugify($name);
        }

        $productRepository = new ProductRepository($this->db);
        $productRepository->save([
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'price' => $price,
            'image_url' => $imageUrl,
            'is_active' => $isActive,
        ]);

        $_SESSION['flash'] = 'Produit enregistré.';
        header('Location: /admin');
        exit;
    }

    public function deleteProduct(): void
    {
        Auth::requireAdmin('/admin/login');

        $productId = (int) ($_POST['id'] ?? 0);
        if ($productId > 0) {
            $productRepository = new ProductRepository($this->db);
            $productRepository->delete($productId);
            $_SESSION['flash'] = 'Produit supprimé.';
        }

        header('Location: /admin');
        exit;
    }

    public function updateOrderStatus(): void
    {
        Auth::requireAdmin('/admin/login');

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        if ($orderId > 0 && $status !== '') {
            $orderRepository = new OrderRepository($this->db);
            $orderRepository->updateStatus($orderId, $status);
            $_SESSION['flash'] = 'Statut de commande mis à jour.';
        }

        header('Location: /admin');
        exit;
    }

    public function sendOrder(): void
    {
        Auth::requireAdmin('/admin/login');

        $orderId = (int) ($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            header('Location: /admin');
            exit;
        }

        $orderRepository = new OrderRepository($this->db);
        $order = $orderRepository->findById($orderId);
        if (!$order) {
            $_SESSION['flash'] = 'Commande introuvable.';
            header('Location: /admin');
            exit;
        }

        $shippingService = new ShippingService($this->config);
        $result = $shippingService->sendOrder($order, $order['items']);

        $responseText = trim((string) ($result['response'] ?? ''));
        $errorText = trim((string) ($result['error'] ?? ''));
        $statusLabel = $result['status'] >= 200 && $result['status'] < 300 ? 'envoyée' : 'erreur';

        $orderRepository->markShipping(
            $orderId,
            $result['tracking'],
            $statusLabel,
            json_encode([
                'status' => $result['status'],
                'response' => $responseText,
                'error' => $errorText,
            ], JSON_UNESCAPED_UNICODE)
        );

        $_SESSION['flash'] = $statusLabel === 'envoyée'
            ? 'Commande envoyée à la livraison.'
            : 'Erreur lors de l\'envoi. Vérifiez la réponse.';

        header('Location: /admin');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /');
        exit;
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value === '' ? 'produit' : $value;
    }
}
