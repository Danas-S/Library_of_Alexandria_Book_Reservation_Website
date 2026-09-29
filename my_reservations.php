<?php
require_once 'db_config.php';
require_once 'functions.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$mysqli = db_connect();
$username = $_SESSION['username'];

// Handle removal, then redirect so refreshing cannot repeat the POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    $id = filter_var($_POST['remove_id'] ?? null, FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]);
    if (!valid_csrf_token()) {
        $_SESSION['flash_error'] = "Your form expired. Please try again.";
    } elseif (!$id) {
        $_SESSION['flash_error'] = "Please select a valid reservation.";
    } else {
        try {
            $del = $mysqli->prepare("DELETE FROM reserved_books WHERE id = ? AND username = ?");
            $del->bind_param('is', $id, $username);
            $del->execute();
            if ($del->affected_rows === 1) {
                $_SESSION['flash_success'] = "Reservation removed.";
            } else {
                $_SESSION['flash_error'] = "Reservation not found in your account.";
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Reservation removal failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = "Could not remove reservation. Please try again.";
        } finally {
            if (isset($del)) $del->close();
        }
    }
    $mysqli->close();
    header("Location: my_reservations.php", true, 303);
    exit;
}

// Display each result once, on the GET after removal.
$flash_error   = $_SESSION['flash_error']   ?? '';
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// Fetch reservations
$stmt = $mysqli->prepare("
    SELECT rb.id, rb.isbn, b.title, b.author, rb.reserved_date
    FROM reserved_books rb
    JOIN books b ON rb.isbn = b.isbn
    WHERE rb.username = ?
    ORDER BY rb.reserved_date DESC
");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($id, $isbn, $title, $author, $date);

$reservations = [];
while ($stmt->fetch()) {
    $reservations[] = [
        'id'    => $id,
        'isbn'  => $isbn,
        'title' => $title,
        'author'=> $author,
        'date'  => $date
    ];
}
$stmt->close();

include 'header.php';
?>

<h2>My Reservations</h2>

<?php if ($flash_error): ?>
    <div class="alert"><?php echo htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if ($flash_success): ?>
    <div class="success"><?php echo htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<?php if (empty($reservations)): ?>
    <p>You have no current reservations.</p>
<?php else: ?>
    <table class="table">
        <thead>
        <tr>
            <th>ISBN</th>
            <th>Title / Author</th>
            <th>Reserved Date</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars((string)$r['isbn'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <strong><?php echo htmlspecialchars((string)$r['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                    <span><?php echo htmlspecialchars((string)$r['author'], ENT_QUOTES, 'UTF-8'); ?></span>
                </td>
                <td><?php echo htmlspecialchars((string)$r['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Remove this reservation?');">
                        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')?>">
                        <input type="hidden" name="remove_id" value="<?php echo (int)$r['id']; ?>">
                        <input type="submit" value="Remove">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
$mysqli->close();
include 'footer.php';
?>
