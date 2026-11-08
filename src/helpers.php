<?php
declare(strict_types=1);


function text_lower(string $value): string {
    return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
}

function text_len(string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string {
    return $GLOBALS['base_path'] ?? '';
}

function url(string $path = '/'): string {
    $base = base_path();
    $path = '/' . ltrim($path, '/');
    if ($path === '//') $path = '/';
    return ($base ?: '') . $path;
}

function asset(string $path): string {
    return url('/assets/' . ltrim($path, '/'));
}

function absolute_url(string $path='/'): string {
    $scheme=request_is_https()?'https':'http';
    $host=strtolower(trim((string)($_SERVER['HTTP_HOST']??'localhost')));
    if(!preg_match('/^[a-z0-9.-]+(?::[0-9]{1,5})?$/i',$host))throw new RuntimeException('Invalid HTTP host.');
    $allowed=array_values(array_filter(array_map('trim',explode(',',strtolower((string)getenv('ATELIER_ALLOWED_HOSTS'))))));
    if($allowed && !in_array($host,$allowed,true))throw new RuntimeException('Untrusted HTTP host.');
    if(!$allowed && strlen((string)getenv('ATELIER_SENDGRID_API_KEY'))>=20)throw new RuntimeException('ATELIER_ALLOWED_HOSTS is required for password reset mail.');
    return $scheme.'://'.$host.url($path);
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    $sent = $_POST['_csrf'] ?? '';
    if (!$sent || !hash_equals(csrf_token(), (string)$sent)) {
        http_response_code(419);
        exit('Session expired. Please refresh and try again.');
    }
}

function money(int|float $amount): string {
    return '₹' . number_format((float)$amount, 0, '.', ',');
}

function products_all(): array {
    return $GLOBALS['products'] ?? [];
}

function products_public(): array {
    return array_values(array_filter(products_all(), fn($p)=>(($p['status'] ?? 'active') === 'active')));
}

function product_by_slug(string $slug): ?array {
    $source = (function_exists('admin_logged_in') && admin_logged_in()) ? products_all() : products_public();
    foreach ($source as $product) {
        if (($product['slug'] ?? '') === $slug) return $product;
    }
    return null;
}

function category_label(string $category): string {
    $map = [
        'wall-shelves' => 'Wall Shelves',
        'ritual-incense' => 'Ritual & Incense',
        'plant-displays' => 'Plant Displays',
        'utility-gifting' => 'Utility & Gifting',
        'outdoor-accents' => 'Outdoor Accents',
    ];
    return $map[$category] ?? ucwords(str_replace('-', ' ', $category));
}

function image_url(string $file): string {
    return asset('images/products/' . $file);
}

function flash(string $key, ?string $value = null): ?string {
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $out = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $out;
}

function redirect(string $path): never {
    header('Location: ' . url($path));
    exit;
}

function cart_lines(): array {
    return function_exists('commerce_cart_lines') ? commerce_cart_lines() : [];
}

function cart_count(): int {
    return function_exists('commerce_cart_count') ? commerce_cart_count() : 0;
}

function cart_total(): int {
    return function_exists('commerce_cart_subtotal') ? commerce_cart_subtotal() : 0;
}

function is_wishlisted(string $slug): bool {
    return in_array($slug, $_SESSION['wishlist'] ?? [], true);
}

function render(string $template, array $data = []): void {
    extract($data, EXTR_SKIP);
    $templateFile = dirname(__DIR__) . '/templates/' . $template . '.php';
    if (!is_file($templateFile)) throw new RuntimeException('Template not found: ' . $template);
    ob_start();
    include $templateFile;
    $content = ob_get_clean();
    include dirname(__DIR__) . '/templates/layout.php';
}
