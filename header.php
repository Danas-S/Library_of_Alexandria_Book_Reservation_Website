<?php
require_once 'functions.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Library Reservation System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts for ancient vibe -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Crimson+Text:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: "Crimson Text", "Georgia", serif; }
        .site-header h1 { font-family: "Cinzel", "Georgia", serif; }
        .logo-scroll {
            width: 32px;
            height: 32px;
            margin-right: 8px;
        }
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .header-title {
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="container header-flex">
        <div class="header-title">
            <!-- Scroll-like emoji -->
            <svg class="logo-scroll" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <rect x="12" y="10" width="40" height="44" rx="4" ry="4" fill="#f5f1e6" stroke="#8c744a" stroke-width="2"/>
                <line x1="18" y1="20" x2="46" y2="20" stroke="#b29762" stroke-width="2"/>
                <line x1="18" y1="28" x2="46" y2="28" stroke="#b29762" stroke-width="2"/>
                <line x1="18" y1="36" x2="46" y2="36" stroke="#b29762" stroke-width="2"/>
                <line x1="18" y1="44" x2="36" y2="44" stroke="#b29762" stroke-width="2"/>
            </svg>
            <h1><a href="home.php">Library of Alexandria</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>

                <!-- Search Link -->
                <li><a href="index.php">Search</a></li>

                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="my_reservations.php">My Reservations</a></li>
                    <li>Logged in as <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong></li>
                    <li>
                        <form method="post" action="logout.php" class="logout-form">
                            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')?>">
                            <button class="logout-button" type="submit">Logout</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main class="container">
