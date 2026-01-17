<?php

declare(strict_types=1);

final class ClientController extends BaseController
{
    public function index(): void
    {
        $settingsRepository = new SettingsRepository($this->db, $this->config);
        $restaurantRepository = new RestaurantRepository($this->db);

        $distanceMaxKm = $settingsRepository->distanceMaxKm();
        $restaurants = $restaurantRepository->listWithDistance();

        $this->render('client-home', [
            'distanceMaxKm' => $distanceMaxKm,
            'restaurants' => $restaurants,
        ]);
    }
}
