INSERT INTO settings (name, value_int)
VALUES ('distance_max_km', 20);

INSERT INTO restaurants (name, email, menu_url, clicks, is_validated, distance_km)
VALUES
    ('Pizza Nova', 'contact@pizzanova.test', 'https://example.com/pizzanova', 120, 1, 4.5),
    ('Sushi Zen', 'bonjour@sushizen.test', 'https://example.com/sushizen', 76, 1, 18.0),
    ('Burger Time', 'hello@burgertime.test', 'https://example.com/burgertime', 30, 0, 24.0);

INSERT INTO users (restaurant_id, name, email, password_hash, role)
VALUES
    (NULL, 'Admin', 'admin@bolt.test', '$2y$12$W19n3boP4Bb009ikhiG.N.JIbR2E7VW9udnUONDztzB8GN56V95o6', 'admin'),
    (1, 'Pizza Nova', 'resto@pizzanova.test', '$2y$12$tP/F6xlV6gORLXZrvaXZ8.8UiKByS8C1qwLbRa5DQnXFXdB7.tjuK', 'restaurant');

INSERT INTO orders (restaurant_id, customer_name, customer_phone, customer_address, customer_ip, status)
VALUES
    (1, 'Marie Dupont', '+33 6 12 34 56 78', '12 rue des Lilas, Paris', '203.0.113.10', 'pending'),
    (1, 'Lucas Martin', '+33 6 98 76 54 32', '48 avenue Victor Hugo, Paris', '203.0.113.11', 'confirmed');

INSERT INTO order_items (order_id, item_name, quantity, unit_price, options, supplements)
VALUES
    (1, 'Pizza Margherita', 1, 12.50, 'Taille L', 'Olives'),
    (2, 'Pizza Regina', 2, 14.00, 'Taille M', 'Extra fromage');
