<?php
/** 
 * Sources: 
 * (Ref 1) => Getting checkbox data from post method: https://www.geeksforgeeks.org/php/how-to-get-_post-from-multiple-check-boxes/#
 * (Ref 2) => cURL setup for interacting with The Movie Database API: https://developer.themoviedb.org/reference/getting-started
*/

// Start session at the very top
session_start();

/****************** Session Check for JavaScript ******************/

// Return session status as JSON for the index.html page
if (isset($_GET['checkSession'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'logged_in' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true,
        'username' => isset($_SESSION['username']) ? $_SESSION['username'] : null
    ]);
    exit();
}

/****************** Database Connection ******************/

$hostname = "localhost";
$username = "u431967787_eESBoidbE_reviewDev"; 
$password = "K4bn#4!jPK8";
$database = "u431967787_eESBoidbE_reviewDatabase";
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/****************** API Setup ******************/

// Gets the base for the API interaction url - (Ref 2)
$baseCurl = curl_init();

curl_setopt_array($baseCurl, [
  CURLOPT_URL => "https://api.themoviedb.org/3/configuration",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1MTYxN2M4ZjI0ZGI5Nzc4ZWQ4YjcwYmUzNzE1NmQ1ZiIsIm5iZiI6MTc2NDg5MDQwNS43NzYsInN1YiI6IjY5MzIxNzI1Mzk4MTk0NTExOTM2ZTFjZSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.GF1p4OZ1C2ZoHRSo3g9GKlh3rHozkjQH5u1ou3KhldY",
  ],
]);

$baseResponse = curl_exec($baseCurl);
$baseErr = curl_error($baseCurl);

curl_close($baseCurl);

if ($baseErr) {
    echo "cURL Error #:" . $baseErr;
} else {
    $baseInfo = json_decode($baseResponse, true);
    $baseUrl = $baseInfo['images']['secure_base_url'] . "w185"; // Poster size is hardcoded here
}


/****************** Login/Signup ******************/

// Sign up new user
if (isset($_POST['signup'])) {
    $newUsername = htmlspecialchars($_POST['newUsername']);
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        header("Location: /html/signup.php?error=passwords_not_match");
        exit();
    }

    // Check if username already exists
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param("s", $newUsername);
    $checkUser->execute();
    $userResult = $checkUser->get_result();

    if ($userResult->num_rows > 0) {
        header("Location: /html/signup.php?error=username_exists");
        exit();
    }

    // Hash password and insert new user
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, passwordHash, moviesReviewed, seasonsReviewed, totalReviews, experienced) VALUES (?, ?, 0, 0, 0, 0)");
    $stmt->bind_param("ss", $newUsername, $hashedPassword);
    
    if ($stmt->execute()) {
        header("Location: /html/login.php?success=account_created");
        exit();
    } else {
        header("Location: /html/signup.php?error=signup_failed");
        exit();
    }
}

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
        
        // Verify password using passwordHash column
        if (password_verify($loginPassword, $user['passwordHash'])) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            
            header("Location: /index.html?success=logged_in");
            exit();
        } else {
            header("Location: /html/login.php?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: /html/login.php?error=invalid_credentials");
        exit();
    }
}

// Log out user
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: /index.html?success=logged_out");
    exit();
}

/****************** Helper Functions ******************/

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Get current username
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}




/****************** Database Actions ******************/

//***** Create *****//

