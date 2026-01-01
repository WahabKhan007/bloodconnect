<?php
include "db.php";
session_start();
if(!isset($_SESSION['admin'])) header("location:admin_login.php");

if(isset($_GET['accept'])){
    $id=intval($_GET['accept']);
    $stmt = mysqli_prepare($con, "UPDATE requests SET status='Accepted' WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
if(isset($_GET['reject'])){
    $id=intval($_GET['reject']);
    $stmt = mysqli_prepare($con, "UPDATE requests SET status='Rejected' WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
if(isset($_GET['donated'])){
    $id = intval($_GET['donated']);
    // fetch request
    $stmt = mysqli_prepare($con, "SELECT hospital_id, blood_group, units FROM requests WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if ($row) {
        $hid = intval($row['hospital_id']);
        $bg = $row['blood_group'];
        $units = intval($row['units'] ?? 1);
        // decrement inventory if available
        $stmtInv = mysqli_prepare($con, "SELECT quantity FROM hospital_blood WHERE hospital_id=? AND blood_group=?");
        mysqli_stmt_bind_param($stmtInv, 'is', $hid, $bg);
        mysqli_stmt_execute($stmtInv);
        $resInv = mysqli_stmt_get_result($stmtInv);
        $invRow = mysqli_fetch_assoc($resInv);
        mysqli_stmt_close($stmtInv);
        $available = intval($invRow['quantity'] ?? 0);
        if ($available >= $units) {
            $stmtUpd = mysqli_prepare($con, "UPDATE hospital_blood SET quantity = quantity - ? WHERE hospital_id=? AND blood_group=?");
            mysqli_stmt_bind_param($stmtUpd, 'iis', $units, $hid, $bg);
            mysqli_stmt_execute($stmtUpd);
            mysqli_stmt_close($stmtUpd);
            $s = 'Donated';
            $stmt2 = mysqli_prepare($con, "UPDATE requests SET status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt2, 'si', $s, $id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
        }
    }
}
include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Blood Requests</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="section container">
<h2>Manage Blood Requests</h2>
<table>
<tr><th>Name</th><th>Blood Group</th><th>Units</th><th>Status</th><th>Action</th></tr>
<?php
 $q=mysqli_query($con,"SELECT r.*, h.hname FROM requests r LEFT JOIN hospitals h ON r.hospital_id=h.id ORDER BY r.id DESC");
if(!$q) {
    echo "<tr><td colspan='4'><strong style='color:red;'>Error: " . htmlspecialchars(mysqli_error($con)) . "</strong></td></tr>";
} else {
    $count = 0;
    while($d=mysqli_fetch_assoc($q)){
        $rname = htmlspecialchars($d['name']);
        $rbg = htmlspecialchars($d['blood_group']);
        $runits = intval($d['units'] ?? 1);
        $rstatus = htmlspecialchars($d['status']);
        $rid = intval($d['id']);
        echo "<tr>
        <td>$rname</td>
        <td>$rbg</td>
        <td>$runits</td>
        <td>$rstatus</td>
        <td>";
        if ($rstatus === 'Pending') {
            echo "<a href='admin_requests.php?accept=$rid' class='btn btn-primary'>Accept</a> ";
            echo "<a href='admin_requests.php?reject=$rid' class='btn btn-secondary'>Reject</a> ";
            echo "<a href='admin_requests.php?donated=$rid' class='btn btn-success'>Mark Donated</a> ";
        } elseif ($rstatus === 'Accepted') {
            echo "<a href='admin_requests.php?donated=$rid' class='btn btn-success'>Mark Donated</a> ";
            echo "<a href='admin_requests.php?reject=$rid' class='btn btn-secondary'>Reject</a> ";
        } elseif ($rstatus === 'Rejected') {
            // no actions
        } elseif ($rstatus === 'Donated') {
            // no actions after donated
        } else {
            echo "<a href='admin_requests.php?donated=$rid' class='btn btn-success'>Mark Donated</a> ";
        }
        echo "</td>
        </tr>";
        $count++;
    }
    if($count == 0) {
        echo "<tr><td colspan='4'>No requests found</td></tr>";
    }
}
?>
</table>
<?php include "footer.php"; ?>
</section>
</body>
</html>
