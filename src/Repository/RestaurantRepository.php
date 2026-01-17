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

    /** @return array<int, array<string, mixed>> */
    public function listAdmin(int $limit): array
    {
        $safeLimit = max(1, min($limit, 200));

        try {
            $connection = $this->db->connection();
            $statement = $connection->query(
                'SELECT id, name, email, menu_url, clicks, is_validated FROM restaurants ORDER BY id DESC LIMIT ' . $safeLimit
            );

            if (!$statement) {
                return [];
            }

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            return [];
        }
    }

    public function toggleValidation(int $restaurantId): void
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare(
                'UPDATE restaurants SET is_validated = IF(is_validated = 1, 0, 1) WHERE id = :id'
            );
            $statement->execute(['id' => $restaurantId]);
        } catch (PDOException $exception) {
        }
    }

    public function delete(int $restaurantId): void
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare('DELETE FROM restaurants WHERE id = :id');
            $statement->execute(['id' => $restaurantId]);
        } catch (PDOException $exception) {
        }
    }
}
