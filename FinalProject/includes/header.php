<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <nav>
        <h1>CineReview</h1>
        <ul>
            <li><a href="/index.html">Home</a></li>
            <li><a href="#">Movies</a></li>
            <li><a href="#">TV Shows</a></li>
            <li><a href="#">Top Rated</a></li>
            <li><a href="/html/movie_review.php">Review a Movie</a></li>
            <li><a href="/html/show_review.php">Review a Show</a></li>
        </ul>
        <section id="login">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <span class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="/process.php?logout=true">
                    <button>Logout</button>
                </a>
            <?php else: ?>
                <a href="/html/login.php">
                    <button>Login</button>
                </a>
            <?php endif; ?>
        </section>
    </nav>
</header>
