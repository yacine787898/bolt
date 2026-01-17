<?php

declare(strict_types=1);

final class RestaurantController extends BaseController
{
    public function showLogin(): void
    {
        $this->render('restaurant-login');
    }

    public function login(): void
    {
        $userRepository = new UserRepository($this->db);
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $userRepository->findByEmailAndRole($email, Auth::ROLE_RESTAURANT);
        if ($user && password_verify($password, $user['password_hash'])) {
            Auth::login($user);
            header('Location: /restaurant');
            exit;
        }

        $this->render('restaurant-login', [
            'error' => 'Identifiants invalides.',
            'email' => $email,
        ]);
    }

    public function dashboard(): void
    {
        Auth::requireRole(Auth::ROLE_RESTAURANT, '/restaurant/login');

        $restaurantId = Auth::restaurantId() ?? 0;
        $orderRepository = new OrderRepository($this->db);

        $stats = $orderRepository->statsByRestaurant($restaurantId);
        $orders = $orderRepository->listByRestaurant($restaurantId);

        $this->render('restaurant-dashboard', [
            'stats' => $stats,
            'orders' => $orders,
            'isImpersonating' => Auth::isImpersonating(),
        ]);
    }

    public function updateOrderStatus(): void
    {
        Auth::requireRole(Auth::ROLE_RESTAURANT, '/restaurant/login');

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $restaurantId = Auth::restaurantId() ?? 0;

        if ($orderId > 0) {
            $orderRepository = new OrderRepository($this->db);
            $orderRepository->updateStatus($orderId, $restaurantId, $status);
        }

        header('Location: /restaurant');
        exit;
    }
}
