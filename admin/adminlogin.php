<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Database Connection
$conn = new mysqli("localhost", "root", "", "agro_council");
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}
session_start();
if(isset($_POST['login']))
{
    $adminname = $_POST['adminname'];
    $adminpassword = $_POST['adminpassword'];
    
    $stmt = $conn->prepare("SELECT adminpassword FROM admin WHERE adminname = ?");
    $stmt->bind_param("s", $adminname);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();
        $stored = $row['adminpassword'];
        $valid = password_verify($adminpassword, $stored) || $adminpassword === $stored; // backward compatibility for legacy plaintext

        if ($valid) {
            $_SESSION['adminname'] = $adminname;

            // For logout Purpose
            $_SESSION['admin'] =  $_POST['adminname'];
            // Redirect to the admin panel page
            header ("Location: adminpanel.php");
            exit();
        }
    }
    // Redirect to the same login page with an error message
    header("Location: adminlogin.php?error=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/adminlogin.css">
</head>
<body>
    <div class="admin-auth-shell">
        <div class="admin-auth-card">
            <div class="admin-auth-header">
                <p class="eyebrow">Admin</p>
                <h1>Sign in</h1>
                <p class="helper">Manage approvals and content securely.</p>
            </div>
            <form action="" method="post" autocomplete="off" class="admin-auth-form">
                <div class="field">
                    <label for="adminname">Admin name</label>
                    <input type="text" placeholder="Enter admin name" name="adminname" id="adminname" required>
                </div>
                <div class="field">
                    <label for="adminpassword">Password</label>
                    <input type="password" placeholder="Enter password" name="adminpassword" id="adminpassword" required>
                </div>
                <div class="button-row">
                    <button type="submit" name="login">Log in</button>
                </div>
                <?php if (isset($_GET['error'])) { ?>
                    <div class="admin-alert">Invalid credentials. Try again.</div>
                <?php } ?>
            </form>
        </div>
    </div>
</body>
</html>
