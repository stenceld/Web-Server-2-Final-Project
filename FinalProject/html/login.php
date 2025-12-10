<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/login.css">
    <title>Login - CineReview</title>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main>

        <section id="Login Form">
            <h2>Login</h2>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-message">
                    <?php
                    switch ($_GET['error']) {
                        case 'invalid_credentials':
                            echo "Invalid username or password.";
                            break;
                        case 'login_required':
                            echo "Please login to submit a review.";
                            break;
                        default:
                            echo "An error occurred. Please try again.";
                    }
                    ?>
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'account_created'): ?>
                <p class="success-message">Account created successfully! Please login.</p>
            <?php endif; ?>

            <form method="post" action="../process.php">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit" name="login">Login</button>
            </form>

            <p class="signup-link">Don't have an account? <a href="signup.php">Sign up</a></p>
        </section>

    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>
