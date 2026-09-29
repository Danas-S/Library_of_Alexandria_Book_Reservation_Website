<?php
require_once 'db_config.php';
require_once 'functions.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/* Build a local destination from search fields, never from a supplied URL. */
$returnUrl = 'index.php?' . http_build_query(search_parameters($_POST));
$isbn = trim(input_string($_POST, 'isbn'));
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

if (!valid_csrf_token()) {
    $_SESSION['flash_error'] = "Your form expired. Please try again.";
} elseif ($isbn === '' || strlen($isbn) > 20) {
    $_SESSION['flash_error'] = "Please select a valid book.";
} else {
    $mysqli = db_connect();
    try {
        // Only existing books can be reserved; the unique ISBN prevents races.
        $ins = $mysqli->prepare("INSERT INTO reserved_books (username, isbn, reserved_date)
            SELECT ?, isbn, NOW() FROM books WHERE isbn = ?");
        $ins->bind_param('ss', $_SESSION['username'], $isbn);
        $ins->execute();
        if ($ins->affected_rows === 1) {
            $_SESSION['flash_success'] = "Book reserved successfully.";
        } else {
            $_SESSION['flash_error'] = "Please select a valid book.";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $_SESSION['flash_error'] = "This book is already reserved.";
        } else {
            error_log('Reservation failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = "Could not reserve the book. Please try again.";
        }
    } finally {
        if (isset($ins)) $ins->close();
        $mysqli->close();
    }
}

header("Location: " . $returnUrl, true, 303);
exit;
