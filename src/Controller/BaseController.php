<?php

declare(strict_types=1);

abstract class BaseController
{
    protected Database $db;
    protected Config $config;

    public function __construct(Database $db, Config $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    protected function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../../templates/' . $template . '.php';
    }
}
