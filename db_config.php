<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      
define('DB_PASS', '');        
define('DB_NAME', 'librarydb'); 

function db_connect() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        die("Database connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}
?>
