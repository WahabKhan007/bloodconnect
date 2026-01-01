<?php
include "db.php";
session_start();
if(!isset($_SESSION['admin'])) header("location:admin_login.php");

if(isset($_GET['accept'])){
    $id=intval($_GET['accept']);
    $stmt = mysqli_prepare($con, "UPDATE donors SET status='Accepted' WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
if(isset($_GET['reject'])){
    $id=intval($_GET['reject']);
    $stmt = mysqli_prepare($con, "UPDATE donors SET status='Rejected' WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
// Admin can also mark donor as donated (will upsert into hospital_blood)
if(isset($_GET['donated'])){
    $id = intval($_GET['donated']);
    // fetch donor
    $stmt = mysqli_prepare($con, "SELECT hospital_id, blood_group, units FROM donors WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if ($row) {
        $hid = intval($row['hospital_id']);
        $bg = $row['blood_group'];
        $units = intval($row['units'] ?? 1);
        $stmt2 = mysqli_prepare($con, "UPDATE donors SET status='Donated', is_donor=1, donated_at=NOW() WHERE id=?");
        mysqli_stmt_bind_param($stmt2, 'i', $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        $stmt3 = mysqli_prepare($con, "INSERT INTO hospital_blood (hospital_id, blood_group, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        mysqli_stmt_bind_param($stmt3, 'isi', $hid, $bg, $units);
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);
    }
}
include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Donors</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Manage Donors</h2>
<table>
<tr><th>Name</th><th>Blood Group</th><th>Units</th><th>Status</th><th>Action</th></tr>
<?php
$q=mysqli_query($con,"SELECT * FROM donors ORDER BY id DESC");
if(!$q) {
    echo "<tr><td colspan='5'><strong style='color:red;'>Error: " . htmlspecialchars(mysqli_error($con)) . "</strong></td></tr>";
} else {
    $count = 0;
    while($d=mysqli_fetch_assoc($q)){
        $dname = htmlspecialchars($d['name']);
        $dbg = htmlspecialchars($d['blood_group']);
        $dunits = intval($d['units'] ?? 1);
        $dstatus = htmlspecialchars($d['status']);
        $did = intval($d['id']);
        echo "<tr>
        <td>$dname</td>
        <td>$dbg</td>
        <td>$dunits</td>
        <td>$dstatus</td>
        <td>";
        if ($dstatus === 'Pending') {
            echo "<a href='admin_donors.php?accept=$did' class='btn btn-primary'>Accept</a> ";
            echo "<a href='admin_donors.php?reject=$did' class='btn btn-secondary'>Reject</a> ";
            echo "<a href='admin_donors.php?donated=$did' class='btn btn-success'>Mark Donated</a> ";
        } elseif ($dstatus === 'Accepted') {
            echo "<a href='admin_donors.php?donated=$did' class='btn btn-success'>Mark Donated</a> ";
            echo "<a href='admin_donors.php?reject=$did' class='btn btn-secondary'>Reject</a> ";
        } elseif ($dstatus === 'Rejected') {
            // only delete available via other UI (not shown here)
        } elseif ($dstatus === 'Donated') {
            // no actions after donated
        } else {
            echo "<a href='admin_donors.php?donated=$did' class='btn btn-success'>Mark Donated</a> ";
        }
        echo "</td></tr>";
        $count++;
    }
    if($count == 0) {
        echo "<tr><td colspan='5'>No donors found</td></tr>";
    }
}
?>
</table>
<?php include "footer.php"; ?>
</section>
</body>
</html>
