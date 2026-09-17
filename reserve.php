<?php
require_once 'db_config.php';
session_start();

if (empty($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$isbn = $_POST['isbn'] ?? '';
$return_url = $_POST['return_url'] ?? 'index.php';

if ($isbn === '') {
    $_SESSION['flash_error'] = "No book selected.";
    header("Location: " . $return_url);
    exit;
}

$mysqli = db_connect();

try {
    $ins = $mysqli->prepare("INSERT INTO reserved_books (username, isbn, reserved_date) VALUES (?, ?, NOW())");
    $ins->bind_param('ss', $_SESSION['username'], $isbn);
    if ($ins->execute()) {
        $_SESSION['flash_success'] = "Book reserved successfully.";
    } else {
        $_SESSION['flash_error'] = "Could not reserve book. It may already be reserved.";
    }
    $ins->close();
} catch (Exception $e) {
    $_SESSION['flash_error'] = "An error occurred while reserving the book.";
}

$mysqli->close();
header("Location: " . $return_url);
exit;
