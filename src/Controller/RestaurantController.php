<?php

declare(strict_types=1);

final class RestaurantController extends BaseController
{
    public function dashboard(): void
    {
        $stats = [
            'visites_page' => 0,
            'commandes_passees' => 0,
            'commandes_confirmees' => 0,
        ];

        $this->render('restaurant-dashboard', [
            'stats' => $stats,
        ]);
    }
}
