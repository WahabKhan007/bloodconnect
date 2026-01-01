<?php
include "db.php";
session_start();

// If already logged in as admin, redirect to dashboard
if(isset($_SESSION['admin'])) {
    header("location:admin_panel.php");
    exit;
}

if(isset($_POST['btn'])){
    $u=$_POST['username']; $p=$_POST['password'];
    $q=mysqli_query($con,"SELECT * FROM admin WHERE username='$u' AND password='$p'");
    if(mysqli_num_rows($q)==1){
        $_SESSION['admin']=$u;
        header("location:admin_panel.php");
        exit;
    } else { $error="Invalid Credentials"; }
}
include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Admin Login</h2>
<div class="form-section">
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
<div class="form-group">
<label>Username</label>
<input type="text" name="username" required>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<button class="btn btn-primary" name="btn">Login</button>
</form>
</div>
</section>

<?php include "footer.php"; ?>
</body>
</html>
