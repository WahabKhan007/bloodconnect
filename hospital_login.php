<?php
include "db.php";
session_start();

// If already logged in as hospital, redirect to dashboard
if(isset($_SESSION['hospital_id'])) {
    header("location:hospital_panel.php");
    exit;
}

if(isset($_POST['btn'])){
    $u=$_POST['username']; $p=$_POST['password'];
    $q=mysqli_query($con,"SELECT * FROM hospitals WHERE username='$u' AND password='$p'");
    if(mysqli_num_rows($q)==1){
        $h=mysqli_fetch_assoc($q);
        $_SESSION['hospital_id']=$h['id'];
        $_SESSION['hospital_name']=$h['hname'];
        header("location:hospital_panel.php");
        exit;
    } else { $error="Invalid Credentials"; }
}
include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Hospital Login</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Hospital Login</h2>
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
