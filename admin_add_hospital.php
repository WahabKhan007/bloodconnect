<?php
include "db.php";
session_start();
if(!isset($_SESSION['admin'])) header("location:admin_login.php");

$error_msg = '';
$success_msg = '';

if (isset($_POST['btn'])) {
    $hname = trim($_POST['hname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($hname === '' || $username === '' || $password === '') {
        $error_msg = 'Hospital name, username, and password are required.';
    } else {
        // Check if username already exists
        $check_stmt = mysqli_prepare($con, "SELECT id FROM hospitals WHERE username=?");
        mysqli_stmt_bind_param($check_stmt, 's', $username);
        mysqli_stmt_execute($check_stmt);
        $res = mysqli_stmt_get_result($check_stmt);
        if (mysqli_fetch_assoc($res)) {
            $error_msg = 'Username already exists.';
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);
            // Insert new hospital
            $insert_stmt = mysqli_prepare($con, "INSERT INTO hospitals (hname, username, password, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, 'ssssss', $hname, $username, $password, $phone, $email, $address);
                if (mysqli_stmt_execute($insert_stmt)) {
                    $success_msg = 'Hospital added successfully.';
                } else {
                    $error_msg = 'Database error: ' . mysqli_error($con);
                }
                mysqli_stmt_close($insert_stmt);
            } else {
                $error_msg = 'Database error: ' . mysqli_error($con);
            }
        }
    }
}

include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Hospital</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Add New Hospital</h2>

<?php if ($success_msg): ?>
    <div class="success"><?php echo htmlspecialchars($success_msg); ?></div>
    <p><a href="admin_hospitals.php" class="btn btn-primary">Back to Hospitals</a></p>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="error"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<div class="form-section">
<form method="POST">
<div class="form-group">
<label>Hospital Name</label>
<input type="text" name="hname" required>
</div>

<div class="form-group">
<label>Username</label>
<input type="text" name="username" required>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" name="phone">
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email">
</div>

<div class="form-group">
<label>Address</label>
<textarea name="address" rows="3"></textarea>
</div>

<button class="btn btn-primary" name="btn">Add Hospital</button>
</form>
</div>

<?php include "footer.php"; ?>
</section>
</body>
</html>
