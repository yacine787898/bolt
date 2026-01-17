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
        $userRepository = new UserRepository($this->db);
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $userRepository->findByEmailAndRole($email, Auth::ROLE_ADMIN);
        if ($user && password_verify($password, $user['password_hash'])) {
            Auth::login($user);
            header('Location: /admin');
            exit;
        }

        $this->render('admin-login', [
            'error' => 'Identifiants invalides.',
            'email' => $email,
        ]);
    }

    public function dashboard(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN, '/admin/login');

        $settingsRepository = new SettingsRepository($this->db, $this->config);
        $restaurantRepository = new RestaurantRepository($this->db);

        $this->render('admin-dashboard', [
            'distanceMaxKm' => $settingsRepository->distanceMaxKm(),
            'restaurants' => $restaurantRepository->listAdmin(50),
            'isImpersonating' => Auth::isImpersonating(),
        ]);
    }

    public function updateDistance(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN, '/admin/login');

        $distance = (int) ($_POST['distance_max_km'] ?? $this->config->distanceMaxKm);
        $settingsRepository = new SettingsRepository($this->db, $this->config);
        $settingsRepository->updateDistanceMaxKm($distance);

        header('Location: /admin');
        exit;
    }

    public function toggleRestaurantValidation(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN, '/admin/login');

        $restaurantId = (int) ($_GET['id'] ?? 0);
        if ($restaurantId > 0) {
            $restaurantRepository = new RestaurantRepository($this->db);
            $restaurantRepository->toggleValidation($restaurantId);
        }

        header('Location: /admin');
        exit;
    }

    public function deleteRestaurant(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN, '/admin/login');

        $restaurantId = (int) ($_GET['id'] ?? 0);
        if ($restaurantId > 0) {
            $restaurantRepository = new RestaurantRepository($this->db);
            $restaurantRepository->delete($restaurantId);
        }

        header('Location: /admin');
        exit;
    }

    public function impersonate(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN, '/admin/login');

        $restaurantId = (int) ($_GET['id'] ?? 0);
        if ($restaurantId > 0 && isset($_SESSION['user_id'])) {
            Auth::startImpersonation($restaurantId, (int) $_SESSION['user_id']);
            header('Location: /restaurant');
            exit;
        }

        header('Location: /admin');
        exit;
    }

    public function stopImpersonation(): void
    {
        Auth::stopImpersonation();
        header('Location: /admin');
        exit;
    }
}
