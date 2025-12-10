
const textField = document.getElementById("test");
textField.innerHTML = "You are seeing the most updated version"; // If this appears at the top of the page, you should be seeing the most updated version

const recentField = document.getElementById("recentReviews");

async function recentReviews() {
    let response = await fetch(`process.php?search=${encodeURIComponent(document.getElementById("search").value)}`); // Uses an empty version of the search query to grab recent reviews

    let reviews = await response.json();

    if (response.status === 200) {
        
        let htmlText = "";
        for (let i = 0; i < 3; i++) {
            let review = reviews[i];

            htmlText += "<article>" +
                        "<h3 id=\"recentHeading" + (i + 1) + "\">" + review.authorUsername + " - " + review.movieTitle + "</h3>" +
                        "<p id=\"recentRating" + (i + 1) + "\">" + review.starRating + "/5</p>" +
                        "<p id=\"recentReview" + (i + 1) + "\">" + review.reviewText + "</p>" +
                        "</article>";
        }
        recentField.innerHTML = htmlText;
    }
}

recentReviews(); // Updates the recent reviews as soon as the home page loads