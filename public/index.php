<?php

declare(strict_types=1);

session_start();

const ADMIN_PASSWORD_HASH = '$2y$12$.i79PM8Ti1C59wf4xzny2OB/Ax5VUNIxk5J.6KBIyARC1NHBLolqe';
const DATA_DIR = __DIR__ . '/../db/store';
const PRODUCTS_FILE = DATA_DIR . '/products.json';
const ORDERS_FILE = DATA_DIR . '/orders.json';
const QUESTIONS_FILE = DATA_DIR . '/questions.json';
const WILAYAS_FILE = DATA_DIR . '/wilayas.json';

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function loadJson(string $path, array $default): array
{
    if (!file_exists($path)) {
        return $default;
    }

    $data = json_decode((string) file_get_contents($path), true);
    if (!is_array($data)) {
        return $default;
    }

    return $data;
}

function saveJson(string $path, array $data): void
{
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
    $value = preg_replace('/\s+/', '-', $value) ?? '';

    return trim($value, '-');
}

function sanitizeText(string $value): string
{
    return trim(strip_tags($value));
}

function getCart(): array
{
    return $_SESSION['cart'] ?? [];
}

function saveCart(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function getFlash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }
    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $value;
}

function setFlash(string $key, string $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function getCartCount(array $cart): int
{
    $count = 0;
    foreach ($cart as $item) {
        $count += (int) ($item['qty'] ?? 0);
    }
    return $count;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = $basePath === '/' ? '' : $basePath;
$assetBasePath = $basePath;

function withBasePath(string $path, string $basePath): string
{
    if ($path === '') {
        return $basePath !== '' ? $basePath : '/';
    }
    if (str_starts_with($path, '/')) {
        return $basePath . $path;
    }
    return $basePath . '/' . $path;
}

$products = loadJson(PRODUCTS_FILE, []);
$orders = loadJson(ORDERS_FILE, []);
$questions = loadJson(QUESTIONS_FILE, []);
$wilayas = loadJson(WILAYAS_FILE, []);

usort($products, static function (array $a, array $b): int {
    return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
});

$cart = getCart();

if ($path === $basePath . '/cart/add' && $method === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $qty = (int) ($_POST['qty'] ?? 0);
    $options = $_POST['options'] ?? [];
    $selectedOptions = [];

    foreach ($options as $name => $value) {
        $selectedOptions[sanitizeText((string) $name)] = sanitizeText((string) $value);
    }

    foreach ($products as $product) {
        if ((int) $product['id'] === $productId) {
            $step = max(1, (int) ($product['min_qty'] ?? 1));
            $qty = max($step, $qty);
            $qty = (int) (ceil($qty / $step) * $step);
            if (isset($cart[$productId])) {
                $cart[$productId]['qty'] += $qty;
                $cart[$productId]['options'] = $selectedOptions;
            } else {
                $cart[$productId] = [
                    'id' => $productId,
                    'qty' => $qty,
                    'options' => $selectedOptions,
                ];
            }
            break;
        }
    }

    saveCart($cart);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

if ($path === $basePath . '/cart/remove' && $method === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    unset($cart[$productId]);
    saveCart($cart);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

if ($path === $basePath . '/checkout' && $method === 'POST') {
    $quantities = $_POST['cart_qty'] ?? [];
    $updatedCart = [];

    foreach ($quantities as $id => $qty) {
        $productId = (int) $id;
        $qtyValue = (int) $qty;
        foreach ($products as $product) {
            if ((int) $product['id'] === $productId) {
                $step = max(1, (int) ($product['min_qty'] ?? 1));
                $qtyValue = max($step, $qtyValue);
                $qtyValue = (int) (ceil($qtyValue / $step) * $step);
                if ($qtyValue > 0) {
                    $updatedCart[$productId] = $cart[$productId] ?? [
                        'id' => $productId,
                        'options' => [],
                    ];
                    $updatedCart[$productId]['qty'] = $qtyValue;
                }
                break;
            }
        }
    }

    $cart = $updatedCart;
    saveCart($cart);

    $firstName = sanitizeText((string) ($_POST['first_name'] ?? ''));
    $lastName = sanitizeText((string) ($_POST['last_name'] ?? ''));
    $phone = sanitizeText((string) ($_POST['phone'] ?? ''));
    $commune = sanitizeText((string) ($_POST['commune'] ?? ''));
    $deliveryType = ($_POST['delivery_type'] ?? 'domicile') === 'domicile' ? 'domicile' : 'stopdesk';
    $wilayaId = (int) ($_POST['wilaya_id'] ?? 0);

    $selectedWilaya = null;
    foreach ($wilayas as $wilaya) {
        if ((int) $wilaya['IDWilaya'] === $wilayaId) {
            $selectedWilaya = $wilaya;
            break;
        }
    }

    if (empty($cart) || !$selectedWilaya) {
        setFlash('error', 'Votre panier est vide ou la wilaya est invalide.');
        header('Location: ' . withBasePath('/', $basePath));
        exit;
    }

    $subtotal = 0;
    $items = [];
    foreach ($cart as $item) {
        foreach ($products as $product) {
            if ((int) $product['id'] === (int) $item['id']) {
                $lineTotal = (int) $product['price'] * (int) $item['qty'];
                $subtotal += $lineTotal;
                $items[] = [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'price' => $product['price'],
                    'qty' => $item['qty'],
                    'options' => $item['options'] ?? [],
                ];
                break;
            }
        }
    }

    $shipping = (int) ($deliveryType === 'domicile' ? $selectedWilaya['Domicile'] : $selectedWilaya['Stopdesk']);
    $orderId = !empty($orders) ? (int) max(array_column($orders, 'id')) + 1 : 1;

    $orders[] = [
        'id' => $orderId,
        'created_at' => date('Y-m-d H:i:s'),
        'status' => 'en attente',
        'items' => $items,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $subtotal + $shipping,
        'customer' => [
            'full_name' => trim($firstName . ' ' . $lastName),
            'phone' => $phone,
            'wilaya' => $selectedWilaya['Wilaya'],
            'commune' => $commune,
        ],
        'delivery' => [
            'type' => $deliveryType,
            'price' => $shipping,
        ],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    ];

    saveJson(ORDERS_FILE, $orders);
    saveCart([]);
    setFlash('success', 'Merci ! Votre commande a été envoyée.');
    header('Location: ' . withBasePath('/', $basePath));
    exit;
}

if ($path === $basePath . '/admin/login' && $method === 'POST') {
    $password = sanitizeText((string) ($_POST['password'] ?? ''));
    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ' . withBasePath('/admin', $basePath));
        exit;
    }

    setFlash('error', 'Mot de passe incorrect.');
    header('Location: ' . withBasePath('/admin', $basePath));
    exit;
}

if ($path === $basePath . '/admin/logout') {
    unset($_SESSION['admin_logged_in']);
    header('Location: ' . withBasePath('/', $basePath));
    exit;
}

if ($path === $basePath . '/admin' && $method === 'POST') {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: ' . withBasePath('/admin', $basePath));
        exit;
    }

    $action = sanitizeText((string) ($_POST['action'] ?? ''));

    if ($action === 'toggle_status') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $statuses = ['en attente', 'confirmée', 'livrée'];
        foreach ($orders as &$order) {
            if ((int) $order['id'] === $orderId) {
                $currentIndex = array_search($order['status'], $statuses, true);
                $nextIndex = $currentIndex === false ? 0 : ($currentIndex + 1) % count($statuses);
                $order['status'] = $statuses[$nextIndex];
                break;
            }
        }
        unset($order);
        saveJson(ORDERS_FILE, $orders);
    }

    if ($action === 'delete_order') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $orders = array_values(array_filter($orders, static fn(array $order): bool => (int) $order['id'] !== $orderId));
        saveJson(ORDERS_FILE, $orders);
    }

    if ($action === 'save_product') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $title = sanitizeText((string) ($_POST['title'] ?? ''));
        $description = sanitizeText((string) ($_POST['description'] ?? ''));
        $price = (int) ($_POST['price'] ?? 0);
        $minQty = max(1, (int) ($_POST['min_qty'] ?? 1));
        $order = (int) ($_POST['order'] ?? 0);
        $imagesText = trim((string) ($_POST['images'] ?? ''));
        $images = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $imagesText))));

        $options = [];
        $optionsText = trim((string) ($_POST['options'] ?? ''));
        foreach (preg_split('/\r\n|\r|\n/', $optionsText) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            [$name, $values] = array_pad(explode(':', $line, 2), 2, '');
            $name = sanitizeText($name);
            $valuesList = array_values(array_filter(array_map('trim', explode(',', $values))));
            if ($name !== '' && $valuesList !== []) {
                $options[] = [
                    'name' => $name,
                    'values' => $valuesList,
                ];
            }
        }

        if ($productId === 0) {
            $productId = !empty($products) ? (int) max(array_column($products, 'id')) + 1 : 1;
            $products[] = [
                'id' => $productId,
                'title' => $title,
                'slug' => slugify($title),
                'description' => $description,
                'price' => $price,
                'min_qty' => $minQty,
                'order' => $order,
                'images' => $images,
                'options' => $options,
            ];
        } else {
            foreach ($products as &$product) {
                if ((int) $product['id'] === $productId) {
                    $product['title'] = $title;
                    $product['slug'] = slugify($title);
                    $product['description'] = $description;
                    $product['price'] = $price;
                    $product['min_qty'] = $minQty;
                    $product['order'] = $order;
                    $product['images'] = $images;
                    $product['options'] = $options;
                    break;
                }
            }
            unset($product);
        }

        saveJson(PRODUCTS_FILE, $products);
    }

    if ($action === 'delete_product') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $products = array_values(array_filter($products, static fn(array $product): bool => (int) $product['id'] !== $productId));
        saveJson(PRODUCTS_FILE, $products);
    }

    if ($action === 'save_question') {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $questionText = sanitizeText((string) ($_POST['question'] ?? ''));
        $answerText = sanitizeText((string) ($_POST['answer'] ?? ''));
        $instagramUrl = sanitizeText((string) ($_POST['instagram_url'] ?? ''));

        if ($questionId === 0) {
            $questionId = !empty($questions) ? (int) max(array_column($questions, 'id')) + 1 : 1;
            $questions[] = [
                'id' => $questionId,
                'question' => $questionText,
                'answer' => $answerText,
                'instagram_url' => $instagramUrl,
            ];
        } else {
            foreach ($questions as &$question) {
                if ((int) $question['id'] === $questionId) {
                    $question['question'] = $questionText;
                    $question['answer'] = $answerText;
                    $question['instagram_url'] = $instagramUrl;
                    break;
                }
            }
            unset($question);
        }

        saveJson(QUESTIONS_FILE, $questions);
    }

    if ($action === 'delete_question') {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $questions = array_values(array_filter($questions, static fn(array $question): bool => (int) $question['id'] !== $questionId));
        saveJson(QUESTIONS_FILE, $questions);
    }

    header('Location: ' . withBasePath('/admin', $basePath));
    exit;
}

