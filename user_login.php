<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: donor.php');
    exit();
}

include 'db.php';
include 'navbar.php';

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        // Check user exists
        $login_stmt = $con->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $login_stmt->bind_param('s', $email);
        $login_stmt->execute();
        $result = $login_stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                header('Location: donor.php');
                exit();
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Email not found. Please sign up.';
        }
        $login_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign In - BloodConnect</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
        <div class="auth-container">
            <h2>Sign In</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
            </form>

            <div class="link-text">
                Don't have an account? <a href="user_signup.php">Sign up</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
