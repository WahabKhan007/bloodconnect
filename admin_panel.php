<?php 
include "db.php";
session_start();
if(!isset($_SESSION['admin'])) header("location:admin_login.php");
include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Admin Dashboard</h2>

<div class="stats" style="display:flex;gap:1rem;flex-wrap:wrap;">
<div class="card">
<?php $c=mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) AS cnt FROM donors")); ?>
<div class="stat-number"><?php echo $c['cnt']; ?></div>
<div class="stat-label">Total Donors</div>
</div>

<div class="card">
<?php $c=mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) AS cnt FROM requests")); ?>
<div class="stat-number"><?php echo $c['cnt']; ?></div>
<div class="stat-label">Total Requests</div>
</div>

<div class="card">
<?php $c=mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) AS cnt FROM hospitals")); ?>
<div class="stat-number"><?php echo $c['cnt']; ?></div>
<div class="stat-label">Hospitals</div>
</div>

<div class="card">
<?php $c=mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) AS cnt FROM contacts")); ?>
<div class="stat-number"><?php echo $c['cnt']; ?></div>
<div class="stat-label">Contact Messages</div>
</div>

<div class="card">
<?php $c=mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) AS cnt FROM users")); ?>
<div class="stat-number"><?php echo $c['cnt']; ?></div>
<div class="stat-label">Total Users</div>
</div>
</div>

<div style="margin-top:2rem;">
<a href="admin_hospitals.php" class="btn btn-primary">Manage Hospitals</a>
<a href="admin_users.php" class="btn btn-primary">Manage Users</a>
<a href="admin_logout.php" class="btn btn-secondary">Logout</a>
</div>

<h2 style="margin-top:3rem;">Contact Us Queries</h2>
<table>
<tr><th>Name</th><th>Email</th><th>Message</th><th>Submitted At</th></tr>
<?php
$q = mysqli_query($con, "SELECT * FROM contacts ORDER BY id DESC LIMIT 20");
if (!$q) {
    echo "<tr><td colspan='4'><strong style='color:red;'>Error: " . htmlspecialchars(mysqli_error($con)) . "</strong></td></tr>";
} else {
    $count = 0;
    while ($d = mysqli_fetch_assoc($q)) {
        $cname = htmlspecialchars($d['name']);
        $cemail = htmlspecialchars($d['email']);
        $cmsg = htmlspecialchars(substr($d['message'], 0, 100)); // truncate to 100 chars
        $created = htmlspecialchars($d['created_at'] ?? '');
        echo "<tr><td>$cname</td><td>$cemail</td><td>$cmsg...</td><td>$created</td></tr>";
        $count++;
    }
    if ($count == 0) {
        echo "<tr><td colspan='4'>No contact messages yet</td></tr>";
    }
}
?>
</table>
</section>

<?php include "footer.php"; ?>
</body>
</html>
