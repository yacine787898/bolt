<?php

declare(strict_types=1);

final class SettingsRepository
{
    private Database $db;
    private Config $config;

    public function __construct(Database $db, Config $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function distanceMaxKm(): int
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->query("SELECT value_int FROM settings WHERE name = 'distance_max_km' LIMIT 1");
            $row = $statement ? $statement->fetch() : null;

            if (!$row) {
                return $this->config->distanceMaxKm;
            }

            return (int) $row['value_int'];
        } catch (PDOException $exception) {
            return $this->config->distanceMaxKm;
        }
    }
}
