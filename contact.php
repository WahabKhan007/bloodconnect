<?php
include "db.php";
$success_msg = '';
$error_msg = '';

if (isset($_POST['name'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error_msg = 'All fields are required.';
    } else {
        $stmt = mysqli_prepare($con, "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $message);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = 'Thank you for reaching out! We will get back to you soon.';
            } else {
                $error_msg = 'Database error: ' . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = 'Database error: ' . mysqli_error($con);
        }
    }
}

include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - BloodConnect</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<section class="section container">
    <h2>Contact Us</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
        <!-- Contact Information -->
        <div class="card">
            <h3 style="color: var(--primary); margin-bottom: 1.5rem; font-size: 1.25rem;">Get in Touch</h3>
            
            <div style="margin-bottom: 1.5rem;">
                <h4 style="color: var(--foreground); margin-bottom: 0.5rem; font-weight: 600;">Email</h4>
                <p style="color: var(--muted-foreground);">info@bloodconnect.com</p>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <h4 style="color: var(--foreground); margin-bottom: 0.5rem; font-weight: 600;">Phone</h4>
                <p style="color: var(--muted-foreground);">+92 300 0000000</p>
            </div>
            
            <div>
                <h4 style="color: var(--foreground); margin-bottom: 0.5rem; font-weight: 600;">Address</h4>
                <p style="color: var(--muted-foreground); line-height: 1.8;">123 Blood Street, Medical District, City, Country</p>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="form-section" style="max-width: none; margin: 0;">
            <h3 style="margin-top: 0; color: var(--primary); margin-bottom: 1.5rem; font-size: 1.25rem;">Send us a Message</h3>
            
            <?php if ($success_msg): ?>
                <div class="success"><?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="error"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" placeholder="Tell us how we can help..." style="min-height: 150px; padding: 0.75rem; font-family: inherit;" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>
</body>
</html>
