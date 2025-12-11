
// Print header
<?php include '../includes/header.php' ?>

<?php
/****************** Database Connection ******************/

$hostname = "localhost";
$username = "u431967787_eESBoidbE_reviewDev"; 
$password = "K4bn#4!jPK8";
$database = "u431967787_eESBoidbE_reviewDatabase";
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/****************** Functions ******************/
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Get current username
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/****************** Main Process ******************/

if(isset($_GET['myReviews'])) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        header("Location: /html/login.php?error=login_required");
        exit();
    }

    // Gets the active user's username
    $username = getCurrentUsername();

    // Query to display all of the users movie reviews
        $stmt = $conn->prepare(
            "SELECT * FROM movieReviews 
            WHERE authorUsername = ?");

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<h2>Movies</h2>";

        // Tracked for each update/delete button so session variables can track which movie's button is clicked
        $reviewNum = 1;

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

            echo "<br>Reviewed by you " .
            "<br>Released: " . $row['releaseYear'] .
            "<br>Genres: " . $row['genres'] .
            "<br>Rating: " . $row['starRating'] . "/5 stars" .
            "<br>Review:<br>" . $row['reviewText'] .
            "<input type=\"submit\" id=\"updateMovie" + $reviewNum + "\" value=\"Update\">" .
            "<input type=\"submit\" id=\"deleteMovie" + $reviewNum + "\" value=\"Delete\">" .
            "<br><br>------------------------------------<br><br>";

            $reviewNum++;
        }

    // Query to display all of the users show reviews
        $stmt2 = $conn->prepare(
            "SELECT * FROM tvShowReviews
            WHERE authorUsername = ?");

        $stmt2->bind_param("s", $username);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        echo "<h2>TV Shows</h2>";

        // Tracked for each update/delete button so session variables can track which movie's button is clicked
        $reviewNum = 1;

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

            echo "<br>Reviewed by: you" .
            "<br>Released: " . $row['releaseYear'] .
            "<br>Genres: " . $row['genres'] .
            "<br>Rating: " . $row['starRating'] . "/5 stars" .
            "<br>Review:<br>" . $row['reviewText'] .
            "<input type=\"submit\" id=\"updateShow" + $reviewNum + "\" value=\"Update\">" .
            "<input type=\"submit\" id=\"deleteShow" + $reviewNum + "\" value=\"Delete\">" .
            "<br><br>------------------------------------<br><br>";
        }
}
?>

// Print footer
<?php include '../includes/footer.php' ?>