function renderHeader(string $title, int $cartCount, string $basePath, string $assetBasePath): void
{
    echo "<!doctype html>\n";
    echo "<html lang=\"fr\">\n";
    echo "<head>\n";
    echo "<meta charset=\"utf-8\">\n";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    echo "<title>" . e($title) . "</title>\n";
    echo "<link rel=\"stylesheet\" href=\"" . e(withBasePath('/assets/style.css', $assetBasePath)) . "\">\n";
    echo "</head>\n";
    echo "<body>\n";
    echo "<header>\n";
    echo "<div class=\"container navbar\">\n";
    echo "<a class=\"logo\" href=\"" . e(withBasePath('/', $basePath)) . "\">meneetocom</a>\n";
    echo "<div class=\"nav-actions\">\n";
    echo "<button class=\"button ghost\" data-open-modal=\"cart\">Panier (<span data-cart-count>" . $cartCount . "</span>)</button>\n";
    echo "<a class=\"button\" href=\"" . e(withBasePath('/#produits', $basePath)) . "\">Voir les produits</a>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</header>\n";
}

function renderFooter(string $basePath, string $assetBasePath): void
{
    echo "<footer>meneetocom - Cartes & Tags NFC dynamiques</footer>\n";
    echo "<script src=\"" . e(withBasePath('/assets/app.js', $assetBasePath)) . "\"></script>\n";
    echo "</body>\n</html>";
}

