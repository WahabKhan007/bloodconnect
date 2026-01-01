<?php
include "db.php";
session_start();
if(!isset($_SESSION['hospital_id'])) header("location:hospital_login.php");
$hid=$_SESSION['hospital_id'];

// ============================================
// ACTION HANDLERS FOR ALL TABS
// ============================================

$success_msg = '';
$error_msg = '';

// DONATIONS ACTIONS
if (isset($_GET['don_action'])) {
    $action = $_GET['don_action'];
    $did = intval($_GET['don_id'] ?? 0);
    
    if ($did > 0) {
        $stmt = mysqli_prepare($con, "SELECT hospital_id, blood_group, units FROM donors WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $did);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        
        if (!$row || intval($row['hospital_id']) !== intval($hid)) {
            $error_msg = 'Unauthorized or record not found.';
        } else {
            if ($action === 'accept') {
                $s = 'Accepted';
                $stmt = mysqli_prepare($con, "UPDATE donors SET status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'si', $s, $did);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success_msg = 'Donation accepted.';
            } elseif ($action === 'reject') {
                $s = 'Rejected';
                $stmt = mysqli_prepare($con, "UPDATE donors SET status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'si', $s, $did);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success_msg = 'Donation rejected.';
            } elseif ($action === 'donated') {
                $s = 'Donated';
                $units = intval($row['units'] ?? 1);
                $bg = $row['blood_group'];
                $stmt = mysqli_prepare($con, "UPDATE donors SET status=?, is_donor=1, donated_at=NOW() WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'si', $s, $did);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $stmt2 = mysqli_prepare($con, "INSERT INTO hospital_blood (hospital_id, blood_group, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
                mysqli_stmt_bind_param($stmt2, 'isi', $hid, $bg, $units);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                $success_msg = 'Donation marked as donated and inventory updated.';
            } elseif ($action === 'delete') {
                $stmt = mysqli_prepare($con, "DELETE FROM donors WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'i', $did);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success_msg = 'Donation deleted.';
            }
        }
    }
}

// REQUESTS ACTIONS
if (isset($_GET['req_action'])) {
    $action = $_GET['req_action'];
    $rid = intval($_GET['req_id'] ?? 0);
    
    if ($rid > 0) {
        $stmt = mysqli_prepare($con, "SELECT hospital_id, blood_group, units FROM requests WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $rid);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        
        if (!$row || intval($row['hospital_id']) !== intval($hid)) {
            $error_msg = 'Unauthorized or request not found.';
        } else {
            if ($action === 'accept') {
                $bg = $row['blood_group'];
                $units = intval($row['units']);
                $stmtInv = mysqli_prepare($con, "SELECT quantity FROM hospital_blood WHERE hospital_id=? AND blood_group=?");
                mysqli_stmt_bind_param($stmtInv, 'is', $hid, $bg);
                mysqli_stmt_execute($stmtInv);
                $resInv = mysqli_stmt_get_result($stmtInv);
                $invRow = mysqli_fetch_assoc($resInv);
                mysqli_stmt_close($stmtInv);
                
                $available = intval($invRow['quantity'] ?? 0);
                if ($available < $units) {
                    $error_msg = "Cannot Accept — Not Enough Blood. Available: $available, Required: $units.";
                } else {
                    $s = 'Accepted';
                    $stmt = mysqli_prepare($con, "UPDATE requests SET status=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, 'si', $s, $rid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $success_msg = 'Request accepted.';
                }
            } elseif ($action === 'reject') {
                $s = 'Rejected';
                $stmt = mysqli_prepare($con, "UPDATE requests SET status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'si', $s, $rid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success_msg = 'Request rejected.';
            } elseif ($action === 'donated') {
                $bg = $row['blood_group'];
                $units = intval($row['units']);
                $stmtInv = mysqli_prepare($con, "SELECT quantity FROM hospital_blood WHERE hospital_id=? AND blood_group=?");
                mysqli_stmt_bind_param($stmtInv, 'is', $hid, $bg);
                mysqli_stmt_execute($stmtInv);
                $resInv = mysqli_stmt_get_result($stmtInv);
                $invRow = mysqli_fetch_assoc($resInv);
                mysqli_stmt_close($stmtInv);
                
                $available = intval($invRow['quantity'] ?? 0);
                if ($available < $units) {
                    $error_msg = "Cannot Mark Donated — Not Enough Blood. Available: $available, Required: $units.";
                } else {
                    $stmtUpd = mysqli_prepare($con, "UPDATE hospital_blood SET quantity = quantity - ? WHERE hospital_id=? AND blood_group=?");
                    mysqli_stmt_bind_param($stmtUpd, 'iis', $units, $hid, $bg);
                    mysqli_stmt_execute($stmtUpd);
                    mysqli_stmt_close($stmtUpd);
                    
                    $s = 'Donated';
                    $stmt = mysqli_prepare($con, "UPDATE requests SET status=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, 'si', $s, $rid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $success_msg = 'Request marked as donated and inventory updated.';
                }
            } elseif ($action === 'delete') {
                $stmt = mysqli_prepare($con, "DELETE FROM requests WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'i', $rid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success_msg = 'Request deleted.';
            }
        }
    }
}

include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Hospital Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
<style>
  .tab-buttons {
    display: flex;
    gap: 0.5rem;
    margin: 2rem 0 1.5rem 0;
    border-bottom: 2px solid #e0e0e0;
  }
  .tab-btn {
    padding: 0.8rem 1.5rem;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 1rem;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
  }
  .tab-btn:hover {
    color: #333;
    border-bottom-color: #ddd;
  }
  .tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    font-weight: 600;
  }
  .tab-content {
    display: none;
    animation: fadeIn 0.3s ease;
  }
  .tab-content.active {
    display: block;
  }
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  .quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
  }
  .quick-link-card {
    padding: 1.5rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #f8f9fa;
    text-align: center;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
  }
  .quick-link-card:hover {
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }
  .quick-link-card h4 {
    margin: 0 0 0.5rem 0;
    color: var(--primary);
  }
  .quick-link-card p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
  }
  .dashboard-section {
    border: 1px solid #ddd;
    padding: 1.5rem;
    border-radius: 8px;
    background-color: #f9f9f9;
  }
  .dashboard-section h3 {
    margin-top: 0;
    color: #333;
  }
  .dashboard-section table {
    width: 100%;
    font-size: 0.9rem;
  }
  .dashboard-section table th,
  .dashboard-section table td {
    padding: 0.5rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
</style>
</head>
<body>

<section class="section container">
<?php
// Get hospital name
$hospital_name = 'Hospital';
$h_stmt = mysqli_prepare($con, "SELECT hname FROM hospitals WHERE id=?");
if ($h_stmt) {
    mysqli_stmt_bind_param($h_stmt, 'i', $hid);
    mysqli_stmt_execute($h_stmt);
    $h_res = mysqli_stmt_get_result($h_stmt);
    if ($h_row = mysqli_fetch_assoc($h_res)) {
        $hospital_name = htmlspecialchars($h_row['hname']);
    }
    mysqli_stmt_close($h_stmt);
}
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h2>Hospital Dashboard</h2>
    <p style="margin: 0; color: var(--muted-foreground);\"><strong><?php echo $hospital_name; ?></strong></p>
</div>

<?php if ($success_msg): ?>
    <div class="success" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="error" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<!-- Tabs for Tables -->
<div class="dashboard-section">
    <div class="tab-buttons">
        <button class="tab-btn active" onclick="showTab('donations')">Incoming Donations</button>
        <button class="tab-btn" onclick="showTab('requests')">Blood Requests</button>
        <button class="tab-btn" onclick="showTab('bloodbank')">My Blood Bank</button>
    </div>

    <!-- Incoming Donations Tab -->
    <div id="donations" class="tab-content active">
        <h3>Incoming Donations</h3>
        <table>
            <tr><th>Name</th><th>Blood Group</th><th>Units</th><th>Status</th><th>Submitted</th><th>Action</th></tr>
            <?php
            $q = mysqli_query($con, "SELECT * FROM donors WHERE hospital_id='$hid' ORDER BY id DESC");
            if ($q) {
                $count = 0;
                while ($d = mysqli_fetch_assoc($q)) {
                    $dname = htmlspecialchars($d['name']);
                    $dbg = htmlspecialchars($d['blood_group']);
                    $dunits = intval($d['units'] ?? 1);
                    $dstatus = htmlspecialchars($d['status'] ?? 'Pending');
                    $dcreated = isset($d['created_at']) ? htmlspecialchars($d['created_at']) : '';
                    $ddonated = isset($d['donated_at']) ? htmlspecialchars($d['donated_at']) : '';
                    $did = intval($d['id']);
                    echo "<tr><td>$dname</td><td>$dbg</td><td>$dunits</td><td>$dstatus</td><td>$dcreated";
                    if ($ddonated) echo "<br><small>Donated: $ddonated</small>";
                    echo "</td><td>";
                    if ($dstatus === 'Pending') {
                        echo "<a class='btn btn-primary' href='hospital_panel.php?don_action=accept&don_id=$did'>Accept</a> ";
                        echo "<a class='btn btn-secondary' href='hospital_panel.php?don_action=reject&don_id=$did'>Reject</a> ";
                        echo "<a class='btn btn-success' href='hospital_panel.php?don_action=donated&don_id=$did'>Mark Donated</a> ";
                        echo "<a class='btn btn-danger' href='hospital_panel.php?don_action=delete&don_id=$did' onclick=\"return confirm('Delete this record?')\">Delete</a>";
                    } elseif ($dstatus === 'Accepted') {
                        echo "<a class='btn btn-success' href='hospital_panel.php?don_action=donated&don_id=$did'>Mark Donated</a> ";
                        echo "<a class='btn btn-danger' href='hospital_panel.php?don_action=delete&don_id=$did' onclick=\"return confirm('Delete this record?')\">Delete</a>";
                    } elseif ($dstatus === 'Rejected') {
                        echo "<a class='btn btn-danger' href='hospital_panel.php?don_action=delete&don_id=$did' onclick=\"return confirm('Delete this record?')\">Delete</a>";
                    } elseif ($dstatus === 'Donated') {
                        echo '<span style="color: #28a745; font-weight: 600;">✓ Donated</span>';
                    }
                    echo "</td></tr>";
                    $count++;
                }
                if ($count === 0) {
                    echo "<tr><td colspan='6'>No incoming donations</td></tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Error loading donations</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Blood Requests Tab -->
    <div id="requests" class="tab-content">
        <h3>Blood Requests</h3>
        <table>
            <tr><th>Name</th><th>Blood Group</th><th>Units</th><th>Status</th><th>Submitted</th><th>Action</th></tr>
            <?php
            $q = mysqli_query($con, "SELECT * FROM requests WHERE hospital_id='$hid' ORDER BY id DESC");
            if ($q) {
                $count = 0;
                while ($d = mysqli_fetch_assoc($q)) {
                    $rname = htmlspecialchars($d['name']);
                    $rbg = htmlspecialchars($d['blood_group']);
                    $runits = intval($d['units']);
                    $rstatus = htmlspecialchars($d['status']);
                    $rcreated = htmlspecialchars($d['created_at']);
                    $rid = intval($d['id']);
                    echo "<tr><td>$rname</td><td>$rbg</td><td>$runits</td><td>$rstatus</td><td>$rcreated</td><td>";
                    if ($rstatus === 'Pending') {
                        echo "<a class='btn btn-primary' href='hospital_panel.php?req_action=accept&req_id=$rid'>Accept</a> ";
                        echo "<a class='btn btn-secondary' href='hospital_panel.php?req_action=reject&req_id=$rid'>Reject</a> ";
                        echo "<a class='btn btn-danger' href='hospital_panel.php?req_action=delete&req_id=$rid' onclick=\"return confirm('Delete this request?')\">Delete</a>";
                    } elseif ($rstatus === 'Accepted') {
                        echo "<a class='btn btn-success' href='hospital_panel.php?req_action=donated&req_id=$rid'>Mark Donated</a> ";
                        echo "<a class='btn btn-danger' href='hospital_panel.php?req_action=delete&req_id=$rid' onclick=\"return confirm('Delete this request?')\">Delete</a>";
                    } elseif ($rstatus === 'Rejected') {
                        echo "<a class='btn btn-danger' href='hospital_panel.php?req_action=delete&req_id=$rid' onclick=\"return confirm('Delete this request?')\">Delete</a>";
                    } elseif ($rstatus === 'Donated') {
                        echo '<span style="color: #28a745; font-weight: 600;">✓ Donated</span>';
                    }
                    echo "</td></tr>";
                    $count++;
                }
                if ($count === 0) {
                    echo "<tr><td colspan='6'>No blood requests</td></tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Error loading requests</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Blood Bank Tab -->
    <div id="bloodbank" class="tab-content">
        <h3>Available Blood Inventory</h3>
        <table>
            <tr><th>Blood Group</th><th>Available Units</th></tr>
            <?php
            $all_groups = array('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-');
            
            // Get available inventory from hospital_blood
            $inventory_data = [];
            $bloods = mysqli_query($con, "SELECT blood_group, SUM(quantity) as qty FROM hospital_blood WHERE hospital_id='$hid' GROUP BY blood_group");
            if ($bloods) {
                while ($b = mysqli_fetch_assoc($bloods)) {
                    $bg = $b['blood_group'];
                    $inventory_data[$bg] = intval($b['qty'] ?? 0);
                }
            }
            
            // Initialize all blood groups
            foreach ($all_groups as $bg) {
                if (!isset($inventory_data[$bg])) {
                    $inventory_data[$bg] = 0;
                }
            }
            
            // Display table
            $count = 0;
            foreach ($all_groups as $bg) {
                $available = $inventory_data[$bg];
                echo "<tr>";
                echo "<td><strong>$bg</strong></td>";
                echo "<td style='font-weight: 600;'>$available units</td>";
                echo "</tr>";
                if ($available > 0) {
                    $count++;
                }
            }
            
            if ($count === 0) {
                echo "<tr><td colspan='2'>No blood inventory</td></tr>";
            }
            ?>
        </table>

        <!-- Recent Donations Stats -->
        <h3 style="margin-top: 3rem;">Blood Statistics</h3>
        <table>
            <tr><th>Blood Group</th><th>Left Units (Available)</th><th>Fulfilled Requests</th></tr>
            <?php
            $all_groups = array('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-');
            
            foreach ($all_groups as $bg) {
                // Get available units
                $stmt_avail = mysqli_prepare($con, "SELECT SUM(quantity) as qty FROM hospital_blood WHERE hospital_id=? AND blood_group=?");
                mysqli_stmt_bind_param($stmt_avail, 'is', $hid, $bg);
                mysqli_stmt_execute($stmt_avail);
                $res_avail = mysqli_stmt_get_result($stmt_avail);
                $row_avail = mysqli_fetch_assoc($res_avail);
                mysqli_stmt_close($stmt_avail);
                $available = intval($row_avail['qty'] ?? 0);
                
                // Get fulfilled requests (status 'Donated')
                $stmt_fulfilled = mysqli_prepare($con, "SELECT SUM(units) as fulfilled_sum FROM requests WHERE hospital_id=? AND blood_group=? AND status='Donated'");
                mysqli_stmt_bind_param($stmt_fulfilled, 'is', $hid, $bg);
                mysqli_stmt_execute($stmt_fulfilled);
                $res_fulfilled = mysqli_stmt_get_result($stmt_fulfilled);
                $row_fulfilled = mysqli_fetch_assoc($res_fulfilled);
                mysqli_stmt_close($stmt_fulfilled);
                $fulfilled = intval($row_fulfilled['fulfilled_sum'] ?? 0);
                
                if ($available > 0 || $fulfilled > 0) {
                    echo "<tr>";
                    echo "<td><strong>$bg</strong></td>";
                    echo "<td>$available units</td>";
                    echo "<td>$fulfilled units</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active from all buttons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    
    // Mark button as active
    event.target.classList.add('active');
}
</script>

<?php include "footer.php"; ?>
</section>
</body>
</html>
