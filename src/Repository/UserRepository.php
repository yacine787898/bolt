<?php

declare(strict_types=1);

final class UserRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return array<string, mixed>|null */
    public function findByEmailAndRole(string $email, string $role): ?array
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare(
                'SELECT id, restaurant_id, name, email, password_hash, role FROM users WHERE email = :email AND role = :role LIMIT 1'
            );
            $statement->execute([
                'email' => $email,
                'role' => $role,
            ]);

            $row = $statement->fetch();
            return $row ?: null;
        } catch (PDOException $exception) {
            return null;
        }
    }
}
