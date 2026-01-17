<?php

declare(strict_types=1);

final class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $settingsRepository = new SettingsRepository($this->db, $this->config);

        $this->render('admin-dashboard', [
            'distanceMaxKm' => $settingsRepository->distanceMaxKm(),
        ]);
    }
}
