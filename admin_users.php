<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db.php';
include 'navbar.php';

$success_msg = '';
$error_msg = '';

// Handle delete user action
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    
    if ($user_id > 0) {
        $delete_stmt = $con->prepare('DELETE FROM users WHERE id = ?');
        $delete_stmt->bind_param('i', $user_id);
        
        if ($delete_stmt->execute()) {
            $success_msg = 'User deleted successfully.';
        } else {
            $error_msg = 'Error deleting user: ' . $con->error;
        }
        $delete_stmt->close();
    }
}

// Get all users
$users_query = 'SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC';
$users_result = $con->query($users_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - BloodConnect Admin</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="section container">
        <h2>Manage Users</h2>

        <?php if ($success_msg): ?>
            <div class="success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="error"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <!-- Users Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card">
                <h3>Total Registered Users</h3>
                <div class="stat-number">
                    <?php 
                    $count_result = $con->query('SELECT COUNT(*) as count FROM users');
                    $count_row = $count_result->fetch_assoc();
                    echo intval($count_row['count'] ?? 0);
                    ?>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($users_result && $users_result->num_rows > 0) {
                        while ($user = $users_result->fetch_assoc()) {
                            $id = intval($user['id']);
                            $name = htmlspecialchars($user['name'] ?? '');
                            $email = htmlspecialchars($user['email'] ?? '');
                            $phone = htmlspecialchars($user['phone'] ?? '');
                            $created_at = htmlspecialchars($user['created_at'] ?? '');
                            
                            echo "<tr>";
                            echo "<td><strong>$name</strong></td>";
                            echo "<td>$email</td>";
                            echo "<td>$phone</td>";
                            echo "<td>$created_at</td>";
                            echo "<td>";
                            echo "<form method='POST' style='display:inline;'>";
                            echo "<input type='hidden' name='user_id' value='$id'>";
                            echo "<button type='submit' name='delete_user' class='btn btn-secondary-danger' style='padding: 0.5rem 0.75rem; font-size: 0.85rem;' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding: 2rem;'>No users found yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    <?php include 'footer.php'; ?>
</body>
</html>
