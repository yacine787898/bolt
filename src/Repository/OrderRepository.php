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
    public function listAll(): array
    {
        try {
            $connection = $this->db->connection();
            $statement = $connection->query('SELECT * FROM orders ORDER BY created_at DESC');
            $orders = $statement->fetchAll();
            if (empty($orders)) {
                return [];
            }

            $orderIds = array_map(static fn ($order) => (int) $order['id'], $orders);
            $items = $this->itemsForOrders($orderIds);

            foreach ($orders as &$order) {
                $orderId = (int) $order['id'];
                $order['items'] = $items[$orderId] ?? [];
            }

            return $orders;
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
            $statement = $connection->prepare('SELECT * FROM orders WHERE id = :id');
            $statement->execute(['id' => $id]);
            $order = $statement->fetch();
            if (!$order) {
                return null;
            }

            $items = $this->itemsForOrders([(int) $order['id']]);
            $order['items'] = $items[(int) $order['id']] ?? [];

            return $order;
        } catch (PDOException $exception) {
            return null;
        }
    }

    /** @param array<string, mixed> $orderData
     *  @param array<int, array<string, mixed>> $items
     */
    public function create(array $orderData, array $items): int
    {
        try {
            $connection = $this->db->connection();
            $connection->beginTransaction();

            $statement = $connection->prepare(
                'INSERT INTO orders (customer_name, customer_phone, customer_address, customer_commune, wilaya_id, wilaya_name, delivery_type, shipping_price, subtotal, total, status) VALUES (:customer_name, :customer_phone, :customer_address, :customer_commune, :wilaya_id, :wilaya_name, :delivery_type, :shipping_price, :subtotal, :total, :status)'
            );
            $statement->execute([
                'customer_name' => $orderData['customer_name'],
                'customer_phone' => $orderData['customer_phone'],
                'customer_address' => $orderData['customer_address'],
                'customer_commune' => $orderData['customer_commune'],
                'wilaya_id' => $orderData['wilaya_id'],
                'wilaya_name' => $orderData['wilaya_name'],
                'delivery_type' => $orderData['delivery_type'],
                'shipping_price' => $orderData['shipping_price'],
                'subtotal' => $orderData['subtotal'],
                'total' => $orderData['total'],
                'status' => 'pending',
            ]);

            $orderId = (int) $connection->lastInsertId();

            $itemStatement = $connection->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price)'
            );

            foreach ($items as $item) {
                $itemStatement->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $connection->commit();
            return $orderId;
        } catch (PDOException $exception) {
            $connection = $this->db->connection();
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            return 0;
        }
    }

    public function updateStatus(int $orderId, string $status): void
    {
        if ($orderId <= 0) {
            return;
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $statement->execute([
                'id' => $orderId,
                'status' => $status,
            ]);
        } catch (PDOException $exception) {
        }
    }

    public function markShipping(int $orderId, string $tracking, string $shippingStatus, string $response): void
    {
        if ($orderId <= 0) {
            return;
        }

        try {
            $connection = $this->db->connection();
            $statement = $connection->prepare('UPDATE orders SET shipping_tracking = :tracking, shipping_status = :shipping_status, shipping_response = :shipping_response, status = :status WHERE id = :id');
            $statement->execute([
                'id' => $orderId,
                'tracking' => $tracking,
                'shipping_status' => $shippingStatus,
                'shipping_response' => $response,
                'status' => 'sent',
            ]);
        } catch (PDOException $exception) {
        }
    }

    /** @param array<int, int> $orderIds
     *  @return array<int, array<int, array<string, mixed>>> */
    private function itemsForOrders(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }

        try {
            $connection = $this->db->connection();
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $statement = $connection->prepare('SELECT order_id, product_name, quantity, unit_price FROM order_items WHERE order_id IN (' . $placeholders . ')');
            $statement->execute($orderIds);
            $items = [];
            foreach ($statement->fetchAll() as $row) {
                $orderId = (int) $row['order_id'];
                $items[$orderId][] = $row;
            }

            return $items;
        } catch (PDOException $exception) {
            return [];
        }
    }
}
