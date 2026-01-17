<?php

declare(strict_types=1);

final class RestaurantRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return array<int, array<string, mixed>> */
    public function listWithDistance(): array
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->query(
                'SELECT id, name, email, menu_url, clicks, distance_km, is_validated FROM restaurants ORDER BY name ASC'
            );

            if (!$statement) {
                return [];
            }

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            return [
                [
                    'id' => 1,
                    'name' => 'Demo Pizza',
                    'email' => 'demo@pizza.test',
                    'menu_url' => '#',
                    'clicks' => 0,
                    'distance_km' => 4.2,
                    'is_validated' => 1,
                ],
                [
                    'id' => 2,
                    'name' => 'Demo Burger',
                    'email' => 'demo@burger.test',
                    'menu_url' => '#',
                    'clicks' => 0,
                    'distance_km' => 22.5,
                    'is_validated' => 0,
                ],
            ];
        }
    }
}
