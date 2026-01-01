<?php
include "db.php";
session_start();
if(!isset($_SESSION['admin'])) header("location:admin_login.php");
include "navbar.php";
<?php
$hospitals=mysqli_query($con,"SELECT * FROM hospitals");
if (!$hospitals) {
    echo "<p style='color:red;'>" . htmlspecialchars(mysqli_error($con)) . "</p>";
} else {
    while($h=mysqli_fetch_assoc($hospitals)){
        $hname = htmlspecialchars($h['hname']);
        $hid = intval($h['id']);
        echo "<div class='card'><h3>$hname</h3>";
        // gather hb and donor sums like dashboard
        $hb = [];
        $res = mysqli_query($con, "SELECT blood_group, quantity FROM hospital_blood WHERE hospital_id='$hid'");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) $hb[$r['blood_group']] = intval($r['quantity']);
        }
        $donorSums = [];
        $res2 = mysqli_query($con, "SELECT blood_group, SUM(units) as sumu FROM donors WHERE hospital_id='$hid' AND is_donor=1 GROUP BY blood_group");
        if ($res2) {
            while ($r = mysqli_fetch_assoc($res2)) $donorSums[$r['blood_group']] = intval($r['sumu']);
        }
        $groups = array_unique(array_merge(array_keys($hb), array_keys($donorSums)));
        echo "<table><tr><th>Blood Group</th><th>Quantity</th></tr>";
        if (count($groups) === 0) {
            echo "<tr><td colspan='2'>No blood inventory</td></tr>";
        } else {
            foreach ($groups as $bg) {
                $qty = isset($hb[$bg]) ? $hb[$bg] : ($donorSums[$bg] ?? 0);
                $bbg = htmlspecialchars($bg);
                echo "<tr><td>$bbg</td><td>$qty</td></tr>";
            }
        }
        echo "</table></div>";
    }
}
?>
<?php
$hospitals=mysqli_query($con,"SELECT * FROM hospitals");
if(!$hospitals) {
    echo "<p style='color:red;'><strong>Error: " . htmlspecialchars(mysqli_error($con)) . "</strong></p>";
} else {
    while($h=mysqli_fetch_assoc($hospitals)){
        $hname = htmlspecialchars($h['hname']);
        $hid = intval($h['id']);
        echo "<div class='card'><h3>$hname</h3>";
        $bloods=mysqli_query($con,"SELECT blood_group, SUM(quantity) as qty FROM hospital_blood WHERE hospital_id=$hid GROUP BY blood_group");
        if(!$bloods) {
            echo "<p style='color:red;'>Error loading blood data</p>";
        } else {
            echo "<table><tr><th>Blood Group</th><th>Quantity</th></tr>";
            $count = 0;
            while($b=mysqli_fetch_assoc($bloods)){
                $bbg = htmlspecialchars($b['blood_group']);
                $qty = htmlspecialchars($b['qty'] ?? '0');
                echo "<tr><td>$bbg</td><td>$qty</td></tr>";
                $count++;
            }
            if($count == 0) {
                echo "<tr><td colspan='2'>No blood inventory</td></tr>";
            }
            echo "</table>";
        }
        echo "</div>";
    }
}
?>

<?php include "footer.php"; ?>
</section>
</body>
</html>
