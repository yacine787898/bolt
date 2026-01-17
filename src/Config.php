<?php

declare(strict_types=1);

final class Config
{
    public string $dbHost;
    public string $dbName;
    public string $dbUser;
    public string $dbPassword;
    public int $distanceMaxKm;

    public function __construct(
        string $dbHost,
        string $dbName,
        string $dbUser,
        string $dbPassword,
        int $distanceMaxKm
    ) {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->distanceMaxKm = $distanceMaxKm;
    }

    public static function fromEnv(): self
    {
        $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
        $dbName = getenv('DB_NAME') ?: 'bolt';
        $dbUser = getenv('DB_USER') ?: 'bolt';
        $dbPassword = getenv('DB_PASSWORD') ?: 'secret';
        $distanceMaxKm = (int) (getenv('DISTANCE_MAX_KM') ?: 20);

        return new self($dbHost, $dbName, $dbUser, $dbPassword, $distanceMaxKm);
    }
}