// Write Movie Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['submitMovieReview']))) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        header("Location: /html/login.php?error=login_required");
        exit();
    }

    try {
        // Get username from session instead of form
        $username = getCurrentUsername();
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
        }

        // Build and execute the SQL INSERT statment
        $stmt = $conn->prepare(
            "INSERT INTO movieReviews
            (authorUsername, movieTitle, releaseYear, genres, starRating, reviewText)
            VALUES
            (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssisds", $username, $movieName, $releaseYear, $genreList, $rating, $reviewText);
        $stmt->execute();

        header("Location: /index.html?success=review_submitted");
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
}

// Write Show Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['submitShowReview']))) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        header("Location: /html/login.php?error=login_required");
        exit();
    }

    try {
        // Get username from session instead of form
        $username = getCurrentUsername();
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
        }

        // Build and execute the SQL INSERT statment
        $stmt = $conn->prepare(
            "INSERT INTO tvShowReviews
            (authorUsername, showTitle, season, releaseYear, genres, starRating, reviewText)
            VALUES
            (?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssiisds", $username, $showName, $season, $releaseYear, $genreList, $rating, $reviewText);
        $stmt->execute();

        header("Location: /index.html?success=review_submitted");
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
}


//***** Read *****//

// Search Bar
if (isset($_GET['search'])) {
    // Print header
    echo "<body>
        <head>
            <link rel=\"stylesheet\" href=\"styles/main.css\">
        </head>
        
        <header>
        <nav>
            <h1>CineReview</h1>
            <ul>
                <li><a href=\"/index.php\">Home</a></li>
                <li><a href=\"#\">Movies</a></li>
                <li><a href=\"#\">TV Shows</a></li>
                <li><a href=\"#\">Top Rated</a></li>
                <li><a href=\"/html/movie_review.php\">Review a Movie</a></li>
                <li><a href=\"/html/show_review.php\">Review a Show</a></li>
            </ul>
            <section id=\"login\">
                <a href=\"/html/login.php\">
                    <button>Login</button>
                </a>
            </section>
        </nav>
        </header>";
    
    // Get title user searched for
    $searchTitle = htmlspecialchars($_GET['searchBar']);
    $queryTitle = "%" . $searchTitle . "%"; // Sets up format for query: %title%

    // If no search value was entered or the field was left blank, all reviews are shown
    if (($searchTitle == "Enter a title") || ($searchTitle == "")) {
        $queryTitle = "%%";
        //echo "<h1>All Reviews</h1>";
    } else {
        //echo "<h1>Reviews matching \"" . $searchTitle . "\"</h1>";
    }

    // Search for movies first
    $stmt = $conn->prepare(
        "SELECT * FROM movieReviews 
        WHERE movieTitle LIKE ?");

    $stmt->bind_param("s", $queryTitle);
    $stmt->execute();
    $result = $stmt->get_result();

    //$resultArray = $result->fetch_all(MYSQLI_ASSOC);

    //header('Content-Type: application/json'); // For sending json to client
    //echo json_encode($resultArray); ////////////////////////////////////////////
    //echo "<h1>Movies encoded json result</h1><p>" . $moviesJsonResult . "</p>";
    
    //exit; // Exiting here. Nothing below executes for now!


    echo "<h2>Movies</h2>";

    // Outputs data for each movie review found
    while ($row = $result->fetch_assoc()) {
        echo "<h3>" . $row['movieTitle'] . "</h3>";

        $urlTitle = str_replace(" ", "+", $row['movieTitle']); // Replaces spaces in the title with "+" for the url

        // API Request url for movie poster file path
        $endpoint = "https://api.themoviedb.org/3/search/movie";
        $query = "query=" . $urlTitle . "&include_adult=true&language=en-US&primary_release_year=" . $row['releaseYear'] . "&page=1";
        $url = $endpoint . "?" . $query;

        // cURL from TMDB API documentation - (Ref 2)
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1MTYxN2M4ZjI0ZGI5Nzc4ZWQ4YjcwYmUzNzE1NmQ1ZiIsIm5iZiI6MTc2NDg5MDQwNS43NzYsInN1YiI6IjY5MzIxNzI1Mzk4MTk0NTExOTM2ZTFjZSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.GF1p4OZ1C2ZoHRSo3g9GKlh3rHozkjQH5u1ou3KhldY",
            "accept: application/json"
        ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $fullUrl = "";
            echo "Error getting Image url<br>";
            echo "cURL Error #:" . $err;
        } else {
            $info = json_decode($response, true);
            $posterPath = $info['results'][0]['poster_path'];
            $fullUrl = $baseUrl . $posterPath;
            echo "<img src=\"" . $fullUrl . "\" alt=\"Movie poster for " . $row['movieTitle'] . "\">"; // Image is displayed
        }

        ///////////////////////////////////////////

        echo "<br>Reviewed by: " . $row['authorUsername'] .
        "<br>Released: " . $row['releaseYear'] .
        "<br>Genres: " . $row['genres'] .
        "<br>Rating: " . $row['starRating'] . "/5 stars" .
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

    // Outputs data for each show review found
    while ($row = $result2->fetch_assoc()) {
        echo "<h3>" . $row['showTitle'] . " (Season: " . $row['season'] . ")</h3>";

        //////// API Request for show ID (Needed to get correct season image) ////////
        $urlTitle = str_replace(" ", "+", $row['showTitle']); // Replaces spaces in the title with "+" for the url
        $endpoint = "https://api.themoviedb.org/3/search/tv";
        $query = "query=" . $urlTitle . "&include_adult=true&language=en-US&page=1";
        $url = $endpoint . "?" . $query;

        // cURL from TMDB API documentation - (Ref 2)
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1MTYxN2M4ZjI0ZGI5Nzc4ZWQ4YjcwYmUzNzE1NmQ1ZiIsIm5iZiI6MTc2NDg5MDQwNS43NzYsInN1YiI6IjY5MzIxNzI1Mzk4MTk0NTExOTM2ZTFjZSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.GF1p4OZ1C2ZoHRSo3g9GKlh3rHozkjQH5u1ou3KhldY",
            "accept: application/json"
        ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "Error getting ID<br>";
            echo "cURL Error #:" . $err;
        } else {
            $info = json_decode($response, true);
            $showId = $info['results'][0]['id'];
        }


        //////// API Request for show poster ////////
        $url = "https://api.themoviedb.org/3/tv/" . $showId . "/season/" . $row['season'] . "/images";

        // cURL from TMDB API documentation - (Ref 2)
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI1MTYxN2M4ZjI0ZGI5Nzc4ZWQ4YjcwYmUzNzE1NmQ1ZiIsIm5iZiI6MTc2NDg5MDQwNS43NzYsInN1YiI6IjY5MzIxNzI1Mzk4MTk0NTExOTM2ZTFjZSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.GF1p4OZ1C2ZoHRSo3g9GKlh3rHozkjQH5u1ou3KhldY",
            "accept: application/json"
        ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $fullUrl = "";
            echo "Error getting show poster<br>";
            echo "cURL Error #:" . $err;
        } else {
            $info = json_decode($response, true);
            $posterPath = $info['posters'][0]['file_path'];
            $fullUrl = $baseUrl . $posterPath;
            echo "<img src=\"" . $fullUrl . "\" alt=\"TV poster for " . $row['showTitle'] . "\">"; // Image is displayed
        }

        echo "<br>Reviewed by: " . $row['authorUsername'] .
        "<br>Released: " . $row['releaseYear'] .
        "<br>Genres: " . $row['genres'] .
        "<br>Rating: " . $row['starRating'] . "/5 Stars" .
        "<br>Review:<br>" . $row['reviewText'] .
        "<br><br>------------------------------------<br><br>";
    }
    // Print footer
    echo "<footer>
        <section id=\"About\">
            <h4>CineReview</h4>
            <p>Your trusted source for honest movie reviews.</p>
        </section>

        <section id=\"Footer Row 1\">
            <h4>Movies</h4>
            <ul>
                <li><a href=\"#\">Now Playing</a></li>
                <li><a href=\"#\">Coming Soon</a></li>
                <li><a href=\"#\">Top Rated</a></li>
            </ul>
        </section>

        <section id= \"Footer Row 2 \">
            <h4>Community</h4>
            <ul>
                <li><a href=\"#\">Reviews</a></li>
                <li><a href=\"#\">Discussions</a></li>
                <li><a href=\"#\">Write a Review</a></li>
            </ul>
        </section>

        <section id=\"Footer Row 3\">
            <h4>Company</h4>
            <ul>
                <li><a href=\"#\">About Us</a></li>
                <li><a href=\"#\">Contact</a></li>
                <li><a href=\"#\">Privacy Policy</a></li>
            </ul>
        </section>
    </footer>
    </body>";
}

//***** Update *****//

//***** Delete *****//

?>