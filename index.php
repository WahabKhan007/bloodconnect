<?php include "navbar.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>BloodConnect | Save Lives Through Blood Donation</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="BloodConnect - Connecting blood donors with hospitals and patients to save lives">
</head>
<body>

<section class="hero">
    <h1>Donate Blood. Save Lives.</h1>
    <?php if(isset($_SESSION['user_id']) && isset($_SESSION['user_name'])): ?>
        <p style="font-size: 1.1rem; margin-bottom: 1rem;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</p>
    <?php endif; ?>
    <p>BloodConnect connects donors, hospitals, and patients on one unified platform. Your contribution today can save a life tomorrow.</p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
        <?php
        if(isset($_SESSION['user_id'])) {
            echo '<button class="btn btn-primary" onclick="location.href=\'donor.php\'">Donate Blood Now</button>';
            echo '<button class="btn btn-secondary" onclick="location.href=\'request.php\'" style="background:white;color:var(--primary);border:2px solid white;">Request Blood</button>';
        } else {
            echo '<button class="btn btn-primary" onclick="location.href=\'user_signup.php\'">Get Started</button>';
            echo '<button class="btn btn-secondary" onclick="location.href=\'user_login.php\'" style="background:white;color:var(--primary);border:2px solid white;">Sign In</button>';
        }
        ?>
    </div>
</section>

<section class="section container">
    <h2>Why BloodConnect?</h2>
    <p style="text-align: center; color: var(--muted-foreground); margin-top: 1rem; font-size: 1.05rem; line-height: 1.8;">
        Our platform ensures fast, reliable communication between donors, hospitals, and patients. <strong>Every second counts — every drop matters.</strong>
    </p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem;">
        <div class="card">
            <div style="font-size: 2rem; margin-bottom: 1rem;">Donors</div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--primary);">Register & Donate</h3>
            <p style="color: var(--muted-foreground); line-height: 1.8;">Create your account, share your blood type, and help patients in need. Every donation counts.</p>
        </div>
        
        <div class="card">
            <div style="font-size: 2rem; margin-bottom: 1rem;">Patients</div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--primary);">Request Blood</h3>
            <p style="color: var(--muted-foreground); line-height: 1.8;">Submit blood requests quickly and securely. Connect with hospitals near you instantly.</p>
        </div>
        
        <div class="card">
            <div style="font-size: 2rem; margin-bottom: 1rem;">Hospitals</div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--primary);">Manage & Track</h3>
            <p style="color: var(--muted-foreground); line-height: 1.8;">Manage blood inventory, process donations, and fulfill patient requests efficiently.</p>
        </div>
    </div>
</section>

<section style="background: var(--muted); padding: 3rem 1rem;">
    <div class="container">
        <h2>How It Works</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary); font-weight: 700; margin-bottom: 1rem;">1</div>
                <h4 style="margin-bottom: 0.5rem;">Sign Up</h4>
                <p style="color: var(--muted-foreground);">Create your account in seconds</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary); font-weight: 700; margin-bottom: 1rem;">2</div>
                <h4 style="margin-bottom: 0.5rem;">Choose Your Role</h4>
                <p style="color: var(--muted-foreground);">Donor, patient, or hospital admin</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary); font-weight: 700; margin-bottom: 1rem;">3</div>
                <h4 style="margin-bottom: 0.5rem;">Connect & Save</h4>
                <p style="color: var(--muted-foreground);">Donate or request blood instantly</p>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>
</body>
</html>
