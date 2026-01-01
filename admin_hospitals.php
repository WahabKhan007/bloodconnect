<?php
include "db.php";
session_start();
if(!isset($_SESSION['admin'])) header("location:admin_login.php");

// Handle delete action
if (isset($_GET['delete'])) {
    $hid = intval($_GET['delete']);
    $stmt = mysqli_prepare($con, "DELETE FROM hospitals WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $hid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Hospitals</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Manage Hospitals</h2>

<div style="margin-bottom: 2rem;">
<a href="admin_add_hospital.php" class="btn btn-primary">Add New Hospital</a>
</div>

<table>
<tr><th>Hospital Name</th><th>Username</th><th>Phone</th><th>Email</th><th>Address</th><th>Action</th></tr>
<?php
$q = mysqli_query($con, "SELECT * FROM hospitals ORDER BY id DESC");
if (!$q) {
    echo "<tr><td colspan='6'><strong style='color:red;'>Error: " . htmlspecialchars(mysqli_error($con)) . "</strong></td></tr>";
} else {
    $count = 0;
    while ($h = mysqli_fetch_assoc($q)) {
        $hname = htmlspecialchars($h['hname']);
        $username = htmlspecialchars($h['username']);
        $phone = htmlspecialchars($h['phone'] ?? '');
        $email = htmlspecialchars($h['email'] ?? '');
        $address = htmlspecialchars($h['address'] ?? '');
        $hid = intval($h['id']);
        echo "<tr>
        <td>$hname</td>
        <td>$username</td>
        <td>$phone</td>
        <td>$email</td>
        <td>$address</td>
        <td>
        <a href='admin_edit_hospital.php?id=$hid' class='btn btn-primary'>Edit</a>
        <a href='admin_hospitals.php?delete=$hid' class='btn btn-danger' onclick=\"return confirm('Delete this hospital?')\">Delete</a>
        </td>
        </tr>";
        $count++;
    }
    if ($count == 0) {
        echo "<tr><td colspan='6'>No hospitals found</td></tr>";
    }
}
?>
</table>

<?php include "footer.php"; ?>
</section>
</body>
</html>
