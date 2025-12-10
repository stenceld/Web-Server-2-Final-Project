<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineReview</title>
    <link rel="stylesheet" href="styles/main.css">
</head>

<body>

    <header>
        <nav>
            <h1>CineReview</h1>
            <ul>
                <li><a href="/index.php">Home</a></li>
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

        <form method="get" action="process.php">
            <label>Search Reviews: </label>
            <input type="text" name="searchBar" id="searchBar" placeholder="Enter a title">
            <input type="submit" name="search" id="search" value="Search">
        </form>
    </header>

    <main>

        <?php if (isset($_GET['success'])): ?>
            <p class="success-message">
                <?php
                switch ($_GET['success']) {
                    case 'logged_in':
                        echo "Welcome back, " . htmlspecialchars($_SESSION['username']) . "!";
                        break;
                    case 'logged_out':
                        echo "You have been logged out successfully.";
                        break;
                    case 'review_submitted':
                        echo "Your review has been submitted successfully!";
                        break;
                    default:
                        echo "Success!";
                }
                ?>
            </p>
        <?php endif; ?>

        <p id="test" name="test">If you see this: test failed.</p>

        <section id="FeaturedMovie">
            <h2>Echoes of Tomorrow</h2>
            <p>In a world where memories can be extracted and sold...</p>
            <button>Read Reviews</button>
        </section>

        <section id="PopularMovies">
            <h2>Popular Movies</h2>
            <ul>
                <li><figure><img src="#" alt=""><figcaption>Echoes of Tomorrow</figcaption></figure></li>
                <li><figure><img src="#" alt=""><figcaption>Shadow Protocol</figcaption></figure></li>
                <li><figure><img src="#" alt=""><figcaption>Velocity</figcaption></figure></li>
                <li><figure><img src="#" alt=""><figcaption>The Last Celebration</figcaption></figure></li>
                <li><figure><img src="#" alt=""><figcaption>Reflections</figcaption></figure></li>
            </ul>
        </section>

        <section id="recentReviews">
            <h2>Recent Reviews</h2>

            <article>
                <h3 id="recentHeading1">Sarah Mitchell — Echoes of Tomorrow</h3>
                <p id="recentRating1">5/5</p>
                <p id="recentReview1">An absolute masterpiece! The cinematography is breathtaking...</p>
            </article>

            <article>
                <h3 id="recentHeading2">Sarah Mitchell — Echoes of Tomorrow</h3>
                <p id="recentRating2">5/5</p>
                <p id="recentReview2">An absolute masterpiece! The cinematography is breathtaking...</p>
            </article>

            <article>
                <h3 id="recentHeading3">Sarah Mitchell — Echoes of Tomorrow</h3>
                <p id="recentRating3">5/5</p>
                <p id="recentReview3">An absolute masterpiece! The cinematography is breathtaking...</p>
            </article>

        </section>

    </main>

    <footer>
        <section id="About">
            <h4>CineReview</h4>
            <p>Your trusted source for honest movie reviews.</p>
        </section>

        <section id="FooterRow1">
            <h4>Movies</h4>
            <ul>
                <li><a href="#">Now Playing</a></li>
                <li><a href="#">Coming Soon</a></li>
                <li><a href="#">Top Rated</a></li>
            </ul>
        </section>

        <section id="FooterRow2">
            <h4>Community</h4>
            <ul>
                <li><a href="#">Reviews</a></li>
                <li><a href="#">Discussions</a></li>
                <li><a href="/html/movie_review.php">Write a Review</a></li>
            </ul>
        </section>

        <section id="FooterRow3">
            <h4>Company</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
        </section>
    </footer>

    <script src="/dataQuery.js"></script>
</body>
</html>
