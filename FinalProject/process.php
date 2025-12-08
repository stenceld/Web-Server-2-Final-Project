<?php
/** 
 * Sources: (Ref 1) => Getting checkbox data from post method: https://www.geeksforgeeks.org/php/how-to-get-_post-from-multiple-check-boxes/#
 * 
*/

/****************** Database Connection ******************/

$hostname = "localhost";
$username = "u431967787_eESBoidbE_reviewDev"; 
$password = "K4bn#4!jPK8";
$database = "u431967787_eESBoidbE_reviewDatabase";
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn_error);
}


/****************** Login/Signup ******************/

// Log in existing user




/****************** Database Actions ******************/

//***** Create *****//

// Write Movie Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['submitMovieReview']))) {
    try {
        // Get and sanitize inputs from form
        $username = htmlspecialchars($_POST['username']);
        $movieName = htmlspecialchars($_POST['movieName']);
        $releaseYear = htmlspecialchars($_POST['releaseYear']);
        $genres = (isset($_POST['genre'])) ? $_POST['genre'] : array(); // Help from geeksforgeeks (Ref 1)
        $rating = $_POST['rating'];
        $reviewText = htmlspecialchars($_POST['review-text']);

        // Genres built as string
        $genreList = "";
        if (count($genres) > 0) {
            foreach($genres as $genre) {
                $genreList .= $genre . ','; // Genres will be saved as a list deliminated by a comma: (comedy,action,drama,)
            }
        } else {
            echo "You must select at least one genre!"; // Replace this with proper error handling
        }

        // Build and execute the SQL INSERT statment
        $stmt = $conn->prepare(
            "INSERT INTO movieReviews
            (authorUsername, movieTitle, releaseYear, genres, starRating, reviewText)
            VALUES
            (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssisds", $username, $movieName, $releaseYear, $genreList, $rating, $reviewText);
        $stmt->execute();

        header("Location: index.html"); // Replace with redirect to user's reviews after account are finished
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
}

// Write Show Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['submitShowReview']))) {
    try {
        // Get and sanitize inputs from form
        $username = htmlspecialchars($_POST['username']);
        $showName = htmlspecialchars($_POST['show-name']);
        $releaseYear = htmlspecialchars($_POST['release-year']);
        $season = htmlspecialchars($_POST['season']);
        $genres = (isset($_POST['genre'])) ? $_POST['genre'] : array(); // Help from geeksforgeeks (Ref 1)
        $rating = $_POST['rating'];
        $reviewText = htmlspecialchars($_POST['review-text']);

        // Genres built as string
        $genreList = "";
        if (count($genres) > 0) {
            foreach($genres as $genre) {
                $genreList .= $genre . ','; // Genres will be saved as a list deliminated by a comma: (comedy,action,drama,)
            }
        } else {
            echo "You must select at least one genre!"; // Replace this with proper error handling
        }

        // Build and execute the SQL INSERT statment
        $stmt = $conn->prepare(
            "INSERT INTO tvShowReviews
            (authorUsername, showTitle, season, releaseYear, genres, starRating, reviewText)
            VALUES
            (?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssiisds", $username, $showName, $season, $releaseYear, $genreList, $rating, $reviewText);
        $stmt->execute();

        header("Location: index.html"); // Replace with redirect to user's reviews after account are finished
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
}




//***** Read *****//

// Search Bar
if (isset($_GET['search'])) {
    // Get title user searched for
    $searchTitle = htmlspecialchars($_GET['searchBar']);
    $queryTitle = "%" . $searchTitle . "%"; // Sets up format for query: %title%

    // If no search value was entered or the field was left blank, all reviews are shown
    if (($searchTitle == "Enter a title") || ($searchTitle == "")) {
        $queryTitle = "%%";
        echo "<h1>All Reviews</h1>";
    } else {
        echo "<h1>Reviews matching \"" . $searchTitle . "\"</h1>";
    }

    // Search for movies first --------- (There is probably a better way to do this in one query but I had issues trying)
    $stmt = $conn->prepare(
        "SELECT * FROM movieReviews 
        WHERE movieTitle LIKE ?");

    $stmt->bind_param("s", $queryTitle);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Movies</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<h3>" . $row['movieTitle'] . "</h3>" .
        "<br>Reviewed by: " . $row['authorUsername'] .
        "<br>Released: " . $row['releaseYear'] .
        "<br>Genres: " . $row['genres'] .
        "<br>Rating: " . $row['starRating'] . "/5 Stars" .
        "<br>Review:<br>" . $row['reviewText'] .
        "<br><br>------------------------------------<br><br>";
    }

    // Search for shows second
    $stmt2 = $conn->prepare(
        "SELECT * FROM tvShowReviews
        WHERE showTitle LIKE ?");
    
    $stmt2->bind_param("s", $queryTitle);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    echo "<h2>TV Shows</h2>";
    while ($row = $result2->fetch_assoc()) {
        echo "<h3>" . $row['showTitle'] . " (Season: " . $row['season'] . ")</h3>" .
        "<br>Reviewed by: " . $row['authorUsername'] .
        "<br>Released: " . $row['releaseYear'] .
        "<br>Genres: " . $row['genres'] .
        "<br>Rating: " . $row['starRating'] . "/5 Stars" .
        "<br>Review:<br>" . $row['reviewText'] .
        "<br><br>------------------------------------<br><br>";
    }
}

//***** Update *****//

//***** Delete *****//

?>