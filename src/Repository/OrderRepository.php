<?php

declare(strict_types=1);

final class OrderRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return array<int, array<string, mixed>> */
    public function listByRestaurant(int $restaurantId): array
    {
        if ($restaurantId <= 0) {
            return [];
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare(
                'SELECT id, customer_name, customer_phone, customer_address, customer_ip, status, created_at FROM orders WHERE restaurant_id = :restaurant_id ORDER BY created_at DESC'
            );
            $statement->execute(['restaurant_id' => $restaurantId]);
            return $statement->fetchAll();
        } catch (PDOException $exception) {
            return [];
        }
    }

    /** @return array<string, int> */
    public function statsByRestaurant(int $restaurantId): array
    {
        $default = [
            'visites_page' => 0,
            'commandes_passees' => 0,
            'commandes_confirmees' => 0,
        ];

        if ($restaurantId <= 0) {
            return $default;
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare(
                "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed FROM orders WHERE restaurant_id = :restaurant_id"
            );
            $statement->execute(['restaurant_id' => $restaurantId]);
            $row = $statement->fetch();

            return [
                'visites_page' => 0,
                'commandes_passees' => (int) ($row['total'] ?? 0),
                'commandes_confirmees' => (int) ($row['confirmed'] ?? 0),
            ];
        } catch (PDOException $exception) {
            return $default;
        }
    }

    public function updateStatus(int $orderId, int $restaurantId, string $status): void
    {
        if ($orderId <= 0 || $restaurantId <= 0) {
            return;
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare(
                'UPDATE orders SET status = :status WHERE id = :id AND restaurant_id = :restaurant_id'
            );
            $statement->execute([
                'status' => $status,
                'id' => $orderId,
                'restaurant_id' => $restaurantId,
            ]);
        } catch (PDOException $exception) {
        }
    }
}
