<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/login.css">
    <title>Sign Up - CineReview</title>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main>

        <section id="Login Form">
            <h2>Sign Up</h2>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-message">
                    <?php
                    switch ($_GET['error']) {
                        case 'passwords_not_match':
                            echo "Passwords do not match.";
                            break;
                        case 'username_exists':
                            echo "Username already taken.";
                            break;
                        case 'signup_failed':
                            echo "Signup failed. Please try again.";
                            break;
                        default:
                            echo "An error occurred. Please try again.";
                    }
                    ?>
                </p>
            <?php endif; ?>

            <form method="post" action="../process.php">
                <label for="newUsername">Username:</label>
                <input type="text" id="newUsername" name="newUsername" required>
                
                <label for="newPassword">Password:</label>
                <input type="password" id="newPassword" name="newPassword" required>
                
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                
                <button type="submit" name="signup">Sign Up</button>
            </form>

            <p class="signup-link">Already have an account? <a href="login.php">Login</a></p>
        </section>

    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>
