<?php
/* XAMPP defaults. Copy db_config.sample.php to db_config.local.php to override. */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Keep connection/query details in the server log, not in the browser.
set_exception_handler(function (Throwable $error) {
    error_log('Library application error: ' . $error->getMessage());
    http_response_code(500);
    exit('The library is temporarily unavailable. Please try again later.');
});

function db_connect() {
    $config = [
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => '',
        'database' => 'librarydb',
        'port' => 3306
    ];
    $localFile = __DIR__ . '/db_config.local.php';
    if (is_file($localFile)) {
        $config = array_replace($config, require $localFile);
    }

    $mysqli = new mysqli($config['host'], $config['user'], $config['password'],
        $config['database'], (int)$config['port']);
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
