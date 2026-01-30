<?php

declare(strict_types=1);

final class WilayaRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return array<int, array<string, mixed>> */
    public function listAll(): array
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->query('SELECT id, name, domicile_price, stopdesk_price FROM wilayas ORDER BY id');
            return $statement->fetchAll();
        } catch (PDOException $exception) {
            return [];
        }
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare('SELECT id, name, domicile_price, stopdesk_price FROM wilayas WHERE id = :id');
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            return $row ?: null;
        } catch (PDOException $exception) {
            return null;
        }
    }
}
