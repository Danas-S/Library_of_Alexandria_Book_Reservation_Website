<?php include 'header.php'; ?>

<style>
/* Home Page Styling Only */

.hero {
    text-align: center;
    padding: 30px 16px;
    background: linear-gradient(180deg, #fffaf1, #f5f1e6);
    border: 2px solid #d8c9a5;
    border-radius: 6px;
}

.hero h2 {
    font-family: "Cinzel", serif;
    font-size: 32px;
    margin-bottom: 10px;
}

.hero p {
    font-size: 18px;
    max-width: 700px;
    margin: 0 auto 20px auto;
}

.cta-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-buttons a {
    text-decoration: none;
}

.feature-grid {
    margin-top: 40px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
    gap: 20px;
}

.feature {
    background: #fffdf7;
    border: 2px solid #d8c9a5;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 0 6px rgba(0,0,0,0.08);
}

.feature h3 {
    margin-top: 10px;
    font-family: "Cinzel", serif;
}

.feature p {
    font-size: 15px;
}

/* Small icons without external images */
.feature-icon {
    font-size: 34px;
}
</style>

<div class="hero">
    <h2>Library of Alexandria</h2>

    <p>
        Welcome to the modern Library of Alexandria.
        Browse the scrolls of history, science, and imagination and reserve your next read
        with ease.
    </p>

    <div class="cta-buttons">
        <a href="index.php">
            <button>Search the Library</button>
        </a>


        <?php if (empty($_SESSION['username'])): ?>
            <a href="register.php">
                <button>Create an Account</button>
            </a>
            <a href="login.php">
                <button>Login</button>
            </a>
        <?php else: ?>
            <a href="my_reservations.php">
                <button>My Reservations</button>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="feature-grid">

    <div class="feature">
        <div class="feature-icon">📜</div>
        <h3>Discover Books</h3>
        <p>
            Search by title or author, or explore by category.
        </p>
    </div>

    <div class="feature">
        <div class="feature-icon">🏺</div>
        <h3>Reserve Scrolls</h3>
        <p>
            Each book may only be reserved by one reader at a time. Secure your
            book instantly with one click.
        </p>
    </div>

    <div class="feature">
        <div class="feature-icon">📚</div>
        <h3>Manage Collection</h3>
        <p>
            View all books that you've reserved and remove them whenever you are finished.
            Your personal library is always under your control.
        </p>
    </div>

</div>

<?php include 'footer.php'; ?>
