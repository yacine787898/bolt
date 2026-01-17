<?php

declare(strict_types=1);

final class Database
{
    private Config $config;
    private ?PDO $connection = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $this->config->dbHost,
            $this->config->dbName
        );

        $this->connection = new PDO($dsn, $this->config->dbUser, $this->config->dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $this->connection;
    }
}
