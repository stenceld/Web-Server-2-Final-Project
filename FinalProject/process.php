<?php
/** 
 * Sources: 
 * (Ref 1) => Getting checkbox data from post method: https://www.geeksforgeeks.org/php/how-to-get-_post-from-multiple-check-boxes/#
 * (Ref 2) => cURL setup for interacting with The Movie Database API: https://developer.themoviedb.org/reference/getting-started
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

    // Search for movies first
    $stmt = $conn->prepare(
        "SELECT * FROM movieReviews 
        WHERE movieTitle LIKE ?");

    $stmt->bind_param("s", $queryTitle);
    $stmt->execute();
    $result = $stmt->get_result();

    // This commented block below was for outputting through json - meant to be passed through JS to update the html
    
    //$resultArray = $result->fetch_all(MYSQLI_ASSOC);
    //header('Content-Type: application/json'); // For sending json to client
    //echo json_encode($resultArray);
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
        "<br>Rating: " . $row['starRating'] . "/5 stars" .
        "<br>Review:<br>" . $row['reviewText'] .
        "<br><br>------------------------------------<br><br>";
    }
}

//***** Update *****// --- NOT TESTED YET - There is currently no 'update(Movie/Show)Review' button

// Update Movie Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['updateMovieReview']))) {
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
        }

        // Build and execute the SQL UPDATE statment
        $stmt = $conn->prepare(
            "UPDATE movieReviews
            SET genres = ?, starRating = ?, reviewText = ?
            WHERE authorUsername = ?, movieTitle = ?, releaseYear = ?");

        $stmt->bind_param("sdsssi", $genres, $rating, $reviewText, $username, $movieName, $releaseYear);
        $stmt->execute();

        header("Location: index.html"); // Replace with redirect to user's reviews after account are finished
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
}

// Update Show Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['updateShowReview']))) {
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
        }

        // Build and execute the SQL INSERT statment
        $stmt = $conn->prepare(
            "UPDATE tvShowReviews
            SET genres = ?, starRating = ?, reviewText = ?
            WHERE authorUsername = ?, showTitle = ?, season = ?");

        $stmt->bind_param("sdsssi", $genres, $rating, $reviewText, $username, $showName, $season);
        $stmt->execute();

        header("Location: index.html"); // Replace with redirect to user's reviews after account are finished
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
}


//***** Delete *****// --- NOT TESTED YET - There is currently no 'delete(Movie/Show)Review' button

// Delete Movie Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['deleteMovieReview']))) {
    try {
        // Get and sanitize inputs from form
        $username = htmlspecialchars($_POST['username']);
        $movieName = htmlspecialchars($_POST['movieName']);
        $releaseYear = htmlspecialchars($_POST['releaseYear']);

        // Build and execute the SQL DELETE statment
        $stmt = $conn->prepare(
            "DELETE FROM movieReviews
            WHERE authorUsername = ?
            AND movieName = ?
            AND releseYear = ?");

        $stmt->bind_param("ssi", $username, $movieName, $releaseYear);
        $stmt->execute();

        header("Location: index.html"); // Replace with redirect to user's reviews after account are finished
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
    }

// Delete TV Show Review
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['deleteShowReview']))) {
    try {
        // Get and sanitize inputs from form
        $username = htmlspecialchars($_POST['username']);
        $showName = htmlspecialchars($_POST['show-name']);
        $season = htmlspecialchars($_POST['season']);

        // Build and execute the SQL DELETE statment
        $stmt = $conn->prepare(
            "DELETE FROM tvShowReviews
            WHERE authorUsername = ?
            AND showTitle = ?
            AND season = ?");

        $stmt->bind_param("ssi", $username, $showName, $season);
        $stmt->execute();

        header("Location: index.html"); // Replace with redirect to user's reviews after account are finished
        exit;

        } catch (Exception $error) {
            echo "Error: " . $error->getMessage();
        }
    }
?>