<?php
require_once 'db_config.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $mobile   = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    /* Validation */
    if ($username === '' || $fullname === '' || $email === '' ||
        $mobile === '' || $password === '' || $confirm === '') {
        $errors[] = "All fields are required.";
    }

    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Mobile number must be numeric and exactly 10 digits.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (!$errors) {
        $mysqli = db_connect();

        /* Check username uniqueness */
        $stmt = $mysqli->prepare("SELECT username FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Username is already in use.";
            $stmt->close();
        } else {
            $stmt->close();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $mysqli->prepare("
                INSERT INTO users
                (username, fullname, email, mobile, password_hash)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert->bind_param(
                'sssss',
                $username,
                $fullname,
                $email,
                $mobile,
                $hash
            );

            if ($insert->execute()) {
                // ✅ SUCCESS -> GO TO LOGIN PAGE
                $insert->close();
                $mysqli->close();
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Registration failed. Please try again.";
                $insert->close();
            }
        }
        $mysqli->close();
    }
}

include 'header.php';
?>

<h2>Register</h2>

<?php if ($errors): ?>
    <div class="alert">
        <?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>
    </div>
<?php endif; ?>

<form method="post" action="register.php" novalidate>

    <div class="form-group">
        <label>Username</label>
        <input name="username"
               value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Full Name</label>
        <input name="fullname"
               value="<?php echo htmlspecialchars($_POST['fullname'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input name="email" type="email"
               value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Mobile (10 digits)</label>
        <input name="mobile"
               value="<?php echo htmlspecialchars($_POST['mobile'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Password (minimum 6 characters)</label>
        <input name="password" type="password">
    </div>

    <div class="form-group">
        <label>Confirm Password</label>
        <input name="password_confirm" type="password">
    </div>

    <input type="submit" value="Register">

</form>

<?php include 'footer.php'; ?>