function renderQuestions(array $questions): void
{
    if (empty($questions)) {
        return;
    }
    echo "<section class=\"questions\">\n";
    echo "<h2 class=\"section-title\">Questions fréquentes</h2>\n";
    foreach ($questions as $question) {
        $video = $question['instagram_url'] ?? '';
        echo "<div class=\"question-item\" data-question>\n";
        echo "<button type=\"button\">" . e($question['question']) . "</button>\n";
        echo "<div class=\"question-answer\">\n";
        echo "<p>" . e($question['answer']) . "</p>\n";
        if ($video !== '') {
            echo "<div class=\"question-video\">\n";
            echo "<iframe src=\"" . e($video) . "\" width=\"100%\" height=\"220\" frameborder=\"0\" allowfullscreen></iframe>\n";
            echo "</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
    }
    echo "</section>\n";
}

function renderAboutUs(): void
{
    echo "<section class=\"about-us\">\n";
    echo "<div class=\"whatsapp-bar\">\n";
    echo "<span>💬 Discuter sur WhatsApp</span>\n";
    echo "</div>\n";
    echo "<p style=\"margin-top:1rem;color:var(--muted)\">\n";
    echo "Nous aidons les professionnels à partager leurs infos avec style : cartes NFC, tags dynamiques, conseils de design et assistance rapide.\n";
    echo "</p>\n";
    echo "<div class=\"socials\">\n";
    echo "<a href=\"https://facebook.com\" aria-label=\"Facebook\">📘</a>\n";
    echo "<a href=\"https://instagram.com\" aria-label=\"Instagram\">📸</a>\n";
    echo "</div>\n";
    echo "</section>\n";
}

function buildCartItems(array $cart, array $products): array
{
    $items = [];
    $subtotal = 0;

    foreach ($cart as $item) {
        foreach ($products as $product) {
            if ((int) $product['id'] === (int) $item['id']) {
                $lineTotal = (int) $product['price'] * (int) $item['qty'];
                $subtotal += $lineTotal;
                $items[] = [
                    'product' => $product,
                    'qty' => $item['qty'],
                    'options' => $item['options'] ?? [],
                    'line_total' => $lineTotal,
                ];
                break;
            }
        }
    }

    return [$items, $subtotal];
}

function renderCartModal(array $cartItems, int $subtotal, array $wilayas, string $basePath): void
{
    $defaultWilaya = $wilayas[0] ?? ['Domicile' => 0, 'Stopdesk' => 0, 'IDWilaya' => 0];
    echo "<div class=\"modal\" data-modal=\"cart\">\n";
    echo "  <div class=\"modal-content\">\n";
    echo "    <button class=\"modal-close\" type=\"button\">✕</button>\n";
    echo "    <h3>Votre panier</h3>\n";
    if (empty($cartItems)) {
        echo "      <p>Votre panier est vide pour le moment.</p>\n";
    } else {
        echo "      <form method=\"post\" action=\"" . e(withBasePath('/checkout', $basePath)) . "\">\n";
        echo "        <table class=\"cart-table\">\n";
        echo "          <thead>\n";
        echo "            <tr>\n";
        echo "              <th>Produit</th>\n";
        echo "              <th>Options</th>\n";
        echo "              <th>Quantité</th>\n";
        echo "              <th>Total</th>\n";
        echo "              <th></th>\n";
        echo "            </tr>\n";
        echo "          </thead>\n";
        echo "          <tbody>\n";
        foreach ($cartItems as $item) {
            echo "            <tr data-cart-item data-price=\"" . e((string) $item['product']['price']) . "\">\n";
            echo "              <td>" . e($item['product']['title']) . "</td>\n";
            echo "              <td>\n";
            if (!empty($item['options'])) {
                echo "                <ul>\n";
                foreach ($item['options'] as $name => $value) {
                    echo "                  <li>" . e($name) . ": " . e($value) . "</li>\n";
                }
                echo "                </ul>\n";
            } else {
                echo "                -\n";
            }
            echo "              </td>\n";
            echo "              <td>\n";
            echo "                <div class=\"quantity-picker\" data-qty-picker data-step=\"" . e((string) $item['product']['min_qty']) . "\">\n";
            echo "                  <button type=\"button\" data-direction=\"minus\">-</button>\n";
            echo "                  <input type=\"number\" name=\"cart_qty[" . e((string) $item['product']['id']) . "]\" value=\"" . e((string) $item['qty']) . "\" min=\"" . e((string) $item['product']['min_qty']) . "\" step=\"" . e((string) $item['product']['min_qty']) . "\">\n";
            echo "                  <button type=\"button\" data-direction=\"plus\">+</button>\n";
            echo "                </div>\n";
            echo "              </td>\n";
            echo "              <td data-line-total>" . e((string) $item['line_total']) . " DZD</td>\n";
            echo "              <td>\n";
            echo "                <button\n";
            echo "                  class=\"button ghost small\"\n";
            echo "                  type=\"submit\"\n";
            echo "                  formaction=\"" . e(withBasePath('/cart/remove', $basePath)) . "\"\n";
            echo "                  formmethod=\"post\"\n";
            echo "                  name=\"product_id\"\n";
            echo "                  value=\"" . e((string) $item['product']['id']) . "\"\n";
            echo "                >\n";
            echo "                  Supprimer\n";
            echo "                </button>\n";
            echo "              </td>\n";
            echo "            </tr>\n";
        }
        echo "          </tbody>\n";
        echo "        </table>\n";
        echo "        <h4 style=\"margin-top:1.5rem;\">Informations client</h4>\n";
        echo "        <div class=\"form-grid\">\n";
        echo "          <div>\n";
        echo "            <label>Nom</label>\n";
        echo "            <input type=\"text\" name=\"last_name\" required>\n";
        echo "          </div>\n";
        echo "          <div>\n";
        echo "            <label>Prénom</label>\n";
        echo "            <input type=\"text\" name=\"first_name\" required>\n";
        echo "          </div>\n";
        echo "          <div>\n";
        echo "            <label>Numéro de téléphone</label>\n";
        echo "            <input type=\"text\" name=\"phone\" required>\n";
        echo "          </div>\n";
        echo "          <div>\n";
        echo "            <label>Wilaya</label>\n";
        echo "            <select name=\"wilaya_id\" data-wilaya-select required>\n";
        foreach ($wilayas as $wilaya) {
            $selected = ($wilaya['IDWilaya'] ?? 0) === ($defaultWilaya['IDWilaya'] ?? 0) ? 'selected' : '';
            echo "              <option value=\"" . e((string) $wilaya['IDWilaya']) . "\"\n";
            echo "                data-domicile=\"" . e((string) $wilaya['Domicile']) . "\"\n";
            echo "                data-stopdesk=\"" . e((string) $wilaya['Stopdesk']) . "\"\n";
            echo "                $selected\n";
            echo "              >\n";
            echo "                " . e($wilaya['Wilaya']) . "\n";
            echo "              </option>\n";
        }
        echo "            </select>\n";
        echo "          </div>\n";
        echo "          <div>\n";
        echo "            <label>Commune</label>\n";
        echo "            <input type=\"text\" name=\"commune\" required>\n";
        echo "          </div>\n";
        echo "          <div>\n";
        echo "            <label>Type de livraison</label>\n";
        echo "            <select name=\"delivery_type\" data-delivery-select>\n";
        echo "              <option value=\"domicile\">À domicile</option>\n";
        echo "              <option value=\"stopdesk\">Au bureau</option>\n";
        echo "            </select>\n";
        echo "          </div>\n";
        echo "        </div>\n";
        echo "        <div class=\"cart-summary\">\n";
        echo "          <span>Sous-total: <span data-cart-subtotal>" . e((string) $subtotal) . " DZD</span></span>\n";
        echo "          <span>Livraison: <span data-cart-shipping>" . e((string) ($defaultWilaya['Domicile'] ?? 0)) . " DZD</span></span>\n";
        echo "          <span>Total: <span data-cart-total>" . e((string) ($subtotal + (int) ($defaultWilaya['Domicile'] ?? 0))) . " DZD</span></span>\n";
        echo "        </div>\n";
        echo "        <button class=\"button\" style=\"margin-top:1.5rem;\" type=\"submit\">Valider la commande</button>\n";
        echo "      </form>\n";
    }
    echo "  </div>\n";
    echo "</div>\n";
}

if ($path === $basePath . '/admin') {
    $cartCount = getCartCount($cart);
    renderHeader('Admin - meneetocom', $cartCount, $basePath, $assetBasePath);

    if (empty($_SESSION['admin_logged_in'])) {
        $error = getFlash('error');
        echo "<main class=\"container\" style=\"padding:2rem 0\">\n";
        if ($error) {
            echo "<div class=\"notice\">" . e($error) . "</div>\n";
        }
        echo "<div class=\"admin-card\" style=\"max-width:420px;margin:0 auto\">\n";
        echo "<h2>Connexion admin</h2>\n";
        echo "<form method=\"post\" action=\"" . e(withBasePath('/admin/login', $basePath)) . "\">\n";
        echo "<label>Mot de passe</label>\n";
        echo "<input type=\"password\" name=\"password\" required>\n";
        echo "<button class=\"button\" type=\"submit\" style=\"margin-top:1rem\">Se connecter</button>\n";
        echo "</form>\n";
        echo "</div>\n";
        echo "</main>\n";
        renderFooter($basePath, $assetBasePath);
        exit;
    }

    $success = getFlash('success');

    echo "<main class=\"container admin-layout\" style=\"padding:2rem 0\">\n";
    if ($success) {
        echo "<div class=\"notice\">" . e($success) . "</div>\n";
    }
    echo "<div class=\"admin-card\">\n";
    echo "<div style=\"display:flex;justify-content:space-between;align-items:center;\">\n";
    echo "<h2>Commandes</h2>\n";
    echo "<a class=\"button ghost small\" href=\"" . e(withBasePath('/admin/logout', $basePath)) . "\">Déconnexion</a>\n";
    echo "</div>\n";
    echo "<table class=\"admin-table\">\n";
    echo "<thead><tr><th>Date</th><th>#</th><th>Statut</th><th>Actions</th></tr></thead>\n";
    echo "<tbody>\n";
    if (empty($orders)) {
        echo "<tr><td colspan=\"4\">Aucune commande.</td></tr>\n";
    } else {
        foreach ($orders as $order) {
            $statusClass = match ($order['status']) {
                'confirmée' => 'status-confirmee',
                'livrée' => 'status-livree',
                default => 'status-en-attente',
            };
            echo "<tr>\n";
            echo "<td>" . e($order['created_at']) . "</td>\n";
            echo "<td>#" . e((string) $order['id']) . "</td>\n";
            echo "<td>\n";
            echo "<form method=\"post\" class=\"inline\">\n";
            echo "<input type=\"hidden\" name=\"action\" value=\"toggle_status\">\n";
            echo "<input type=\"hidden\" name=\"order_id\" value=\"" . e((string) $order['id']) . "\">\n";
            echo "<button class=\"status-pill " . $statusClass . "\" type=\"submit\">" . e($order['status']) . "</button>\n";
            echo "</form>\n";
            echo "</td>\n";
            echo "<td>\n";
            echo "<button class=\"button ghost small\" type=\"button\" data-view-order=\"" . e((string) $order['id']) . "\">Voir</button>\n";
            echo "<form method=\"post\" class=\"inline\" onsubmit=\"return confirm('Supprimer cette commande ?');\">\n";
            echo "<input type=\"hidden\" name=\"action\" value=\"delete_order\">\n";
            echo "<input type=\"hidden\" name=\"order_id\" value=\"" . e((string) $order['id']) . "\">\n";
            echo "<button class=\"button ghost small\" type=\"submit\">Supprimer</button>\n";
            echo "</form>\n";
            echo "</td>\n";
            echo "</tr>\n";
        }
    }
    echo "</tbody>\n";
    echo "</table>\n";
    echo "</div>\n";

    echo "<div class=\"admin-card\">\n";
    echo "<div style=\"display:flex;justify-content:space-between;align-items:center;\">\n";
    echo "<h2>Produits</h2>\n";
    echo "<button class=\"button small\" type=\"button\" data-open-modal=\"product-editor\">Ajouter un produit</button>\n";
    echo "</div>\n";
    echo "<table class=\"admin-table\">\n";
    echo "<thead><tr><th>Titre</th><th>Prix</th><th>Min/commande</th><th>Ordre</th><th>Actions</th></tr></thead>\n";
    echo "<tbody>\n";
    if (empty($products)) {
        echo "<tr><td colspan=\"5\">Aucun produit.</td></tr>\n";
    } else {
        foreach ($products as $product) {
            echo "<tr>\n";
            echo "<td>" . e($product['title']) . "</td>\n";
            echo "<td>" . e((string) $product['price']) . " DZD</td>\n";
            echo "<td>" . e((string) $product['min_qty']) . "</td>\n";
            echo "<td>" . e((string) $product['order']) . "</td>\n";
            echo "<td>\n";
            echo "<button class=\"button ghost small\" type=\"button\" data-edit-product=\"" . e((string) $product['id']) . "\">Modifier</button>\n";
            echo "<form method=\"post\" class=\"inline\" onsubmit=\"return confirm('Supprimer ce produit ?');\">\n";
            echo "<input type=\"hidden\" name=\"action\" value=\"delete_product\">\n";
            echo "<input type=\"hidden\" name=\"product_id\" value=\"" . e((string) $product['id']) . "\">\n";
            echo "<button class=\"button ghost small\" type=\"submit\">Supprimer</button>\n";
            echo "</form>\n";
            echo "</td>\n";
            echo "</tr>\n";
        }
    }
    echo "</tbody>\n";
    echo "</table>\n";
    echo "</div>\n";

    echo "<div class=\"admin-card\">\n";
    echo "<div style=\"display:flex;justify-content:space-between;align-items:center;\">\n";
    echo "<h2>Questions</h2>\n";
    echo "<button class=\"button small\" type=\"button\" data-open-modal=\"question-editor\">Ajouter une question</button>\n";
    echo "</div>\n";
    echo "<table class=\"admin-table\">\n";
    echo "<thead><tr><th>Question</th><th>Réponse</th><th>Actions</th></tr></thead>\n";
    echo "<tbody>\n";
    if (empty($questions)) {
        echo "<tr><td colspan=\"3\">Aucune question.</td></tr>\n";
    } else {
        foreach ($questions as $question) {
            echo "<tr>\n";
            echo "<td>" . e($question['question']) . "</td>\n";
            echo "<td>" . e($question['answer']) . "</td>\n";
            echo "<td>\n";
            echo "<button class=\"button ghost small\" type=\"button\" data-edit-question=\"" . e((string) $question['id']) . "\">Modifier</button>\n";
            echo "<form method=\"post\" class=\"inline\" onsubmit=\"return confirm('Supprimer cette question ?');\">\n";
            echo "<input type=\"hidden\" name=\"action\" value=\"delete_question\">\n";
            echo "<input type=\"hidden\" name=\"question_id\" value=\"" . e((string) $question['id']) . "\">\n";
            echo "<button class=\"button ghost small\" type=\"submit\">Supprimer</button>\n";
            echo "</form>\n";
            echo "</td>\n";
            echo "</tr>\n";
        }
    }
    echo "</tbody>\n";
    echo "</table>\n";
    echo "</div>\n";
    echo "</main>\n";

    echo "<div class=\"modal\" data-modal=\"order-details\">\n";
    echo "<div class=\"modal-content\">\n";
    echo "<button class=\"modal-close\" type=\"button\">✕</button>\n";
    echo "<h3>Détails de la commande</h3>\n";
    echo "<div data-order-details></div>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<div class=\"modal\" data-modal=\"product-editor\">\n";
    echo "<div class=\"modal-content\">\n";
    echo "<button class=\"modal-close\" type=\"button\">✕</button>\n";
    echo "<h3>Produit</h3>\n";
    echo "<form method=\"post\" data-product-form>\n";
    echo "<input type=\"hidden\" name=\"action\" value=\"save_product\">\n";
    echo "<input type=\"hidden\" name=\"product_id\" value=\"0\">\n";
    echo "<div class=\"form-grid\">\n";
    echo "<div>\n<label>Titre</label><input name=\"title\" required></div>\n";
    echo "<div>\n<label>Prix (DZD)</label><input type=\"number\" name=\"price\" required></div>\n";
    echo "<div>\n<label>Quantité minimum</label><input type=\"number\" name=\"min_qty\" value=\"1\" required></div>\n";
    echo "<div>\n<label>Ordre d'affichage</label><input type=\"number\" name=\"order\" value=\"0\"></div>\n";
    echo "</div>\n";
    echo "<label>Description</label><textarea name=\"description\" required></textarea>\n";
    echo "<label>Images (1 URL par ligne)</label><textarea name=\"images\"></textarea>\n";
    echo "<label>Options (format : Nom: valeur1, valeur2)</label><textarea name=\"options\"></textarea>\n";
    echo "<button class=\"button\" type=\"submit\">Enregistrer</button>\n";
    echo "</form>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<div class=\"modal\" data-modal=\"question-editor\">\n";
    echo "<div class=\"modal-content\">\n";
    echo "<button class=\"modal-close\" type=\"button\">✕</button>\n";
    echo "<h3>Question</h3>\n";
    echo "<form method=\"post\" data-question-form>\n";
    echo "<input type=\"hidden\" name=\"action\" value=\"save_question\">\n";
    echo "<input type=\"hidden\" name=\"question_id\" value=\"0\">\n";
    echo "<label>Question</label><input name=\"question\" required>\n";
    echo "<label>Réponse</label><textarea name=\"answer\" required></textarea>\n";
    echo "<label>Vidéo Instagram (URL embed)</label><input name=\"instagram_url\">\n";
    echo "<button class=\"button\" type=\"submit\">Enregistrer</button>\n";
    echo "</form>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<script>\n";
    echo "window.__ORDERS__ = " . json_encode($orders, JSON_UNESCAPED_UNICODE) . ";\n";
    echo "window.__PRODUCTS__ = " . json_encode($products, JSON_UNESCAPED_UNICODE) . ";\n";
    echo "window.__QUESTIONS__ = " . json_encode($questions, JSON_UNESCAPED_UNICODE) . ";\n";
    echo "</script>\n";

    [$cartItems, $subtotal] = buildCartItems($cart, $products);
    renderCartModal($cartItems, $subtotal, $wilayas, $basePath);
    renderFooter($basePath, $assetBasePath);
    exit;
}

if ($path === $basePath . '/product') {
    $slug = sanitizeText((string) ($_GET['slug'] ?? ''));
    $product = null;
    foreach ($products as $item) {
        if ($item['slug'] === $slug) {
            $product = $item;
            break;
        }
    }
    if (!$product) {
        header('Location: ' . withBasePath('/', $basePath));
        exit;
    }

    $cartCount = getCartCount($cart);
    renderHeader($product['title'] . ' - meneetocom', $cartCount, $basePath, $assetBasePath);

    echo "<main class=\"container\" style=\"padding:2rem 0\">\n";
    echo "<a href=\"" . e(withBasePath('/', $basePath)) . "\" class=\"badge\">← Retour</a>\n";
    echo "<div class=\"product-page\">\n";
    echo "<div class=\"gallery\">\n";
    echo "<img src=\"" . e($product['images'][0] ?? 'https://placehold.co/900x600') . "\" alt=\"" . e($product['title']) . "\">\n";
    if (!empty($product['images'])) {
        echo "<div class=\"gallery-thumbs\">\n";
        foreach ($product['images'] as $image) {
            echo "<img src=\"" . e($image) . "\" alt=\"" . e($product['title']) . "\">\n";
        }
        echo "</div>\n";
    }
    echo "</div>\n";

    echo "<div>\n";
    echo "<h1>" . e($product['title']) . "</h1>\n";
    echo "<p style=\"color:var(--muted)\">" . e($product['description']) . "</p>\n";
    echo "<p class=\"price\">" . e((string) $product['price']) . " DZD</p>\n";
    echo "<form method=\"post\" action=\"" . e(withBasePath('/cart/add', $basePath)) . "\">\n";
    echo "<input type=\"hidden\" name=\"product_id\" value=\"" . e((string) $product['id']) . "\">\n";
    if (!empty($product['options'])) {
        echo "<div class=\"options\">\n";
        foreach ($product['options'] as $option) {
            echo "<label>" . e($option['name']) . "</label>\n";
            echo "<select name=\"options[" . e($option['name']) . "]\">\n";
            foreach ($option['values'] as $value) {
                echo "<option value=\"" . e($value) . "\">" . e($value) . "</option>\n";
            }
            echo "</select>\n";
        }
        echo "</div>\n";
    }
    echo "<div class=\"quantity-picker\" data-qty-picker data-step=\"" . e((string) $product['min_qty']) . "\">\n";
    echo "<button type=\"button\" data-direction=\"minus\">-</button>\n";
    echo "<input type=\"number\" name=\"qty\" value=\"" . e((string) $product['min_qty']) . "\" min=\"" . e((string) $product['min_qty']) . "\" step=\"" . e((string) $product['min_qty']) . "\">\n";
    echo "<button type=\"button\" data-direction=\"plus\">+</button>\n";
    echo "</div>\n";
    echo "<div class=\"card-actions\" style=\"margin-top:1rem\">\n";
    echo "<button class=\"button\" type=\"submit\">Ajouter au panier</button>\n";
    echo "<button class=\"button secondary\" type=\"button\" data-open-modal=\"cart\">Commander</button>\n";
    echo "</div>\n";
    echo "</form>\n";
    echo "</div>\n";
    echo "</div>\n";

    renderQuestions($questions);
    renderAboutUs();

    echo "</main>\n";
    [$cartItems, $subtotal] = buildCartItems($cart, $products);
    renderCartModal($cartItems, $subtotal, $wilayas, $basePath);
    renderFooter($basePath, $assetBasePath);
    exit;
}

$cartCount = getCartCount($cart);
renderHeader('meneetocom - Boutique NFC', $cartCount, $basePath, $assetBasePath);

$flashSuccess = getFlash('success');
$flashError = getFlash('error');

[$cartItems, $subtotal] = buildCartItems($cart, $products);

usort($products, static function (array $a, array $b): int {
    return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
});

?>

<main class="container">
  <section class="hero">
    <h1>Cartes & Tags NFC dynamiques pour booster votre présence.</h1>
    <p>
      meneetocom propose des supports NFC simples à programmer, modifiables à distance et parfaits pour partager vos liens, réseaux sociaux ou menus en un geste.
    </p>
    <div class="hero-card">
      <div>
        <strong>Programmation rapide</strong>
        <p>Un lien, un tap NFC, tout est prêt.</p>
      </div>
      <div>
        <strong>Gestion à distance</strong>
        <p>Modifiez vos informations sans réimpression.</p>
      </div>
      <div>
        <strong>Compatible mobile</strong>
        <p>Expérience fluide sur iOS et Android.</p>
      </div>
    </div>
  </section>

  <?php if ($flashSuccess): ?>
    <div class="notice"><?php echo e($flashSuccess); ?></div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="notice"><?php echo e($flashError); ?></div>
  <?php endif; ?>

  <section id="produits">
    <h2 class="section-title">Nos produits</h2>
    <div class="products-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <img src="<?php echo e($product['images'][0] ?? 'https://placehold.co/900x600'); ?>" alt="<?php echo e($product['title']); ?>">
          <div class="content">
            <span class="badge">Min <?php echo e((string) $product['min_qty']); ?> unités</span>
            <h3><?php echo e($product['title']); ?></h3>
            <p><?php echo e($product['description']); ?></p>
            <div class="price"><?php echo e((string) $product['price']); ?> DZD</div>
            <div class="card-actions">
                <a class="button ghost" href="<?php echo e(withBasePath('/product', $basePath)); ?>?slug=<?php echo e($product['slug']); ?>">Voir</a>
              <form method="post" action="<?php echo e(withBasePath('/cart/add', $basePath)); ?>">
                <input type="hidden" name="product_id" value="<?php echo e((string) $product['id']); ?>">
                <input type="hidden" name="qty" value="<?php echo e((string) $product['min_qty']); ?>">
                <button class="button" type="submit">Ajouter</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php renderQuestions($questions); ?>
  <?php renderAboutUs(); ?>
</main>

<?php renderCartModal($cartItems, $subtotal, $wilayas, $basePath); ?>

<?php renderFooter($basePath, $assetBasePath); ?>
