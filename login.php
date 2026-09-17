<?php
require_once 'db_config.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = "Both username and password are required.";
    } else {
        $mysqli = db_connect();
        $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($hash);
            $stmt->fetch();

            if (password_verify($password, $hash)) {
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }

        $stmt->close();
        $mysqli->close();
    }
}

include 'header.php';
?>

<style>
/* Login page custom layout */

.login-card {
    max-width: 420px;
    margin: 30px auto;
    background: #fffdf7;
    border: 2px solid #d8c9a5;
    border-radius: 6px;
    padding: 24px;
    box-shadow: 0 0 8px rgba(0,0,0,0.08);
}

.login-card h2 {
    text-align: center;
    margin-bottom: 8px;
}

.login-subtitle {
    text-align: center;
    font-size: 14px;
    margin-bottom: 16px;
    color: #555;
}

.login-links {
    margin-top: 12px;
    text-align: center;
}

.login-links a {
    color: #8c744a;
    text-decoration: none;
    font-style: italic;
}

.login-links a:hover {
    text-decoration: underline;
}

</style>

<div class="login-card">

    <h2>Login</h2>

    <p class="login-subtitle">
  
    </p>

    <?php if ($errors): ?>
        <div class="alert">
            <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">

        <div class="form-group">
            <label>Username</label>
            <input name="username"
                   type="text"
                   placeholder="Enter your username"
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input name="password"
                   type="password"
                   placeholder="Enter your password"
                   required>
        </div>

        <input type="submit" value="Enter the Library">

    </form>

    <div class="login-links">

        <p>
            Not Registered? <br>
            <a href="register.php">Register here</a>
        </p>

        <p>
            <a href="home.php">Return to Home Page</a>
        </p>

    </div>

</div>

<?php include 'footer.php'; ?>
