<?php 
session_start();

// Database connection
$hostname = "localhost";
$username = "u431967787_eESBoidbE_reviewDev"; 
$password = "K4bn#4!jPK8";
$database = "u431967787_eESBoidbE_reviewDatabase";
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get parameters
$type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : '';
$title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';
$season = isset($_GET['season']) ? (int)$_GET['season'] : null;

$reviews = [];
$pageTitle = "";

// Fetch reviews based on type
if ($type === 'movie' && !empty($title)) {
    $stmt = $conn->prepare("SELECT * FROM movieReviews WHERE movieTitle = ? ORDER BY starRating DESC");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    $pageTitle = $title;
} elseif ($type === 'show' && !empty($title)) {
    if ($season) {
        $stmt = $conn->prepare("SELECT * FROM tvShowReviews WHERE showTitle = ? AND season = ? ORDER BY starRating DESC");
        $stmt->bind_param("si", $title, $season);
        $pageTitle = $title . " - Season " . $season;
    } else {
        $stmt = $conn->prepare("SELECT * FROM tvShowReviews WHERE showTitle = ? ORDER BY season, starRating DESC");
        $stmt->bind_param("s", $title);
        $pageTitle = $title . " (All Seasons)";
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
}

// Calculate average rating
$avgRating = 0;
if (count($reviews) > 0) {
    $totalRating = 0;
    foreach ($reviews as $review) {
        $totalRating += $review['starRating'];
    }
    $avgRating = round($totalRating / count($reviews), 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/viewReviews.css">
    <title><?php echo $pageTitle ? $pageTitle . " - Reviews" : "Reviews"; ?> - CineReview</title>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main>

        <section id="titleHeader">
            <?php if (!empty($title)): ?>
                <h1><?php echo $pageTitle; ?></h1>
                <div class="meta">
                    <span class="type"><?php echo ucfirst($type); ?></span>
                    <span class="rating">★ <?php echo $avgRating; ?>/5</span>
                    <span class="count"><?php echo count($reviews); ?> Review<?php echo count($reviews) !== 1 ? 's' : ''; ?></span>
                </div>
            <?php else: ?>
                <h1>Reviews</h1>
                <p>Select a movie or show to view its reviews.</p>
            <?php endif; ?>
        </section>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'review_submitted'): ?>
            <p class="success-message">Your review has been submitted successfully!</p>
        <?php endif; ?>

        <section id="reviewsList">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <article class="review-card">
                        <div class="review-header">
                            <div class="user-info">
                                <span class="username"><?php echo htmlspecialchars($review['authorUsername']); ?></span>
                                <?php if ($type === 'show' && isset($review['season'])): ?>
                                    <span class="season">Season <?php echo $review['season']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="rating-badge">
                                ★ <?php echo $review['starRating']; ?>
                            </div>
                        </div>
                        
                        <div class="review-meta">
                            <span class="year">Released: <?php echo $review['releaseYear']; ?></span>
                            <?php if (!empty($review['genres'])): ?>
                                <span class="genres"><?php echo htmlspecialchars(rtrim($review['genres'], ',')); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="review-text"><?php echo htmlspecialchars($review['reviewText']); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php elseif (!empty($title)): ?>
                <div class="no-reviews">
                    <h3>No reviews yet</h3>
                    <p>Be the first to review this <?php echo $type; ?>!</p>
                    <?php if ($type === 'movie'): ?>
                        <a href="movie_review.php" class="write-review-btn">Write a Review</a>
                    <?php else: ?>
                        <a href="show_review.php" class="write-review-btn">Write a Review</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="no-reviews">
                    <h3>No title selected</h3>
                    <p>Use the search bar to find a movie or show.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if (count($reviews) > 0): ?>
            <section id="writeReview">
                <?php if ($type === 'movie'): ?>
                    <a href="movie_review.php" class="write-review-btn">Write Your Own Review</a>
                <?php else: ?>
                    <a href="show_review.php" class="write-review-btn">Write Your Own Review</a>
                <?php endif; ?>
            </section>
        <?php endif; ?>

    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>
