<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/movie_show.css">
    <title>Show - CineReview</title>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main>

        <section id="Movie-Show Display">
            <!-- Will be filled dynamically with movie/show details -->
        </section>

        <section id="Review Form">
            <h2>Submit a Show Review</h2>

            <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
                <p class="error-message">You must be <a href="login.php">logged in</a> to submit a review.</p>
            <?php endif; ?>

            <form method="post" action="../process.php">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" 
                           readonly required>
                </div>

                <div>
                    <label for="show-name">Show Name:</label>
                    <input type="text" id="show-name" name="show-name" required>
                </div>

                <div>
                    <label for="release-year">Release Year:</label>
                    <input type="text" id="release-year" name="release-year" required>
                </div>

                <div>
                    <label for="season">Season:</label>
                    <select id="season" name="season">
                        <option value="">Select Season</option>
                        <option value="1">Season 1</option>
                        <option value="2">Season 2</option>
                        <option value="3">Season 3</option>
                        <option value="4">Season 4</option>
                        <option value="5">Season 5</option>
                        <option value="6">Season 6</option>
                        <option value="7">Season 7</option>
                        <option value="8">Season 8</option>
                        <option value="9">Season 9</option>
                        <option value="10">Season 10</option>
                    </select>
                </div>

                <div>
                    <legend>Genre:</legend>
                    <div>
                        <input type="checkbox" id="action" name="genre[]" value="action">
                        <label for="action">Action</label>
                    </div>
                    <div>
                        <input type="checkbox" id="drama" name="genre[]" value="drama">
                        <label for="drama">Drama</label>
                    </div>
                    <div>
                        <input type="checkbox" id="comedy" name="genre[]" value="comedy">
                        <label for="comedy">Comedy</label>
                    </div>
                    <div>
                        <input type="checkbox" id="thriller" name="genre[]" value="thriller">
                        <label for="thriller">Thriller</label>
                    </div>
                    <div>
                        <input type="checkbox" id="sci-fi" name="genre[]" value="sci-fi">
                        <label for="sci-fi">Sci-Fi</label>
                    </div>
                </div>

                <div>
                    <legend>Rating:</legend>
                    <div>
                        <input type="radio" id="star1" name="rating" value="1" required>
                        <label for="star1">1 Star</label>
                    </div>
                    <div>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">2 Stars</label>
                    </div>
                    <div>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">3 Stars</label>
                    </div>
                    <div>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">4 Stars</label>
                    </div>
                    <div>
                        <input type="radio" id="star5" name="rating" value="5">
                        <label for="star5">5 Stars</label>
                    </div>
                </div>

                <div>
                    <label for="review-text">Review:</label>
                    <textarea id="review-text" name="review-text" rows="8" cols="50" required></textarea>
                </div>

                <button type="submit" id="submitShowReview" name="submitShowReview"
                        <?php echo (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) ? 'disabled' : ''; ?>>
                    Submit Review
                </button>
            </form>
        </section>

    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>
