<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

include "db.php";
$success_msg = '';
$error_msg = '';

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? '';

// Get user info
$user_stmt = $con->prepare('SELECT name, phone FROM users WHERE id = ?');
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

if (isset($_POST['btn'])) {
    $name = trim($_POST['name'] ?? '');
    $bg = $_POST['blood_group'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $h = $_POST['hospital'] ?? '';
    $units = intval($_POST['units'] ?? 1);

    if ($name === '' || $bg === '' || $phone === '' || $city === '' || $h === '') {
        $error_msg = 'Please fill in all fields.';
    } else {
        $stmt = $con->prepare("INSERT INTO donors (user_id, name, blood_group, phone, city, hospital_id, units, applicant, status, is_donor, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 0, NOW())");
        if ($stmt) {
            $stmt->bind_param('issssiis', $user_id, $name, $bg, $phone, $city, $h, $units, $user_email);
            if ($stmt->execute()) {
                $success_msg = 'Donation submitted successfully.';
            } else {
                $error_msg = 'Database error: ' . $con->error;
            }
            $stmt->close();
        } else {
            $error_msg = 'Database error: ' . $con->error;
        }
    }
}
include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Donate Blood</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h2>Donate Blood</h2>
    <p style="margin: 0; color: var(--muted-foreground);">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></strong>!</p>
</div>

<?php if ($success_msg): ?>
    <div class="success"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="error"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<div class="form-section">
<form method="POST">
<div class="form-group">
<label>Name</label>
<input type="text" name="name" required>
</div>

<div class="form-group">
<label>Blood Group</label>
<select name="blood_group" required>
<option>A+</option><option>A-</option>
<option>B+</option><option>B-</option>
<option>O+</option><option>O-</option>
<option>AB+</option><option>AB-</option>
</select>
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" required>
</div>

<div class="form-group">
<label>City</label>
<input type="text" name="city" required>
</div>

<div class="form-group">
<label>Choose Hospital</label>
<select name="hospital">
<?php
$r = mysqli_query($con, "SELECT * FROM hospitals");
if ($r) {
    while ($x = mysqli_fetch_assoc($r)) {
        $hid = htmlspecialchars($x['id']);
        $hname = htmlspecialchars($x['hname']);
        echo "<option value='" . $hid . "'>" . $hname . "</option>";
    }
}
?>
</select>
</div>

<div class="form-group">
<label>Units</label>
<input type="number" name="units" value="1" min="1" required>
</div>

<button class="btn btn-primary" name="btn">Submit</button>
</form>
</div>

<h2 style="margin-top:3rem;">Your Recent Donations</h2>
<table>
<tr><th>Name</th><th>Blood Group</th><th>Units</th><th>Status</th><th>Submitted</th></tr>
<?php
$q = $con->prepare("SELECT * FROM donors WHERE user_id = ? ORDER BY id DESC LIMIT 10");
$q->bind_param('i', $user_id);
$q->execute();
$result = $q->get_result();

if ($result && $result->num_rows > 0) {
    while ($d = $result->fetch_assoc()) {
        $dname = htmlspecialchars($d['name'] ?? '');
        $dbg = htmlspecialchars($d['blood_group'] ?? '');
        $dunits = intval($d['units'] ?? 1);
        $dstatus = htmlspecialchars($d['status'] ?? 'Pending');
        $dcreated = isset($d['created_at']) ? htmlspecialchars($d['created_at']) : '';
        echo "<tr><td>$dname</td><td>$dbg</td><td>$dunits</td><td>$dstatus</td><td>$dcreated</td></tr>";
    }
} else {
    echo "<tr><td colspan='5'>No donations submitted yet.</td></tr>";
}
$q->close();
?>
</table>

<?php include "footer.php"; ?>
</section>
</body>
</html>
