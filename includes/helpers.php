<?php

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_price(float $price): string
{
    return number_format($price, 2, ',', ' ') . ' €';
}

function format_date_fr(string $date): string
{
    $ts = strtotime($date);
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return $jours[(int)date('w', $ts)] . ' ' . date('j', $ts) . ' ' . $mois[(int)date('n', $ts)];
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die('Requête invalide (jeton CSRF manquant ou expiré). Rechargez la page et réessayez.');
    }
}

/**
 * Inline stroke-icon markup (feather-style), keyed by name. Static/trusted
 * SVG paths only - never interpolate user data into $name's callers.
 */
function icon(string $name): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect>',
        'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M3 9h18"></path><path d="M8 2v4"></path><path d="M16 2v4"></path>',
        'tag' => '<path d="M20.59 13.41 12 22l-9-9 8.59-8.59A2 2 0 0 1 13 4h6a2 2 0 0 1 2 2v6a2 2 0 0 1-.41 1.41Z"></path><circle cx="16.5" cy="7.5" r="1.5"></circle>',
        'clipboard' => '<rect x="6" y="3" width="12" height="18" rx="2"></rect><path d="M9 3v2a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1V3"></path><path d="M9 12h6"></path><path d="M9 16h6"></path>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'building' => '<path d="M5 21V7l8-4 8 4v14"></path><path d="M3 21h18"></path><path d="M9 9h1"></path><path d="M14 9h1"></path><path d="M9 13h1"></path><path d="M14 13h1"></path><path d="M9 17h1"></path><path d="M14 17h1"></path>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"></path>',
    ][$name] ?? '';

    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

/**
 * Renders a whole HTML page. $view is a path relative to /views without extension.
 */
function render(string $view, array $data = []): void
{
    extract($data);
    require BASE_PATH . '/views/layout/header.php';
    require BASE_PATH . '/views/' . $view . '.php';
    require BASE_PATH . '/views/layout/footer.php';
}
