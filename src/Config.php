<?php

declare(strict_types=1);

final class Config
{
    public string $dbHost;
    public string $dbName;
    public string $dbUser;
    public string $dbPassword;
    public string $adminPassword;
    public string $shippingBaseUrl;
    public string $shippingToken;
    public string $shippingKey;

    public function __construct(
        string $dbHost,
        string $dbName,
        string $dbUser,
        string $dbPassword,
        string $adminPassword,
        string $shippingBaseUrl,
        string $shippingToken,
        string $shippingKey
    ) {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->adminPassword = $adminPassword;
        $this->shippingBaseUrl = $shippingBaseUrl;
        $this->shippingToken = $shippingToken;
        $this->shippingKey = $shippingKey;
    }

    public static function fromEnv(): self
    {
        $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
        $dbName = getenv('DB_NAME') ?: 'bolt';
        $dbUser = getenv('DB_USER') ?: 'bolt';
        $dbPassword = getenv('DB_PASSWORD') ?: 'secret';
        $adminPassword = getenv('ADMIN_PASSWORD') ?: 'admin787898';
        $shippingBaseUrl = getenv('SHIPPING_BASE_URL') ?: 'https://procolis.com/api_v1';
        $shippingToken = getenv('SHIPPING_TOKEN') ?: '8171dc6320093f6f7ebfad713814391bfba6fca1e93ed16fbd6380f5574cd16c';
        $shippingKey = getenv('SHIPPING_KEY') ?: '65ba9f3c23da4f7793fc47a7f305fb29';

        return new self(
            $dbHost,
            $dbName,
            $dbUser,
            $dbPassword,
            $adminPassword,
            $shippingBaseUrl,
            $shippingToken,
            $shippingKey
        );
    }
}
