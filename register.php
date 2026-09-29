<?php
require_once 'db_config.php';
require_once 'functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !valid_csrf_token()) {
    $errors[] = "Your form expired. Please try again.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(input_string($_POST, 'username'));
    $fullname = trim(input_string($_POST, 'fullname'));
    $email    = trim(input_string($_POST, 'email'));
    $mobile   = trim(input_string($_POST, 'mobile'));
    $password = input_string($_POST, 'password');
    $confirm  = input_string($_POST, 'password_confirm');

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

    if (mb_strlen($password, 'UTF-8') < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (mb_strlen($username, 'UTF-8') > 50 || mb_strlen($fullname, 'UTF-8') > 100 || strlen($email) > 100) {
        $errors[] = "Username must be at most 50 characters; full name and email at most 100.";
    }

    // PHP's default bcrypt hashes use at most 72 bytes of the password.
    if (strlen($password) > 72 || strpos($password, "\0") !== false) {
        $errors[] = "Password must be at most 72 bytes and contain no null characters.";
    }
    if (!mb_check_encoding([$username, $fullname, $email, $mobile, $password, $confirm], 'UTF-8')) {
        $errors[] = "Please enter valid UTF-8 text.";
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

            try {
                $insert->execute();
                // SUCCESS -> GO TO LOGIN PAGE
                $insert->close();
                $mysqli->close();
                header("Location: login.php", true, 303);
                exit;
            } catch (mysqli_sql_exception $e) {
                // A concurrent registration may have just taken the username.
                if ($e->getCode() === 1062) {
                    $errors[] = "Username is already in use.";
                } else {
                    error_log('Registration failed: ' . $e->getMessage());
                    $errors[] = "Registration failed. Please try again.";
                }
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
        <?php echo implode("<br>", array_map(function ($error) { return htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); }, $errors)); ?>
    </div>
<?php endif; ?>

<form method="post" action="register.php" novalidate>
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')?>">

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               value="<?php echo htmlspecialchars(input_string($_POST, 'username'), ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname"
               value="<?php echo htmlspecialchars(input_string($_POST, 'fullname'), ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email"
               value="<?php echo htmlspecialchars(input_string($_POST, 'email'), ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="form-group">
        <label for="mobile">Mobile (10 digits)</label>
        <input type="text" id="mobile" name="mobile"
               value="<?php echo htmlspecialchars(input_string($_POST, 'mobile'), ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="form-group">
        <label for="password">Password (minimum 6 characters)</label>
        <input id="password" name="password" type="password">
    </div>

    <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input id="password_confirm" name="password_confirm" type="password">
    </div>

    <input type="submit" value="Register">

</form>

<?php include 'footer.php'; ?>
