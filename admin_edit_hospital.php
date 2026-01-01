<?php
include "db.php";
session_start();
if(!isset($_SESSION['admin'])) header("location:admin_login.php");

$error_msg = '';
$success_msg = '';
$hospital = null;

if (!isset($_GET['id'])) {
    header("location:admin_hospitals.php");
    exit;
}

$hid = intval($_GET['id']);

// Fetch hospital data
$stmt = mysqli_prepare($con, "SELECT * FROM hospitals WHERE id=?");
mysqli_stmt_bind_param($stmt, 'i', $hid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$hospital = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$hospital) {
    header("location:admin_hospitals.php");
    exit;
}

if (isset($_POST['btn'])) {
    $hname = trim($_POST['hname'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($hname === '') {
        $error_msg = 'Hospital name is required.';
    } else {
        // Update hospital
        $stmt = mysqli_prepare($con, "UPDATE hospitals SET hname=?, password=?, phone=?, email=?, address=? WHERE id=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssssi', $hname, $password, $phone, $email, $address, $hid);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = 'Hospital updated successfully.';
                // Refresh hospital data
                $hospital['hname'] = $hname;
                $hospital['password'] = $password;
                $hospital['phone'] = $phone;
                $hospital['email'] = $email;
                $hospital['address'] = $address;
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
<title>Edit Hospital</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Edit Hospital: <?php echo htmlspecialchars($hospital['hname']); ?></h2>

<?php if ($success_msg): ?>
    <div class="success"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="error"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<div class="form-section">
<form method="POST">
<div class="form-group">
<label>Hospital Name</label>
<input type="text" name="hname" value="<?php echo htmlspecialchars($hospital['hname']); ?>" required>
</div>

<div class="form-group">
<label>Username (Read-only)</label>
<input type="text" value="<?php echo htmlspecialchars($hospital['username']); ?>" disabled>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" value="<?php echo htmlspecialchars($hospital['password']); ?>" required>
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" name="phone" value="<?php echo htmlspecialchars($hospital['phone'] ?? ''); ?>">
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" value="<?php echo htmlspecialchars($hospital['email'] ?? ''); ?>">
</div>

<div class="form-group">
<label>Address</label>
<textarea name="address" rows="3"><?php echo htmlspecialchars($hospital['address'] ?? ''); ?></textarea>
</div>

<button class="btn btn-primary" name="btn">Update Hospital</button>
<a href="admin_hospitals.php" class="btn btn-secondary">Cancel</a>
</form>
</div>

<?php include "footer.php"; ?>
</section>
</body>
</html>
