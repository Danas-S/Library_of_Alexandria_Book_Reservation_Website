<?php
/* Shared session setup and small form helpers. */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);
    session_start();
}

function input_string($input, $key) {
    return is_string($input[$key] ?? null) ? $input[$key] : '';
}

/* Only these search fields can be carried into a return URL. */
function search_parameters($input) {
    return [
        'title' => trim(input_string($input, 'title')),
        'author' => trim(input_string($input, 'author')),
        'category' => filter_var($input['category'] ?? 0, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]) ?: 0,
        'hide_reserved' => input_string($input, 'hide_reserved') === '1' ? '1' : '0',
        'page' => filter_var($input['page'] ?? 1, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]) ?: 1
    ];
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function valid_csrf_token() {
    return isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], input_string($_POST, 'csrf_token'));
}
