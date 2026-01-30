<?php

declare(strict_types=1);

final class ProductRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return array<int, array<string, mixed>> */
    public function listActive(): array
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->query('SELECT id, name, slug, description, price, image_url FROM products WHERE is_active = 1 ORDER BY created_at DESC');
            return $statement->fetchAll();
        } catch (PDOException $exception) {
            return [];
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function listAll(): array
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->query('SELECT id, name, slug, description, price, image_url, is_active FROM products ORDER BY created_at DESC');
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
            $statement = $connection->prepare('SELECT id, name, slug, description, price, image_url, is_active FROM products WHERE id = :id');
            $statement->execute(['id' => $id]);
            $product = $statement->fetch();
            return $product ?: null;
        } catch (PDOException $exception) {
            return null;
        }
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare('SELECT id, name, slug, description, price, image_url FROM products WHERE slug = :slug AND is_active = 1');
            $statement->execute(['slug' => $slug]);
            $product = $statement->fetch();
            return $product ?: null;
        } catch (PDOException $exception) {
            return null;
        }
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);

        try {
            $connection = $this->db->connection();
            if ($id > 0) {
                $statement = $connection->prepare('UPDATE products SET name = :name, slug = :slug, description = :description, price = :price, image_url = :image_url, is_active = :is_active WHERE id = :id');
                $statement->execute([
                    'id' => $id,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'image_url' => $data['image_url'],
                    'is_active' => $data['is_active'],
                ]);
                return;
            }

            $statement = $connection->prepare('INSERT INTO products (name, slug, description, price, image_url, is_active) VALUES (:name, :slug, :description, :price, :image_url, :is_active)');
            $statement->execute([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'price' => $data['price'],
                'image_url' => $data['image_url'],
                'is_active' => $data['is_active'],
            ]);
        } catch (PDOException $exception) {
        }
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare('DELETE FROM products WHERE id = :id');
            $statement->execute(['id' => $id]);
        } catch (PDOException $exception) {
        }
    }
}
