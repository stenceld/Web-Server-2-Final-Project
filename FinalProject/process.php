<?php

/****************** Database Connection ******************/

$hostname = "localhost";
$username = "u431967787_eESBoidbE_reviewDev"; 
$password = "K4bn#4!jPK8";
$database = "u431967787_eESBoidbE_reviewDatabase";
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn_error);
}

// Going to create a session for user login/signup
// Can also use cookies but I have a better understanding of sessions
session_start();


/****************** Login/Signup ******************/

// Log in existing user
if (isset($_POST['login'])) {
    $loginUsername = htmlspecialchars($_POST['username']);
    $loginPassword = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $loginUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($loginPassword, $user['password'])) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: index.html");
            exit();
        } else {
            header("Location: login.html?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: login.html?error=invalid_credentials");
        exit();
    }
}

// Sign up new user

// Check if passwords match

// Check if username is already taken

// Check if email is already registered

// Hash Password

// Insert new user into database

// Log out user


/****************** Helper Functions for login/Signup ******************/

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

}

/****************** Database Actions ******************/

//***** Create *****//

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