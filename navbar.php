<header>
    <div class="header-content">
        <div class="logo">BloodConnect</div>
        <nav>
            <a href="index.php">Home</a>
            <?php
            // Only start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // Show user navigation
            if(isset($_SESSION['user_id'])) {
                echo "<a href='donor.php'>Donate Blood</a>";
                echo "<a href='request.php'>Request Blood</a>";
                echo "<a href='user_logout.php'>Logout</a>";
            } else {
                echo "<a href='user_login.php'>User Login</a>";
                echo "<a href='user_signup.php'>Sign Up</a>";
            }
            ?>
            <a href="contact.php">Contact</a>
            <?php
            // Show Hospital login/logout
            if(isset($_SESSION['hospital_id'])) {
                echo "<a href='hospital_panel.php'>Hospital</a>";
                echo "<a href='hospital_logout.php'>Hospital Logout</a>";
            } else {
                echo "<a href='hospital_login.php'>Hospital Login</a>";
            }
            // Show Admin login/logout
            if(isset($_SESSION['admin'])) {
                echo "<a href='admin_panel.php'>Admin</a>";
                echo "<a href='admin_logout.php'>Admin Logout</a>";
            } else {
                echo "<a href='admin_login.php'>Admin Login</a>";
            }
            ?>
        </nav>
    </div>
</header>
