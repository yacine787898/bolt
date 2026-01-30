<?php

declare(strict_types=1);

final class ShippingService
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /** @param array<string, mixed> $order
     *  @param array<int, array<string, mixed>> $items
     *  @return array<string, mixed>
     */
    public function sendOrder(array $order, array $items): array
    {
        $tracking = $order['shipping_tracking'] ?? null;
        if (!$tracking) {
            $tracking = 'MATCHA' . $order['id'];
        }

        $productsLabel = implode(', ', array_map(static function (array $item): string {
            return $item['product_name'] . ' x' . $item['quantity'];
        }, $items));

        $payload = [
            'Colis' => [
                [
                    'Tracking' => $tracking,
                    'TypeLivraison' => $order['delivery_type'] === 'stopdesk' ? '1' : '0',
                    'TypeColis' => '0',
                    'Confrimee' => '1',
                    'Client' => $order['customer_name'],
                    'MobileA' => $order['customer_phone'],
                    'MobileB' => '',
                    'Adresse' => $order['customer_address'],
                    'IDWilaya' => (string) $order['wilaya_id'],
                    'Commune' => $order['customer_commune'] ?: $order['wilaya_name'],
                    'Total' => (string) $order['total'],
                    'Note' => 'Commande matcha en ligne',
                    'TProduit' => $productsLabel,
                    'id_Externe' => (string) $order['id'],
                    'Source' => 'Boutique Matcha',
                ],
            ],
        ];

        $ch = curl_init($this->config->shippingBaseUrl . '/add_colis');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'token: ' . $this->config->shippingToken,
                'key: ' . $this->config->shippingKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $responseBody = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'tracking' => $tracking,
            'status' => $status,
            'error' => $error,
            'response' => $responseBody,
        ];
    }
}
