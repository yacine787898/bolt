INSERT INTO settings (name, value_int)
VALUES ('distance_max_km', 20);

INSERT INTO restaurants (name, email, menu_url, clicks, is_validated, distance_km)
VALUES
    ('Pizza Nova', 'contact@pizzanova.test', 'https://example.com/pizzanova', 120, 1, 4.5),
    ('Sushi Zen', 'bonjour@sushizen.test', 'https://example.com/sushizen', 76, 1, 18.0),
    ('Burger Time', 'hello@burgertime.test', 'https://example.com/burgertime', 30, 0, 24.0);
